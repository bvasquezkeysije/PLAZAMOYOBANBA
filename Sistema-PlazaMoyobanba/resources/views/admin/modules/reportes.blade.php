<x-layouts.admin>
    <x-slot name="title">Reportes</x-slot>

    @php
        $maxPayment = max(1, $paymentData->max('total'));
        $donutColors = ['#1d4ed8', '#f59e0b', '#0ea5e9', '#22c55e', '#a855f7', '#ef4444', '#14b8a6'];
        $paymentTotal = max(1, (float) $paymentData->sum('total'));
        $paymentStartDeg = 0;
        $paymentSegments = [];
        foreach ($paymentData as $i => $row) {
            $percent = ((float) $row->total / $paymentTotal) * 100;
            $deg = ($percent / 100) * 360;
            $color = $donutColors[$i % count($donutColors)];
            $paymentSegments[] = [
                'name' => $row->payment_name,
                'percent' => $percent,
                'color' => $color,
                'from' => $paymentStartDeg,
                'to' => $paymentStartDeg + $deg,
            ];
            $paymentStartDeg += $deg;
        }
        $paymentConic = count($paymentSegments)
            ? collect($paymentSegments)->map(fn ($seg) => "{$seg['color']} {$seg['from']}deg {$seg['to']}deg")->implode(', ')
            : '#e2e8f0 0deg 360deg';

        $productTotal = max(1, (float) $topProducts->sum('total'));
        $productStartDeg = 0;
        $productSegments = [];
        foreach ($topProducts as $i => $row) {
            $percent = ((float) $row->total / $productTotal) * 100;
            $deg = ($percent / 100) * 360;
            $color = $donutColors[$i % count($donutColors)];
            $productSegments[] = [
                'name' => $row->name,
                'percent' => $percent,
                'color' => $color,
                'from' => $productStartDeg,
                'to' => $productStartDeg + $deg,
            ];
            $productStartDeg += $deg;
        }
        $productConic = count($productSegments)
            ? collect($productSegments)->map(fn ($seg) => "{$seg['color']} {$seg['from']}deg {$seg['to']}deg")->implode(', ')
            : '#e2e8f0 0deg 360deg';
    @endphp

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('admin.reportes') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Desde</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hasta</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-900 text-white text-sm font-semibold hover:bg-blue-800" aria-label="Filtrar" title="Filtrar">
                        <img src="{{ asset('images/flitro.svg') }}" alt="Filtrar" class="w-4 h-4 brightness-0 invert">
                    </button>
                    <a href="{{ route('admin.reportes') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50" aria-label="Limpiar" title="Limpiar">
                        <img src="{{ asset('images/limpiar.svg') }}" alt="Limpiar" class="w-4 h-4">
                    </a>
                </div>
                <div class="md:text-right">
                    <a href="{{ route('admin.reportes.sales.csv', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-semibold hover:bg-emerald-100" aria-label="Descargar Excel (CSV)" title="Descargar Excel (CSV)">
                        <img src="{{ asset('images/exel-descragar.svg') }}" alt="Descargar Excel (CSV)" class="w-4 h-4">
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total vendido</p>
                <p class="text-3xl font-bold text-blue-900">S/ {{ number_format($summary['total_sales'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">N° comprobantes</p>
                <p class="text-3xl font-bold text-blue-900">{{ $summary['tickets'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Distribución por pago</h3>
                <div class="flex flex-col items-center gap-4">
                    <div class="relative w-44 h-44 rounded-full" style="background: conic-gradient({{ $paymentConic }});">
                        <div class="absolute inset-[20%] rounded-full bg-white border border-slate-100 flex items-center justify-center">
                            <span class="text-xs font-semibold text-slate-700">Pagos</span>
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-1 gap-2">
                        @foreach($paymentSegments as $seg)
                            <div class="flex items-center justify-between text-xs">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $seg['color'] }}"></span>
                                    {{ $seg['name'] }}
                                </span>
                                <span class="font-semibold">{{ number_format($seg['percent'], 1) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Distribución por productos</h3>
                <div class="flex flex-col items-center gap-4">
                    <div class="relative w-44 h-44 rounded-full" style="background: conic-gradient({{ $productConic }});">
                        <div class="absolute inset-[20%] rounded-full bg-white border border-slate-100 flex items-center justify-center">
                            <span class="text-xs font-semibold text-slate-700">Productos</span>
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-1 gap-2">
                        @foreach($productSegments as $seg)
                            <div class="flex items-center justify-between text-xs">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $seg['color'] }}"></span>
                                    {{ $seg['name'] }}
                                </span>
                                <span class="font-semibold">{{ number_format($seg['percent'], 1) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Ventas por tipo de pago</h3>
                <div class="space-y-3">
                    @forelse($paymentData as $row)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-700">{{ $row->payment_name }}</span>
                                <span class="font-semibold text-slate-700">S/ {{ number_format((float) $row->total, 2) }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-blue-700 rounded-full" style="width: {{ (((float) $row->total) / $maxPayment) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Sin datos para el rango seleccionado.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Top productos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Producto</th>
                                <th class="px-3 py-2 text-left">Cantidad</th>
                                <th class="px-3 py-2 text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($topProducts as $item)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $item->name }}</td>
                                <td class="px-3 py-2">{{ (int) $item->qty }}</td>
                                <td class="px-3 py-2">S/ {{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr class="border-t"><td colspan="3" class="px-3 py-2 text-slate-500">Sin productos vendidos en este rango.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-semibold text-slate-800">Detalle de comprobantes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-4 py-3">Comprobante</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Pago</th>
                            <th class="px-4 py-3">Subtotal</th>
                            <th class="px-4 py-3">IGV</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($sales as $sale)
                        <tr class="border-t">
                            <td class="px-4 py-3 font-semibold">{{ $sale->code }}</td>
                            <td class="px-4 py-3">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $sale->client->full_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $sale->paymentType->name ?? '-' }}</td>
                            <td class="px-4 py-3">S/ {{ number_format((float) ($sale->subtotal ?? 0), 2) }}</td>
                            <td class="px-4 py-3">S/ {{ number_format((float) ($sale->igv ?? 0), 2) }}</td>
                            <td class="px-4 py-3 font-semibold">S/ {{ number_format((float) $sale->total, 2) }}</td>
                            <td class="px-4 py-3">{{ strtoupper((string) $sale->status) }}</td>
                        </tr>
                    @empty
                        <tr class="border-t"><td colspan="8" class="px-4 py-3 text-slate-500">Sin ventas en este rango.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
