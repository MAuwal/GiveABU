<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class ReportExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Donation::with(['donor', 'project'])
            ->when($request->project,   fn($q) => $q->where('project_id', $request->project))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->search, function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sub) => $sub
                    ->whereHas('donor', fn($d) => $d
                        ->where('name', 'like', $s)
                        ->orWhere('surname', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s)
                        ->orWhere('organization_name', 'like', $s)
                    )
                    ->orWhereHas('project', fn($p) => $p->where('project_title', 'like', $s))
                    ->orWhere('payment_reference', 'like', $s)
                    ->orWhere('status', 'like', $s)
                );
            })
            ->latest();

        $filename = 'donations_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Date', 'Donor Name', 'Donor Email', 'Donor Phone', 'Donor Type',
                'Project', 'Donation Type', 'Amount (NGN)', 'Status', 'Payment Reference',
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $d) {
                    fputcsv($handle, [
                        $d->id,
                        $d->created_at->format('Y-m-d H:i:s'),
                        optional($d->donor)->full_name ?? 'N/A',
                        optional($d->donor)->email ?? 'N/A',
                        optional($d->donor)->phone ?? 'N/A',
                        ucfirst(optional($d->donor)->donor_type ?? 'N/A'),
                        optional($d->project)->project_title ?? 'General',
                        ucfirst($d->type ?? 'N/A'),
                        number_format($d->amount ?? 0, 2, '.', ''),
                        ucfirst($d->status),
                        $d->payment_reference ?? '',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
