<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('active', (bool) $request->status);
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        return view('admin.modules.clientes', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'regex:/^(\d{8}|\d{11})$/', 'unique:clients,dni'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        Client::create([
            'code' => $this->nextCode(),
            'full_name' => $validated['full_name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'active' => true,
        ]);

        return back()->with('success', 'Cliente registrado correctamente.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'dni' => ['required', 'regex:/^(\d{8}|\d{11})$/', 'unique:clients,dni,' . $client->id],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client->update([
            'full_name' => $validated['full_name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return back()->with('success', 'Cliente actualizado correctamente.');
    }

    public function toggleStatus(Client $client)
    {
        $client->update([
            'active' => !$client->active,
        ]);

        return back()->with('success', $client->active ? 'Cliente activado correctamente.' : 'Cliente desactivado correctamente.');
    }

    public function lookupDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document' => ['required', 'string'],
        ]);

        $document = preg_replace('/\D+/', '', $validated['document']);
        if (!$document) {
            return response()->json(['message' => 'Documento inválido.'], 422);
        }

        $local = Client::query()->where('dni', $document)->first();
        if ($local) {
            return response()->json([
                'source' => 'local',
                'dni' => $local->dni,
                'full_name' => $local->full_name,
                'phone' => (string) ($local->phone ?? ''),
                'email' => (string) ($local->email ?? ''),
            ]);
        }

        $token = config('services.decolecta.token');
        if (!$token) {
            return response()->json(['message' => 'Falta configurar DECOLECTA_API_KEY en .env'], 422);
        }

        if (strlen($document) === 8) {
            $url = config('services.decolecta.reniec_dni_url', 'https://api.decolecta.com/v1/reniec/dni');
            $response = Http::timeout(12)->acceptJson()->withToken($token)->get($url, ['numero' => $document]);
            if ($response->failed()) {
                return response()->json(['message' => 'No se pudo consultar RENIEC.'], 422);
            }
            $data = $response->json();
            $fullName = trim(
                ($data['full_name'] ?? '')
                ?: ($data['nombre_completo'] ?? '')
                ?: (($data['nombres'] ?? '') . ' ' . ($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? ''))
                ?: (($data['first_last_name'] ?? '') . ' ' . ($data['second_last_name'] ?? '') . ' ' . ($data['first_name'] ?? ''))
            );
            if ($fullName === '') {
                return response()->json(['message' => 'RENIEC no devolvió nombre válido.'], 422);
            }
            return response()->json([
                'source' => 'reniec',
                'dni' => $document,
                'full_name' => preg_replace('/\s+/', ' ', $fullName),
                'phone' => '',
                'email' => '',
            ]);
        }

        if (strlen($document) === 11) {
            $url = config('services.decolecta.sunat_ruc_url', 'https://api.decolecta.com/v1/sunat/ruc');
            $response = Http::timeout(12)->acceptJson()->withToken($token)->get($url, ['numero' => $document]);
            if ($response->failed()) {
                return response()->json(['message' => 'No se pudo consultar SUNAT.'], 422);
            }
            $data = $response->json();
            $name = trim((string) ($data['razon_social'] ?? ''));
            if ($name === '') {
                return response()->json(['message' => 'SUNAT no devolvió razón social válida.'], 422);
            }
            return response()->json([
                'source' => 'sunat',
                'dni' => $document,
                'full_name' => $name,
                'phone' => '',
                'email' => '',
            ]);
        }

        return response()->json(['message' => 'Solo se permite DNI (8) o RUC (11).'], 422);
    }

    private function nextCode(): string
    {
        do {
            $code = 'CLI-' . strtoupper(substr(uniqid(), -6));
        } while (Client::where('code', $code)->exists());

        return $code;
    }
}
