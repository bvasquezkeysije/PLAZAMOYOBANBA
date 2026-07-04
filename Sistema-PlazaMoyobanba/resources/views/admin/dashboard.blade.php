<x-layouts.admin>
    <x-slot name="title">Dashboard</x-slot>

    @php
        $maxSalesByDay = max(1, $salesByDay->max('total'));
        $maxPaymentMix = max(1, $paymentMix->max('total'));
        $donutColors = ['#1d4ed8', '#f59e0b', '#0ea5e9', '#22c55e', '#a855f7', '#ef4444'];

        $paymentTotal = max(1, (float) $paymentMix->sum('total'));
        $startDeg = 0;
        $paymentSegments = [];
        foreach ($paymentMix as $i => $row) {
            $percent = ((float) $row->total / $paymentTotal) * 100;
            $deg = ($percent / 100) * 360;
            $color = $donutColors[$i % count($donutColors)];
            $paymentSegments[] = [
                'name' => $row->payment_name,
                'total' => (float) $row->total,
                'percent' => $percent,
                'color' => $color,
                'from' => $startDeg,
                'to' => $startDeg + $deg,
            ];
            $startDeg += $deg;
        }
        $paymentConic = count($paymentSegments)
            ? collect($paymentSegments)->map(fn ($seg) => "{$seg['color']} {$seg['from']}deg {$seg['to']}deg")->implode(', ')
            : '#e2e8f0 0deg 360deg';

        $documentTotal = max(1, (int) $documentMix->sum('qty'));
        $docStartDeg = 0;
        $documentSegments = [];
        foreach ($documentMix as $i => $row) {
            $percent = ((int) $row->qty / $documentTotal) * 100;
            $deg = ($percent / 100) * 360;
            $color = $donutColors[$i % count($donutColors)];
            $documentSegments[] = [
                'name' => strtoupper((string) $row->document_type),
                'qty' => (int) $row->qty,
                'percent' => $percent,
                'color' => $color,
                'from' => $docStartDeg,
                'to' => $docStartDeg + $deg,
            ];
            $docStartDeg += $deg;
        }
        $documentConic = count($documentSegments)
            ? collect($documentSegments)->map(fn ($seg) => "{$seg['color']} {$seg['from']}deg {$seg['to']}deg")->implode(', ')
            : '#e2e8f0 0deg 360deg';
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Ventas hoy</p>
                <p class="text-3xl font-bold text-blue-900">S/ {{ number_format($kpis['sales_today'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Ventas del mes</p>
                <p class="text-3xl font-bold text-blue-900">S/ {{ number_format($kpis['sales_month'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Tickets del mes</p>
                <p class="text-3xl font-bold text-blue-900">{{ $kpis['tickets_month'] }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Habitaciones activas</p>
                <p class="text-3xl font-bold text-blue-900">{{ $kpis['active_rooms'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
            <div class="lg:col-span-2 self-start space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Ventas últimos 7 días</h3>
                    <div class="space-y-2">
                        @foreach($salesByDay as $point)
                            <div class="grid grid-cols-[56px_1fr_110px] items-center gap-3">
                                <span class="text-xs text-slate-500">{{ $point['label'] }}</span>
                                <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-blue-700 rounded-full" style="width: {{ ($point['total'] / $maxSalesByDay) * 100 }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-700 text-right">S/ {{ number_format($point['total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Comprobantes por tipo</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                        <div class="flex items-center justify-center">
                            <div class="relative w-36 h-36 rounded-full" style="background: conic-gradient({{ $documentConic }});">
                                <div class="absolute inset-[20%] rounded-full bg-white border border-slate-100 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-slate-700">{{ $documentTotal }} total</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @forelse($documentSegments as $seg)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="inline-flex items-center gap-2 text-slate-700">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $seg['color'] }}"></span>
                                        {{ $seg['name'] }}
                                    </span>
                                    <span class="font-semibold text-slate-700">{{ $seg['qty'] }} ({{ number_format($seg['percent'], 1) }}%)</span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Sin comprobantes aún.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm self-start">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Medios de pago</h3>
                <div class="flex flex-col items-center gap-4">
                    <div class="relative w-40 h-40 rounded-full" style="background: conic-gradient({{ $paymentConic }});">
                        <div class="absolute inset-[18%] rounded-full bg-white flex flex-col items-center justify-center border border-slate-100">
                            <span class="text-[11px] text-slate-500">Total</span>
                            <span class="text-sm font-bold text-slate-800">S/ {{ number_format((float) $paymentMix->sum('total'), 2) }}</span>
                        </div>
                    </div>
                    <div class="w-full space-y-2">
                        @forelse($paymentSegments as $seg)
                            <div class="flex items-center justify-between text-xs">
                                <span class="inline-flex items-center gap-2 text-slate-700">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $seg['color'] }}"></span>
                                    {{ $seg['name'] }}
                                </span>
                                <span class="font-semibold text-slate-600">{{ number_format($seg['percent'], 1) }}%</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Sin ventas registradas.</p>
                        @endforelse
                    </div>
                </div>
                <div class="space-y-2 mt-4">
                    @foreach($paymentMix as $row)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-slate-700">{{ $row->payment_name }}</span>
                                <span class="text-xs font-semibold text-slate-600">S/ {{ number_format((float) $row->total, 2) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ (((float) $row->total) / $maxPaymentMix) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Clientes activos</p>
                <p class="text-2xl font-bold text-slate-800">{{ $kpis['active_clients'] }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Productos activos</p>
                <p class="text-2xl font-bold text-slate-800">{{ $kpis['active_products'] }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <p class="text-sm text-slate-500">Usuarios</p>
                <p class="text-2xl font-bold text-slate-800">{{ $kpis['users'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Top productos vendidos</h3>
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
                        <tr class="border-t"><td colspan="3" class="px-3 py-2 text-slate-500">Sin datos por ahora.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
