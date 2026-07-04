<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Room;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $kpis = [
            'sales_today' => (float) Sale::query()->whereDate('created_at', $today)->sum('total'),
            'sales_month' => (float) Sale::query()->whereBetween('created_at', [$monthStart, $monthEnd])->sum('total'),
            'tickets_month' => (int) Sale::query()->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'active_rooms' => (int) Room::query()->where('active', true)->count(),
            'active_products' => (int) Product::query()->where('active', true)->count(),
            'active_clients' => (int) Client::query()->where('active', true)->count(),
            'users' => (int) User::query()->count(),
        ];

        $last7Days = collect(range(6, 0))
            ->map(fn ($daysAgo) => Carbon::today()->subDays($daysAgo))
            ->values();

        $salesByDayRaw = Sale::query()
            ->selectRaw("DATE(created_at) as sale_day, SUM(total) as total")
            ->whereDate('created_at', '>=', Carbon::today()->subDays(6))
            ->groupBy('sale_day')
            ->pluck('total', 'sale_day');

        $salesByDay = $last7Days->map(function (Carbon $day) use ($salesByDayRaw) {
            $key = $day->toDateString();
            return [
                'label' => $day->format('d/m'),
                'total' => (float) ($salesByDayRaw[$key] ?? 0),
            ];
        })->values();

        $paymentMix = Sale::query()
            ->leftJoin('payment_types', 'payment_types.id', '=', 'sales.payment_type_id')
            ->selectRaw("COALESCE(payment_types.name, 'Sin tipo') as payment_name, SUM(sales.total) as total")
            ->groupBy('payment_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $documentMix = Sale::query()
            ->selectRaw("UPPER(COALESCE(document_type, 'boleta')) as document_type, COUNT(*) as qty")
            ->groupBy('document_type')
            ->orderByDesc('qty')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as total')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('kpis', 'salesByDay', 'paymentMix', 'documentMix', 'topProducts'));
    }
}
