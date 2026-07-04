<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\PaymentType;
use App\Models\Room;
use App\Models\RoomRental;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\GuestRegister;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $consumerFinal = Client::query()->firstOrCreate(
            ['dni' => '99999999'],
            [
                'code' => 'CLI-CFINAL',
                'full_name' => 'CONSUMIDOR FINAL',
                'email' => null,
                'phone' => null,
                'active' => true,
            ]
        );

        if (!$consumerFinal->active) {
            $consumerFinal->update(['active' => true]);
        }

        $sales = Sale::with(['client', 'items.product', 'rentals.room', 'paymentType'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->where('code', 'like', "%{$q}%")
                        ->orWhereHas('client', function ($client) use ($q) {
                            $client->where('full_name', 'like', "%{$q}%")
                                ->orWhere('dni', 'like', "%{$q}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $clients = Client::where('active', true)->orderBy('full_name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $rooms = Room::where('active', true)->orderBy('room_number')->get();
        $paymentTypes = Schema::hasTable('payment_types')
            ? PaymentType::orderBy('name')->get()
            : collect();

        $consumerFinalLabel = $consumerFinal->code . ' - ' . $consumerFinal->full_name . ' (' . $consumerFinal->dni . ')';

        return view('admin.modules.ventas', compact('sales', 'clients', 'products', 'rooms', 'paymentTypes', 'consumerFinal', 'consumerFinalLabel'));
    }

    public function storeQuickClient(Request $request)
    {
        $validated = $request->validateWithBag('quickClient', [
            'full_name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'regex:/^(\d{8}|\d{11})$/', 'unique:clients,dni'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        Client::create([
            'code' => $this->nextClientCode(),
            'full_name' => $validated['full_name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'active' => true,
        ]);

        return back()
            ->with('success', 'Cliente registrado correctamente.')
            ->with('open_sale_modal', true);
    }

    public function lookupClientDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document' => ['required', 'string'],
        ]);

        $document = preg_replace('/\D+/', '', $validated['document']);
        if (!$document) {
            return response()->json(['message' => 'Documento inválido.'], 422);
        }

        $existingClient = Client::query()->where('dni', $document)->first();
        if ($existingClient) {
            return response()->json([
                'source' => 'local',
                'document_type' => strlen($document) === 11 ? 'ruc' : 'dni',
                'dni' => $existingClient->dni,
                'full_name' => $existingClient->full_name,
                'email' => (string) ($existingClient->email ?? ''),
                'phone' => (string) ($existingClient->phone ?? ''),
                'address' => '',
            ]);
        }

        $token = config('services.decolecta.token');
        if (!$token) {
            return response()->json(['message' => 'Falta configurar DECOLECTA_API_KEY en .env'], 422);
        }

        if (strlen($document) === 8) {
            $url = config('services.decolecta.reniec_dni_url', 'https://api.decolecta.com/v1/reniec/dni');
            \Log::info('Buscando DNI: ' . $document . ' en ' . $url);
            try {
                $response = Http::timeout(12)->acceptJson()->withToken($token)->get($url, ['numero' => $document]);
                \Log::info('Response status: ' . $response->status());
                \Log::info('Response body: ' . $response->body());

                if ($response->failed()) {
                    \Log::error('RENIEC consulta falló: ' . $response->status());
                    return response()->json(['message' => 'No se pudo consultar RENIEC.'], 422);
                }

                $data = $response->json();
                \Log::info('Response JSON: ' . json_encode($data));
                $fullName = trim(
                    ($data['full_name'] ?? '')
                    ?: ($data['nombre_completo'] ?? '')
                    ?: (($data['nombres'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? ''))
                    ?: (($data['first_last_name'] ?? '') . ' ' . ($data['second_last_name'] ?? '') . ' ' . ($data['first_name'] ?? ''))
                );

                if ($fullName === '') {
                    \Log::error('No se encontró nombre válido en respuesta');
                    return response()->json(['message' => 'RENIEC no devolvió nombre válido.'], 422);
                }

                return response()->json([
                    'source' => 'reniec',
                    'document_type' => 'dni',
                    'dni' => $document,
                    'full_name' => preg_replace('/\s+/', ' ', $fullName),
                    'email' => '',
                    'phone' => '',
                    'address' => '',
                ]);
            } catch (\Exception $e) {
                \Log::error('Excepción en consulta DNI: ' . $e->getMessage());
                return response()->json(['message' => 'Error en consulta DNI: ' . $e->getMessage()], 500);
            }
        }

        if (strlen($document) === 11) {
            $url = config('services.decolecta.sunat_ruc_url', 'https://api.decolecta.com/v1/sunat/ruc');
            $response = Http::timeout(12)->acceptJson()->withToken($token)->get($url, ['numero' => $document]);

            if ($response->failed()) {
                return response()->json(['message' => 'No se pudo consultar SUNAT.'], 422);
            }

            $data = $response->json();
            $company = trim((string) ($data['razon_social'] ?? ''));
            if ($company === '') {
                return response()->json(['message' => 'SUNAT no devolvió razón social válida.'], 422);
            }

            return response()->json([
                'source' => 'sunat',
                'document_type' => 'ruc',
                'dni' => $document,
                'full_name' => $company,
                'email' => '',
                'phone' => '',
                'address' => (string) ($data['direccion'] ?? ''),
            ]);
        }

        return response()->json(['message' => 'Solo se permite DNI (8) o RUC (11).'], 422);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('payment_types')) {
            return back()->withErrors([
                'sale' => 'Falta ejecutar migraciones de tipos de pago. Corre: php artisan migrate',
            ])->withInput();
        }

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'document_type' => ['required', 'in:boleta,factura'],
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'rent_room' => ['nullable', 'boolean'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'rate_mode' => ['nullable', 'in:hour,day'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'guest_full_name' => ['nullable', 'string', 'max:150'],
            'guest_document_type' => ['nullable', 'in:DNI,CE,PASAPORTE,OTRO'],
            'guest_document_number' => ['nullable', 'string', 'max:20'],
            'guest_nationality' => ['nullable', 'string', 'max:80'],
            'guest_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $rentRoom = (bool) ($validated['rent_room'] ?? false);
        $hasProduct = !empty($validated['product_id']);

        if (!$rentRoom && !$hasProduct) {
            return back()->withErrors(['sale' => 'Debes registrar al menos una habitacion o un producto.'])->withInput();
        }

        if ($rentRoom && (empty($validated['room_id']) || empty($validated['start_at']) || empty($validated['end_at']) || !isset($validated['rate']))) {
            return back()->withErrors(['sale' => 'Para alquiler debes completar habitacion, inicio, fin y tarifa.'])->withInput();
        }
        if ($rentRoom && (empty($validated['guest_full_name']) || empty($validated['guest_document_type']) || empty($validated['guest_document_number']) || empty($validated['guest_nationality']))) {
            return back()->withErrors(['sale' => 'Para alquiler debes registrar datos del huésped (nombre, documento y nacionalidad).'])->withInput();
        }

        $client = Client::query()->findOrFail($validated['client_id']);
        $isConsumerFinal = $client->dni === '99999999';

        if ($validated['document_type'] === 'factura') {
            if (strlen((string) $client->dni) !== 11 || $isConsumerFinal) {
                return back()->withErrors(['sale' => 'Para FACTURA debes seleccionar un cliente con RUC válido (11 dígitos).'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $rentRoom, $hasProduct, $request, $client, $isConsumerFinal) {
            $doc = $this->nextDocumentData($validated['document_type']);
            $sale = Sale::create([
                'code' => $doc['code'],
                'document_type' => $validated['document_type'],
                'series' => $doc['series'],
                'correlative' => $doc['correlative'],
                'client_id' => $validated['client_id'],
                'user_id' => $request->user()->id,
                'status' => 'paid',
                'payment_type_id' => $validated['payment_type_id'],
                'total' => 0,
                'subtotal' => 0,
                'igv' => 0,
            ]);

            $total = 0;

            if ($rentRoom) {
                $start = Carbon::parse($validated['start_at']);
                $end = Carbon::parse($validated['end_at']);
                $hours = max(1, (int) ceil($start->diffInMinutes($end) / 60));
                $days = max(1, (int) ceil($hours / 24));
                $rateMode = $validated['rate_mode'] ?? 'hour';
                $subtotal = $rateMode === 'day'
                    ? round($days * (float) $validated['rate'], 2)
                    : round($hours * (float) $validated['rate'], 2);

                RoomRental::create([
                    'sale_id' => $sale->id,
                    'room_id' => $validated['room_id'],
                    'start_at' => $start,
                    'end_at' => $end,
                    'hours' => $hours,
                    'days' => $days,
                    'rate' => $validated['rate'],
                    'subtotal' => $subtotal,
                ]);

                GuestRegister::create([
                    'code' => $this->nextGuestCode(),
                    'sale_id' => $sale->id,
                    'room_id' => $validated['room_id'],
                    'created_by' => $request->user()->id,
                    'full_name' => $validated['guest_full_name'],
                    'document_type' => $validated['guest_document_type'],
                    'document_number' => $validated['guest_document_number'],
                    'nationality' => mb_strtoupper($validated['guest_nationality']),
                    'check_in_at' => $start,
                    'check_out_at' => null,
                    'status' => 'hospedado',
                    'notes' => $validated['guest_notes'] ?? null,
                ]);

                $total += $subtotal;
            }

            if ($hasProduct) {
                $product = Product::findOrFail($validated['product_id']);
                $qty = (int) ($validated['quantity'] ?? 1);
                $subtotal = round($qty * (float) $product->price, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                if ($product->stock >= $qty) {
                    $product->decrement('stock', $qty);
                }
            }

            if ($validated['document_type'] === 'boleta' && $total > 700 && $isConsumerFinal) {
                throw ValidationException::withMessages([
                    'sale' => 'Para boletas mayores a S/ 700 debes identificar al cliente con DNI o RUC real.',
                ]);
            }

            $taxBase = round($total / 1.18, 2);
            $igv = round($total - $taxBase, 2);

            $sale->update([
                'total' => $total,
                'subtotal' => $taxBase,
                'igv' => $igv,
            ]);
        });

        return back()->with('success', 'Venta registrada correctamente.');
    }

    public function storePaymentType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:payment_types,name'],
        ]);

        PaymentType::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()
            ->with('payment_types_success', 'Tipo de pago registrado correctamente.')
            ->with('open_payment_types_modal', true);
    }

    public function togglePaymentType(PaymentType $paymentType)
    {
        $paymentType->update([
            'is_active' => !$paymentType->is_active,
        ]);

        return back()
            ->with('payment_types_success', $paymentType->is_active ? 'Tipo de pago activado correctamente.' : 'Tipo de pago desactivado correctamente.')
            ->with('open_payment_types_modal', true);
    }

    public function updatePaymentType(Request $request, PaymentType $paymentType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:payment_types,name,' . $paymentType->id],
        ]);

        $paymentType->update([
            'name' => $validated['name'],
        ]);

        return back()
            ->with('payment_types_success', 'Tipo de pago actualizado correctamente.')
            ->with('open_payment_types_modal', true);
    }

    public function print(Sale $sale)
    {
        $sale->load(['client', 'items.product', 'rentals.room', 'paymentType', 'user']);

        return view('admin.modules.ventas-print', compact('sale'));
    }

    private function nextDocumentData(string $documentType): array
    {
        $series = $documentType === 'factura' ? 'F001' : 'B001';
        $lastCorrelative = Sale::query()
            ->where('series', $series)
            ->max('correlative');

        $correlative = ((int) $lastCorrelative) + 1;
        $code = $series . '-' . str_pad((string) $correlative, 8, '0', STR_PAD_LEFT);

        return [
            'series' => $series,
            'correlative' => $correlative,
            'code' => $code,
        ];
    }

    private function nextClientCode(): string
    {
        do {
            $code = 'CLI-' . strtoupper(substr(uniqid(), -5));
        } while (Client::where('code', $code)->exists());

        return $code;
    }

    private function nextGuestCode(): string
    {
        do {
            $code = 'HSP-' . strtoupper(substr(uniqid(), -6));
        } while (GuestRegister::query()->where('code', $code)->exists());

        return $code;
    }
}
