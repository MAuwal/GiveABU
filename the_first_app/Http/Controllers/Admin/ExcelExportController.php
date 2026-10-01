<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelReportService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportController extends Controller
{
    public function export(Request $request, ExcelReportService $service)
    {
        $filters = [
            'date_from' => $request->date_from ?? '',
            'date_to'   => $request->date_to   ?? '',
            'project'   => $request->project   ?? '',
            'search'    => $request->search    ?? '',
            'gateway'   => $request->gateway   ?? '',
            'status'    => $request->status    ?? '',
            'period'    => $request->period    ?? '',
        ];

        $sheets = $request->sheets
            ? explode(',', $request->sheets)
            : ['dashboard', 'donations', 'transactions', 'top_donors', 'trends'];

        $context  = $request->context ?? 'reports';
        $filename = 'GIVEABU_Report_' . now()->format('Y-m-d_His') . '.xlsx';

        $workbook = $service->build($filters, $sheets);

        return response()->streamDownload(function () use ($workbook) {
            $writer = new Xlsx($workbook);

            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
