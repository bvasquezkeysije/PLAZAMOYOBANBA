<x-layouts.admin>
    <x-slot name="title">Ventas</x-slot>

    <div
        x-data="{
            showFilters: false,
            showSaleModal: {{ (session('open_sale_modal') || $errors->has('sale') || $errors->quickClient->any()) ? 'true' : 'false' }},
            showQuickClientModal: {{ $errors->quickClient->any() ? 'true' : 'false' }},
            showPaymentTypesModal: {{ (session('open_payment_types_modal') || $errors->has('payment_type')) ? 'true' : 'false' }},
            showEditPaymentTypeModal: false,
            showSaleDetailModal: false,
            selectedSale: null,
            editPaymentTypeId: null,
            editPaymentTypeName: '',
            clientInput: '',
            clientId: '',
            consumerFinalId: @js((string) $consumerFinal->id),
            consumerFinalLabel: @js($consumerFinalLabel),
            productInput: '',
            productId: '',
            roomInput: '',
            roomId: '',
            rentRoom: false,
            rateMode: 'hour',
            rateInput: '',
            startAtInput: '',
            endAtInput: '',
            quantityInput: 1,
            roomSubtotal: 0,
            productSubtotal: 0,
            totalPreview: 0,
            quickDocument: '',
            quickLoading: false,
            quickMessage: '',
            quickMessageType: 'info',
            guestLookupDocument: @js((string) old('guest_document_number', '')),
            guestLookupLoading: false,
            guestLookupMessage: '',
            guestLookupMessageType: 'info',
            guestFullName: @js((string) old('guest_full_name', '')),
            guestDocumentType: @js((string) old('guest_document_type', 'DNI')),
            guestDocumentNumber: @js((string) old('guest_document_number', '')),
            guestNationality: @js((string) old('guest_nationality', 'PERUANA')),
            guestNotes: @js((string) old('guest_notes', '')),
            quickFullName: @js((string) old('full_name', '')),
            quickDni: @js((string) old('dni', '')),
            quickEmail: @js((string) old('email', '')),
            quickPhone: @js((string) old('phone', '')),
            salesDetails: @js($sales->getCollection()->mapWithKeys(fn($sale) => [
                $sale->id => [
                    'code' => $sale->code,
                    'document_type' => strtoupper((string) $sale->document_type),
                    'client' => $sale->client->full_name,
                    'client_doc' => $sale->client->dni,
                    'date' => $sale->created_at->format('d/m/Y H:i'),
                    'payment_type' => $sale->paymentType->name ?? '-',
                    'status' => 'Pagado',
                    'subtotal' => (float) ($sale->subtotal ?? 0),
                    'igv' => (float) ($sale->igv ?? 0),
                    'total' => (float) $sale->total,
                    'products' => $sale->items->map(fn($it) => [
                        'name' => $it->product->name ?? 'Producto',
                        'qty' => (int) $it->quantity,
                        'unit_price' => (float) $it->unit_price,
                        'subtotal' => (float) $it->subtotal,
                    ])->values(),
                    'rooms' => $sale->rentals->map(fn($rt) => [
                        'room' => $rt->room->room_number ?? '-',
                        'type' => $rt->room->type ?? '-',
                        'start' => \Carbon\Carbon::parse($rt->start_at)->format('d/m/Y H:i'),
                        'end' => \Carbon\Carbon::parse($rt->end_at)->format('d/m/Y H:i'),
                        'subtotal' => (float) $rt->subtotal,
                    ])->values(),
                ]
            ])),
            clientsRef: {{ \Illuminate\Support\Js::from($clients->map(fn($c) => ['id' => $c->id, 'label' => $c->code.' - '.$c->full_name.' ('.$c->dni.')'])->values()) }},
            productsRef: {{ \Illuminate\Support\Js::from($products->map(fn($p) => ['id' => $p->id, 'label' => $p->code.' - '.$p->name.' (S/ '.number_format((float)$p->price, 2).')', 'price' => (float) $p->price])->values()) }},
            roomsRef: {{ \Illuminate\Support\Js::from($rooms->map(fn($r) => [
                'id' => $r->id,
                'label' => $r->code.' - Hab. '.$r->room_number.' ('.$r->type.')',
                'hourly_rate' => (float) ($r->hourly_rate ?? 0),
                'daily_rate' => (float) ($r->daily_rate ?? 0),
            ])->values()) }},
            openEditPaymentType(paymentType) {
                this.editPaymentTypeId = paymentType.id;
                this.editPaymentTypeName = paymentType.name;
                this.showEditPaymentTypeModal = true;
            },
            closeEditPaymentType() {
                this.showEditPaymentTypeModal = false;
                this.editPaymentTypeId = null;
                this.editPaymentTypeName = '';
            },
            openSaleDetail(sale) {
                this.selectedSale = sale;
                this.showSaleDetailModal = true;
            },
            openSaleDetailById(id) {
                const sale = this.salesDetails[String(id)] ?? this.salesDetails[id];
                if (!sale) return;
                this.openSaleDetail(sale);
            },
            closeSaleDetail() {
                this.showSaleDetailModal = false;
                this.selectedSale = null;
            },
            syncClient() {
                const found = this.clientsRef.find(item => item.label === this.clientInput);
                this.clientId = found ? found.id : '';
            },
            setConsumerFinal() {
                this.clientInput = this.consumerFinalLabel;
                this.clientId = this.consumerFinalId;
            },
            syncProduct() {
                const found = this.productsRef.find(item => item.label === this.productInput);
                this.productId = found ? found.id : '';
                this.updateTotals();
            },
            syncRoom() {
                const found = this.roomsRef.find(item => item.label === this.roomInput);
                this.roomId = found ? found.id : '';
                this.applyRoomRate();
                this.updateTotals();
            },
            applyRoomRate() {
                if (!this.roomId) return;
                const selected = this.roomsRef.find(item => String(item.id) === String(this.roomId));
                if (!selected) return;

                if (this.rateMode === 'day') {
                    this.rateInput = selected.daily_rate > 0 ? selected.daily_rate : '';
                } else {
                    this.rateInput = selected.hourly_rate > 0 ? selected.hourly_rate : '';
                }
            },
            updateTotals() {
                let room = 0;
                if (this.rentRoom && this.roomId && this.rateInput && this.startAtInput && this.endAtInput) {
                    const start = new Date(this.startAtInput);
                    const end = new Date(this.endAtInput);
                    const diffMs = end - start;
                    if (!Number.isNaN(diffMs) && diffMs > 0) {
                        const hours = Math.max(1, Math.ceil(diffMs / (1000 * 60 * 60)));
                        const days = Math.max(1, Math.ceil(hours / 24));
                        room = (this.rateMode === 'day' ? days : hours) * (parseFloat(this.rateInput) || 0);
                    }
                }

                let product = 0;
                if (this.productId) {
                    const selected = this.productsRef.find(item => String(item.id) === String(this.productId));
                    if (selected) {
                        const qty = Math.max(1, parseInt(this.quantityInput || 1));
                        product = qty * (parseFloat(selected.price) || 0);
                    }
                }

                this.roomSubtotal = room;
                this.productSubtotal = product;
                this.totalPreview = room + product;
            },
            async lookupDocument() {
                this.quickMessage = '';
                const doc = (this.quickDocument || '').replace(/\D/g, '');
                if (!(doc.length === 8 || doc.length === 11)) {
                    this.quickMessageType = 'error';
                    this.quickMessage = 'Ingresa un DNI (8) o RUC (11) válido.';
                    return;
                }

                this.quickLoading = true;
                try {
                    const response = await fetch('/admin/ventas/clientes/lookup?document=' + encodeURIComponent(doc), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        this.quickMessageType = 'error';
                        this.quickMessage = payload.message || 'No se pudo consultar el documento.';
                        return;
                    }

                    this.quickDni = payload.dni || doc;
                    this.quickFullName = payload.full_name || '';
                    this.quickEmail = payload.email || '';
                    this.quickPhone = payload.phone || '';
                    this.quickMessageType = 'ok';
                    if (payload.source === 'local') {
                        this.quickMessage = 'Cliente encontrado en la base de datos.';
                    } else if (payload.source === 'reniec') {
                        this.quickMessage = 'Datos cargados desde RENIEC.';
                    } else if (payload.source === 'sunat') {
                        this.quickMessage = 'Datos cargados desde SUNAT.';
                    } else {
                        this.quickMessage = 'Datos cargados correctamente.';
                    }
                } catch (e) {
                    this.quickMessageType = 'error';
                    this.quickMessage = 'Error de conexión al consultar documento.';
                } finally {
                    this.quickLoading = false;
                }
            },
            async lookupGuestByDni() {
                this.guestLookupMessage = '';
                const doc = (this.guestLookupDocument || '').replace(/\D/g, '');
                if (doc.length !== 8) {
                    this.guestLookupMessageType = 'error';
                    this.guestLookupMessage = 'Ingresa un DNI válido de 8 dígitos.';
                    return;
                }
                this.guestLookupLoading = true;
                try {
                    const response = await fetch('/admin/ventas/clientes/lookup?document=' + encodeURIComponent(doc), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        this.guestLookupMessageType = 'error';
                        this.guestLookupMessage = payload.message || 'No se pudo consultar RENIEC.';
                        return;
                    }
                    if ((payload.document_type || '') !== 'dni') {
                        this.guestLookupMessageType = 'error';
                        this.guestLookupMessage = 'Solo se permite DNI para registro de huésped.';
                        return;
                    }
                    this.guestDocumentType = 'DNI';
                    this.guestDocumentNumber = payload.dni || doc;
                    this.guestFullName = payload.full_name || '';
                    this.guestNationality = this.guestNationality || 'PERUANA';
                    this.guestLookupMessageType = 'ok';
                    this.guestLookupMessage = payload.source === 'local'
                        ? 'Huésped encontrado en base local.'
                        : 'Datos del huésped cargados desde RENIEC.';
                } catch (e) {
                    console.error('Error en búsqueda de DNI:', e);
                    this.guestLookupMessageType = 'error';
                    this.guestLookupMessage = 'Error de conexión al consultar DNI: ' + e.message;
                } finally {
                    this.guestLookupLoading = false;
                }
            }
        }"
        class="space-y-4"
    >
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->has('sale'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first('sale') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.ventas') }}" class="space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full lg:flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar venta por código, cliente o usuario">

                    <button type="button" @click="showFilters = !showFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50" aria-label="Filtros" title="Filtros">
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtros" class="w-4 h-4">
                    </button>
                    <button type="button" @click="showSaleModal = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-100" aria-label="Añadir venta" title="Añadir venta">
                        <img src="{{ asset('images/anadir-venta.svg') }}" alt="Añadir venta" class="w-4 h-4">
                    </button>
                    <button type="button" @click="showPaymentTypesModal = true" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-semibold hover:bg-emerald-100" aria-label="Tipos de pago" title="Tipos de pago">
                        <img src="{{ asset('images/tipo-pago.svg') }}" alt="Tipos de pago" class="w-4 h-4">
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800" aria-label="Buscar" title="Buscar">
                        <img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert">
                    </button>
                </div>

                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4" style="display: none;">
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Desde</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Hasta</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label><select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"><option value="">Todos</option><option value="paid">Pagado</option></select></div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left"><tr><th class="px-4 py-3">Comprobante</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Cliente</th><th class="px-4 py-3">Pago</th><th class="px-4 py-3">Subtotal</th><th class="px-4 py-3">IGV</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($sales as $sale)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-semibold">
                                <div>{{ strtoupper($sale->document_type ?? 'boleta') }}</div>
                                <div class="text-xs text-slate-500">{{ $sale->code }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $sale->client->full_name }}</td>
                            <td class="px-4 py-3">{{ $sale->paymentType->name ?? '-' }}</td>
                            <td class="px-4 py-3">S/ {{ number_format((float) ($sale->subtotal ?? 0), 2) }}</td>
                            <td class="px-4 py-3">S/ {{ number_format((float) ($sale->igv ?? 0), 2) }}</td>
                            <td class="px-4 py-3">S/ {{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3">Pagado</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="openSaleDetailById({{ $sale->id }})"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-slate-700 text-white text-xs font-semibold hover:bg-slate-800"
                                        aria-label="Ver detalle"
                                        title="Ver detalle"
                                    >
                                        <img src="{{ asset('images/ver-detalle.svg') }}" alt="Ver detalle" class="w-4 h-4 brightness-0 invert">
                                    </button>
                                    <a
                                        href="{{ route('admin.ventas.print', $sale) }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-blue-700 text-white text-xs font-semibold hover:bg-blue-800"
                                        aria-label="Imprimir boleta"
                                        title="Imprimir boleta"
                                    >
                                        <img src="{{ asset('images/imprimir.svg') }}" alt="Imprimir boleta" class="w-4 h-4 brightness-0 invert">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t"><td class="px-4 py-3" colspan="9">Sin datos por ahora.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $sales->links() }}</div>

        <div x-cloak x-show="showSaleDetailModal" x-transition class="fixed inset-0 z-[95] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60" @click="closeSaleDetail()"></div>
            <div class="relative w-full max-w-4xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Detalle de venta</h3>
                    <button type="button" @click="closeSaleDetail()" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto" x-show="selectedSale">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><span class="font-semibold">Comprobante:</span> <span x-text="selectedSale?.document_type"></span> <span x-text="selectedSale?.code"></span></div>
                        <div><span class="font-semibold">Fecha:</span> <span x-text="selectedSale?.date"></span></div>
                        <div><span class="font-semibold">Cliente:</span> <span x-text="selectedSale?.client"></span></div>
                        <div><span class="font-semibold">Doc:</span> <span x-text="selectedSale?.client_doc"></span></div>
                        <div><span class="font-semibold">Pago:</span> <span x-text="selectedSale?.payment_type"></span></div>
                        <div><span class="font-semibold">Estado:</span> <span x-text="selectedSale?.status"></span></div>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-4 py-2 bg-slate-50 font-semibold text-sm">Habitaciones</div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-white"><tr><th class="px-4 py-2 text-left">Habitación</th><th class="px-4 py-2 text-left">Desde</th><th class="px-4 py-2 text-left">Hasta</th><th class="px-4 py-2 text-left">Subtotal</th></tr></thead>
                            <tbody>
                                <template x-if="selectedSale && selectedSale.rooms && selectedSale.rooms.length === 0">
                                    <tr><td colspan="4" class="px-4 py-3 text-slate-500">Sin alquiler en esta venta.</td></tr>
                                </template>
                                <template x-for="room in (selectedSale?.rooms || [])" :key="room.room + room.start">
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><span x-text="`Hab. ${room.room} (${room.type})`"></span></td>
                                        <td class="px-4 py-2" x-text="room.start"></td>
                                        <td class="px-4 py-2" x-text="room.end"></td>
                                        <td class="px-4 py-2" x-text="`S/ ${Number(room.subtotal).toFixed(2)}`"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-4 py-2 bg-slate-50 font-semibold text-sm">Productos</div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-white"><tr><th class="px-4 py-2 text-left">Producto</th><th class="px-4 py-2 text-left">Cant.</th><th class="px-4 py-2 text-left">P. Unit.</th><th class="px-4 py-2 text-left">Subtotal</th></tr></thead>
                            <tbody>
                                <template x-if="selectedSale && selectedSale.products && selectedSale.products.length === 0">
                                    <tr><td colspan="4" class="px-4 py-3 text-slate-500">Sin productos en esta venta.</td></tr>
                                </template>
                                <template x-for="product in (selectedSale?.products || [])" :key="product.name + product.qty">
                                    <tr class="border-t">
                                        <td class="px-4 py-2" x-text="product.name"></td>
                                        <td class="px-4 py-2" x-text="product.qty"></td>
                                        <td class="px-4 py-2" x-text="`S/ ${Number(product.unit_price).toFixed(2)}`"></td>
                                        <td class="px-4 py-2" x-text="`S/ ${Number(product.subtotal).toFixed(2)}`"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><strong x-text="`S/ ${Number(selectedSale?.subtotal || 0).toFixed(2)}`"></strong></div>
                        <div class="flex justify-between"><span>IGV</span><strong x-text="`S/ ${Number(selectedSale?.igv || 0).toFixed(2)}`"></strong></div>
                        <div class="flex justify-between text-blue-900 font-bold border-t border-blue-200 mt-1 pt-1"><span>Total</span><span x-text="`S/ ${Number(selectedSale?.total || 0).toFixed(2)}`"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showSaleModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="showSaleModal = false"></div>
            <div class="relative w-full max-w-3xl max-h-[92vh] rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Registrar venta</h3>
                    <button type="button" @click="showSaleModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.ventas.store') }}" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                        <h4 class="text-sm font-bold text-slate-800">Datos del cliente</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                                <div class="flex gap-2">
                                    <input list="sales-clients-list" x-model="clientInput" @change="syncClient" @input.debounce.120ms="syncClient" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar cliente en la base de datos" required>
                                    <button type="button" @click="setConsumerFinal()" class="px-3 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-xs font-semibold hover:bg-amber-100 whitespace-nowrap">Consumidor final</button>
                                    <button type="button" @click="showQuickClientModal = true" class="px-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 text-sm font-semibold hover:bg-emerald-100">+</button>
                                </div>
                                <input type="hidden" name="client_id" :value="clientId">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Comprobante</label>
                                <select name="document_type" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="boleta">Boleta</option>
                                    <option value="factura">Factura</option>
                                </select>
                            </div>
                            <input type="hidden" name="status" value="paid">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de pago</label>
                                <select name="payment_type_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Seleccionar tipo de pago</option>
                                    @foreach ($paymentTypes->where('is_active', true) as $paymentType)
                                        <option value="{{ $paymentType->id }}">{{ $paymentType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                        <h4 class="text-sm font-bold text-slate-800">Alquiler de habitación</h4>
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="rent_room" value="1" x-model="rentRoom" @change="updateTotals" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Incluir habitación en esta venta
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input list="sales-rooms-list" x-model="roomInput" @change="syncRoom" @input.debounce.120ms="syncRoom" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar habitación en BD">
                            <input type="hidden" name="room_id" :value="roomId">
                            <select name="rate_mode" x-model="rateMode" @change="applyRoomRate(); updateTotals()" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="hour">Tarifa por hora</option>
                                <option value="day">Tarifa por día</option>
                            </select>
                            <input type="number" step="0.01" min="0" name="rate" x-model="rateInput" @input="updateTotals" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" :placeholder="rateMode === 'day' ? 'Tarifa por día (S/)' : 'Tarifa por hora (S/)'">
                            <input type="datetime-local" name="start_at" x-model="startAtInput" @change="updateTotals" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <input type="datetime-local" name="end_at" x-model="endAtInput" @change="updateTotals" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div x-show="rentRoom" x-transition class="rounded-xl border border-slate-200 bg-white p-4 space-y-3" style="display:none;">
                        <h4 class="text-sm font-bold text-slate-800">Datos del huésped (Libro de Huéspedes)</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <label class="block text-sm font-medium text-slate-700">DNI del huésped</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="guestLookupDocument" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: 12345678">
                                <button type="button" @click="lookupGuestByDni" :disabled="guestLookupLoading" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 disabled:opacity-60" aria-label="Buscar DNI" title="Buscar DNI">
                                    <span x-show="!guestLookupLoading"><img src="{{ asset('images/buscar.svg') }}" alt="Buscar DNI" class="w-4 h-4 brightness-0 invert"></span>
                                    <span x-show="guestLookupLoading" style="display:none;">Buscando...</span>
                                </button>
                            </div>
                            <template x-if="guestLookupMessage">
                                <div class="rounded-lg px-3 py-2 text-sm"
                                     :class="guestLookupMessageType === 'ok' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-700'">
                                    <span x-text="guestLookupMessage"></span>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
                                <input type="text" name="guest_full_name" x-model="guestFullName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" :required="rentRoom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo documento</label>
                                <select name="guest_document_type" x-model="guestDocumentType" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" :required="rentRoom">
                                    <option value="DNI">DNI</option>
                                    <option value="CE">CE</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nro documento</label>
                                <input type="text" name="guest_document_number" x-model="guestDocumentNumber" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" :required="rentRoom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nacionalidad</label>
                                <input type="text" name="guest_nationality" x-model="guestNationality" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" :required="rentRoom">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Observación</label>
                            <textarea name="guest_notes" x-model="guestNotes" rows="2" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Opcional"></textarea>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                        <h4 class="text-sm font-bold text-slate-800">Compra de producto</h4>
                        <p class="text-xs text-slate-500">Producto adicional (snack, gaseosa, etc.)</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input list="sales-products-list" x-model="productInput" @change="syncProduct" @input.debounce.120ms="syncProduct" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Buscar producto en BD (opcional)">
                            <input type="hidden" name="product_id" :value="productId">
                            <input type="number" name="quantity" min="1" x-model="quantityInput" @input="updateTotals" value="1" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Cantidad">
                        </div>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-slate-800 space-y-1">
                        <div class="flex justify-between">
                            <span>Base imponible estimada</span>
                            <span>S/ <span x-text="(totalPreview / 1.18).toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>IGV estimado (18%)</span>
                            <span>S/ <span x-text="(totalPreview - (totalPreview / 1.18)).toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Subtotal habitación</span>
                            <span>S/ <span x-text="roomSubtotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Subtotal producto</span>
                            <span>S/ <span x-text="productSubtotal.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-blue-200 font-bold text-blue-900">
                            <span>Total estimado</span>
                            <span>S/ <span x-text="totalPreview.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar venta</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showPaymentTypesModal" x-transition class="fixed inset-0 z-[80] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showPaymentTypesModal = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Gestión de tipos de pago</h3>
                    <button type="button" @click="showPaymentTypesModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    @if (session('payment_types_success'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('payment_types_success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.ventas.payment-types.store') }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Ej: Efectivo, Yape, Transferencia" required>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 whitespace-nowrap">Añadir tipo</button>
                    </form>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left">
                                <tr>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paymentTypes as $paymentType)
                                    <tr class="border-t">
                                        <td class="px-4 py-3 font-semibold">{{ $paymentType->name }}</td>
                                        <td class="px-4 py-3">{{ $paymentType->is_active ? 'Activo' : 'Inactivo' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    @click='openEditPaymentType({ id: {{ $paymentType->id }}, name: @js($paymentType->name) })'
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600"
                                                    aria-label="Editar"
                                                    title="Editar"
                                                >
                                                    <img src="{{ asset('images/editar.svg') }}" alt="Editar" class="w-4 h-4 brightness-0 invert">
                                                </button>
                                                <form method="POST" action="{{ route('admin.ventas.payment-types.toggle-status', $paymentType) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white {{ $paymentType->is_active ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}" aria-label="{{ $paymentType->is_active ? 'Desactivar' : 'Activar' }}" title="{{ $paymentType->is_active ? 'Desactivar' : 'Activar' }}">
                                                        <img src="{{ asset('images/eliminar-descativar.svg') }}" alt="{{ $paymentType->is_active ? 'Desactivar' : 'Activar' }}" class="w-4 h-4 brightness-0 invert">
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-4 text-slate-500 text-center">No hay tipos de pago registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="showEditPaymentTypeModal" x-transition class="fixed inset-0 z-[90] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60" @click="closeEditPaymentType()"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Editar tipo de pago</h3>
                    <button type="button" @click="closeEditPaymentType()" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form :action="`{{ url('/admin/ventas/tipos-pago') }}/${editPaymentTypeId}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre del tipo de pago</label>
                        <input type="text" name="name" x-model="editPaymentTypeName" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeEditPaymentType()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="showQuickClientModal" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/55" @click="showQuickClientModal = false"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-900">Registrar cliente rápido</h3>
                    <button type="button" @click="showQuickClientModal = false" class="w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.ventas.clients.store') }}" class="p-6 space-y-4">
                    @csrf
                    @if ($errors->quickClient->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ $errors->quickClient->first() }}
                        </div>
                    @endif
                    <div class="grid grid-cols-1 gap-3">
                        <label class="block text-sm font-semibold text-slate-700">DNI / RUC</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="quickDocument" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Ej: 12345678 o 20123456789">
                            <button type="button" @click="lookupDocument" :disabled="quickLoading" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800 disabled:opacity-60" aria-label="Buscar" title="Buscar">
                                <span x-show="!quickLoading"><img src="{{ asset('images/buscar.svg') }}" alt="Buscar" class="w-4 h-4 brightness-0 invert"></span>
                                <span x-show="quickLoading" style="display:none;">Buscando...</span>
                            </button>
                        </div>
                        <template x-if="quickMessage">
                            <div class="rounded-lg px-3 py-2 text-sm"
                                 :class="quickMessageType === 'ok' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-700'">
                                <span x-text="quickMessage"></span>
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <input type="text" name="dni" x-model="quickDni" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="DNI (8) o RUC (11)" required>
                        <input type="text" name="full_name" x-model="quickFullName" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Nombre completo / Razón social" required>
                        <input type="text" name="phone" x-model="quickPhone" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Teléfono">
                        <input type="email" name="email" x-model="quickEmail" class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Correo">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">Guardar cliente</button>
                    </div>
                </form>
            </div>
        </div>

        <datalist id="sales-clients-list">
            @foreach ($clients as $client)
                <option value="{{ $client->code }} - {{ $client->full_name }} ({{ $client->dni }})"></option>
            @endforeach
        </datalist>
        <datalist id="sales-products-list">
            @foreach ($products as $product)
                <option value="{{ $product->code }} - {{ $product->name }} (S/ {{ number_format($product->price, 2) }})"></option>
            @endforeach
        </datalist>
        <datalist id="sales-rooms-list">
            @foreach ($rooms as $room)
                <option value="{{ $room->code }} - Hab. {{ $room->room_number }} ({{ $room->type }})"></option>
            @endforeach
        </datalist>
    </div>
</x-layouts.admin>
