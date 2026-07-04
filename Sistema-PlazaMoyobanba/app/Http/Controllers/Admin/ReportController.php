<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->resolveDates($request);

        $sales = Sale::query()
            ->with(['client', 'paymentType', 'user'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_sales' => (float) Sale::query()
                ->whereDate('created_at', '>=', $from->toDateString())
                ->whereDate('created_at', '<=', $to->toDateString())
                ->sum('total'),
            'tickets' => (int) Sale::query()
                ->whereDate('created_at', '>=', $from->toDateString())
                ->whereDate('created_at', '<=', $to->toDateString())
                ->count(),
        ];

        $paymentData = Sale::query()
            ->leftJoin('payment_types', 'payment_types.id', '=', 'sales.payment_type_id')
            ->selectRaw("COALESCE(payment_types.name, 'Sin tipo') as payment_name, SUM(sales.total) as total")
            ->whereDate('sales.created_at', '>=', $from->toDateString())
            ->whereDate('sales.created_at', '<=', $to->toDateString())
            ->groupBy('payment_name')
            ->orderByDesc('total')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as total')
            ->whereDate('sales.created_at', '>=', $from->toDateString())
            ->whereDate('sales.created_at', '<=', $to->toDateString())
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return view('admin.modules.reportes', compact('sales', 'summary', 'paymentData', 'topProducts', 'from', 'to'));
    }

    public function exportSalesCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDates($request);

        $rows = Sale::query()
            ->with(['client', 'paymentType', 'user'])
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->latest()
            ->get();

        $filename = 'reporte-ventas-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Comprobante', 'Fecha', 'Cliente', 'Documento', 'Pago', 'Subtotal', 'IGV', 'Total', 'Estado', 'Vendedor']);

            foreach ($rows as $sale) {
                fputcsv($out, [
                    $sale->code,
                    optional($sale->created_at)->format('d/m/Y H:i'),
                    $sale->client->full_name ?? '-',
                    $sale->client->dni ?? '-',
                    $sale->paymentType->name ?? '-',
                    number_format((float) ($sale->subtotal ?? 0), 2, '.', ''),
                    number_format((float) ($sale->igv ?? 0), 2, '.', ''),
                    number_format((float) $sale->total, 2, '.', ''),
                    $sale->status,
                    $sale->user->name ?? '-',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function resolveDates(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to) : now()->endOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }
}
