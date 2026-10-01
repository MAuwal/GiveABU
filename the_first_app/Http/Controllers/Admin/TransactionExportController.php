<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class TransactionExportController extends Controller
{
    public function export(Request $request)
    {
        $query = PaymentTransaction::with(['donor', 'project'])
            ->when($request->gateway,  fn($q) => $q->where('payment_gateway', $request->gateway))
            ->when($request->status,   fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sub) => $sub
                    ->where('payment_reference', 'like', $s)
                    ->orWhere('payment_gateway', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhereHas('donor', fn($d) => $d->where('name', 'like', $s)->orWhere('surname', 'like', $s)->orWhere('email', 'like', $s))
                );
            })
            ->when($request->period, function ($q) use ($request) {
                match($request->period) {
                    'today' => $q->whereDate('created_at', today()),
                    'week'  => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    'year'  => $q->whereYear('created_at', now()->year),
                    default => null,
                };
            })
            ->latest();

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'ID', 'Date', 'Gateway', 'Category', 'Event', 'Status',
                'Amount (NGN)', 'Fee (NGN)', 'Donor Name', 'Donor Email', 'Donor Phone',
                'Project', 'Payment Reference', 'Gateway Reference',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    fputcsv($handle, [
                        $t->id,
                        $t->created_at->format('Y-m-d H:i:s'),
                        ucfirst($t->payment_gateway),
                        ucfirst($t->category ?? 'N/A'),
                        ucfirst(str_replace(['.', '_'], ' ', $t->event_type)),
                        ucfirst($t->status),
                        number_format($t->amount ?? 0, 2, '.', ''),
                        number_format($t->fee ?? 0, 2, '.', ''),
                        optional($t->donor)->full_name ?? 'N/A',
                        optional($t->donor)->email ?? 'N/A',
                        optional($t->donor)->phone ?? 'N/A',
                        optional($t->project)->project_title ?? 'General',
                        $t->payment_reference ?? '',
                        $t->gateway_reference ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
