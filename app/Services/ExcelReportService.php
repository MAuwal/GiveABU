<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelReportService
{
    // Brand palette
    const BG_DARK    = 'FF064E3B';
    const BG_MID     = 'FF065F46';
    const BG_LIGHT   = 'FFD1FAE5';
    const BG_ALT     = 'FFF0FDF4';
    const BG_GRAY    = 'FFF9FAFB';
    const BG_BLUE_HD = 'FF1E3A5F';
    const BG_BLUE_LT = 'FFE0F2FE';
    const WHITE      = 'FFFFFFFF';
    const TEXT_DARK  = 'FF111827';
    const COMPLETED  = 'FFD1FAE5';
    const PENDING    = 'FFFEF3C7';
    const FAILED     = 'FFFEE2E2';
    const BORDER     = 'FFE5E7EB';
    const GOLD_BG    = 'FFFEF3C7';
    const SILVER_BG  = 'FFF1F5F9';
    const BRONZE_BG  = 'FFFDEBD0';

    private array $filters = [];
    private Spreadsheet $wb;

    public function build(array $filters, array $sheets): Spreadsheet
    {
        $this->filters = $filters;
        $this->wb = new Spreadsheet();
        $this->wb->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
        $this->wb->getProperties()
            ->setTitle('GIVE ABU Management Report')
            ->setSubject('Endowment Fund Report')
            ->setCreator('GIVE ABU Admin')
            ->setCompany('ABU Endowment Fund');

        $idx = 0;
        if (in_array('dashboard',    $sheets)) { $this->buildDashboard($idx++);    }
        if (in_array('donations',    $sheets)) { $this->buildDonations($idx++);    }
        if (in_array('transactions', $sheets)) { $this->buildTransactions($idx++); }
        if (in_array('top_donors',   $sheets)) { $this->buildTopDonors($idx++);    }
        if (in_array('trends',       $sheets)) { $this->buildTrends($idx++);       }

        // Remove PhpSpreadsheet's default blank sheet if we added our own
        while ($this->wb->getSheetCount() > $idx) {
            $this->wb->removeSheetByIndex($idx);
        }
        $this->wb->setActiveSheetIndex(0);
        return $this->wb;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────
    private function buildDashboard(int $idx): void
    {
        $ws = $idx === 0 ? $this->wb->getActiveSheet() : $this->wb->createSheet($idx);
        $ws->setTitle('DASHBOARD');

        foreach (['A' => 30, 'B' => 22, 'C' => 22, 'D' => 22, 'E' => 18] as $col => $w) {
            $ws->getColumnDimension($col)->setWidth($w);
        }

        // ── Title block ───────────────────────────────────────────
        $ws->mergeCells('A1:E1');
        $ws->setCellValue('A1', 'GIVE ABU ENDOWMENT FUND');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 20, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(40);

        $ws->mergeCells('A2:E2');
        $ws->setCellValue('A2', 'MANAGEMENT REPORT  •  Donation & Transaction Summary');
        $ws->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_MID]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(2)->setRowHeight(22);

        $period  = ($this->filters['date_from'] ?? '')
            ? ($this->filters['date_from'] . '  →  ' . ($this->filters['date_to'] ?: 'Today'))
            : 'All Time';
        $ws->mergeCells('A3:C3');
        $ws->setCellValue('A3', 'Period: ' . $period);
        $ws->mergeCells('D3:E3');
        $ws->setCellValue('D3', 'Generated: ' . now()->format('d M Y, g:i A'));
        $ws->getStyle('A3:E3')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_LIGHT]],
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => self::BG_DARK]],
        ]);
        $ws->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $ws->getRowDimension(3)->setRowHeight(18);

        // ── KPI block ─────────────────────────────────────────────
        $dBase = $this->donationQuery();
        $tBase = $this->transactionQuery();

        $dComp = (float)(clone $dBase)->whereIn('status', ['completed', 'success', 'paid'])->sum('amount');
        $dPend = (float)(clone $dBase)->where('status', 'pending')->sum('amount');
        $dFail = (float)(clone $dBase)->where('status', 'failed')->sum('amount');
        $dCnt  = (int)(clone $dBase)->count();

        $tComp = (float)(clone $tBase)->whereIn('status', ['completed', 'success'])->sum('amount');
        $tPend = (float)(clone $tBase)->where('status', 'pending')->sum('amount');
        $tFail = (float)(clone $tBase)->where('status', 'failed')->sum('amount');
        $tCnt  = (int)(clone $tBase)->count();

        $row = 5;
        $this->sectionHeader($ws, "A{$row}:E{$row}", 'KEY PERFORMANCE INDICATORS');
        $row++;
        $this->tableHeader($ws, $row, ['Metric', 'Donations', 'Transactions', 'Combined'], 'E');
        $row++;

        $kpis = [
            ['Total Records',      $dCnt,  $tCnt,  $dCnt  + $tCnt,  'n'],
            ['✓  Completed (₦)',   $dComp, $tComp, $dComp + $tComp, 'c'],
            ['⏳  Pending (₦)',    $dPend, $tPend, $dPend + $tPend, 'c'],
            ['✗  Failed (₦)',     $dFail, $tFail, $dFail + $tFail, 'c'],
            ['GRAND TOTAL (₦)',    $dComp+$dPend+$dFail, $tComp+$tPend+$tFail, $dComp+$dPend+$dFail+$tComp+$tPend+$tFail, 'c'],
        ];

        foreach ($kpis as $i => [$lbl, $dv, $tv, $cv, $fmt]) {
            $ws->setCellValue("A{$row}", $lbl);
            $ws->setCellValue("B{$row}", $dv);
            $ws->setCellValue("C{$row}", $tv);
            $ws->setCellValue("D{$row}", $cv);
            $numFmt = $fmt === 'c' ? '#,##0.00' : '#,##0';
            $ws->getStyle("B{$row}:D{$row}")->getNumberFormat()->setFormatCode($numFmt);
            $bg = $i % 2 === 0 ? self::BG_GRAY : self::WHITE;
            $ws->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
            if ($i === 4) {
                $ws->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
                $ws->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_LIGHT);
            }
            $row++;
        }

        // ── Gateway block ─────────────────────────────────────────
        $row++;
        $this->sectionHeader($ws, "A{$row}:E{$row}", 'GATEWAY BREAKDOWN  (Completed Transactions)');
        $row++;
        $this->tableHeader($ws, $row, ['Gateway', 'Count', 'Amount (₦)', 'Fees (₦)', 'Avg (₦)'], 'E');
        $row++;

        $gw = PaymentTransaction::query()
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->when($this->filters['project']   ?? null, fn($q) => $q->where('project_id', $this->filters['project']))
            ->when($this->filters['programme'] ?? null, function ($q) {
                $q->whereHas('donor', function ($sub) {
                    $sub->whereIn('donor_type', ['alumni', 'addressable_alumni', 'addresssible_alumni'])
                        ->where('department_id', $this->filters['programme']);
                });
            })
            ->whereIn('status', ['completed', 'success'])
            ->selectRaw('payment_gateway, count(*) as cnt, COALESCE(sum(amount),0) as total, COALESCE(sum(fee),0) as fees')
            ->groupBy('payment_gateway')->get()->keyBy('payment_gateway');

        foreach (['paystack' => 'Paystack', 'squad' => 'Squad'] as $key => $label) {
            $g   = $gw[$key] ?? null;
            $cnt = (int)($g?->cnt   ?? 0);
            $amt = (float)($g?->total ?? 0);
            $fee = (float)($g?->fees  ?? 0);
            $avg = $cnt > 0 ? $amt / $cnt : 0;
            $ws->setCellValue("A{$row}", $label);
            $ws->setCellValue("B{$row}", $cnt);
            $ws->setCellValue("C{$row}", $amt);
            $ws->setCellValue("D{$row}", $fee);
            $ws->setCellValue("E{$row}", $avg);
            $ws->getStyle("C{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        // ── Donor type block ──────────────────────────────────────
        $row++;
        $this->sectionHeader($ws, "A{$row}:E{$row}", 'DONOR TYPE BREAKDOWN');
        $row++;
        $this->tableHeader($ws, $row, ['Donor Type', 'Count', 'Total Donated (₦)', '', ''], 'E');
        $row++;

        $dtypes = Donor::selectRaw('donor_type, count(*) as cnt')->groupBy('donor_type')->get();
        foreach ($dtypes as $dt) {
            $total = Donation::whereIn('status', ['completed', 'success', 'paid'])
                ->whereHas('donor', fn($q) => $q->where('donor_type', $dt->donor_type))
                ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
                ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
                ->when($this->filters['project']   ?? null, fn($q) => $q->where('project_id', $this->filters['project']))
                ->when($this->filters['programme'] ?? null, function ($q) {
                    $q->whereHas('donor', function ($sub) {
                        $sub->whereIn('donor_type', ['alumni', 'addressable_alumni', 'addresssible_alumni'])
                            ->where('department_id', $this->filters['programme']);
                    });
                })
                ->sum('amount');
            $ws->setCellValue("A{$row}", ucfirst($dt->donor_type ?? 'Unknown'));
            $ws->setCellValue("B{$row}", (int)$dt->cnt);
            $ws->setCellValue("C{$row}", (float)$total);
            $ws->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        // Border styling for the dashboard table
        $ws->getStyle("A5:E" . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDER);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DONATIONS
    // ─────────────────────────────────────────────────────────────────────────
    private function buildDonations(int $idx): void
    {
        $ws = $idx === 0 ? $this->wb->getActiveSheet() : $this->wb->createSheet($idx);
        $ws->setTitle('DONATIONS');

        foreach (['A'=>6,'B'=>18,'C'=>28,'D'=>28,'E'=>16,'F'=>14,'G'=>28,'H'=>16,'I'=>14,'J'=>26] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }

        $ws->mergeCells('A1:J1');
        $ws->setCellValue('A1', 'DONATIONS DETAIL REPORT');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(28);

        $this->tableHeader($ws, 2, ['#', 'Date', 'Donor Name', 'Email', 'Phone', 'Type', 'Project', 'Amount (₦)', 'Status', 'Reference'], 'J');
        $ws->getStyle('A2:J2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_MID]],
        ]);
        $ws->freezePane('A3');
        $ws->setAutoFilter('A2:J2');

        $row = 3;
        $this->donationQuery()->with(['donor', 'project'])->chunk(500, function ($recs) use ($ws, &$row) {
            foreach ($recs as $d) {
                $status = strtolower($d->status ?? '');
                $ws->setCellValue("A{$row}", $row - 2);
                $ws->setCellValue("B{$row}", $d->created_at->format('Y-m-d H:i'));
                $ws->setCellValue("C{$row}", optional($d->donor)->full_name ?? 'N/A');
                $ws->setCellValue("D{$row}", optional($d->donor)->email ?? 'N/A');
                $ws->setCellValue("E{$row}", optional($d->donor)->phone ?? 'N/A');
                $ws->setCellValue("F{$row}", ucfirst(optional($d->donor)->donor_type ?? 'N/A'));
                $ws->setCellValue("G{$row}", optional($d->project)->project_title ?? 'General');
                $ws->setCellValue("H{$row}", (float)($d->amount ?? 0));
                $ws->setCellValue("I{$row}", ucfirst($d->status ?? ''));
                $ws->setCellValue("J{$row}", $d->payment_reference ?? '');
                $ws->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $statusBg = match(true) {
                    in_array($status, ['completed','success','paid']) => self::COMPLETED,
                    $status === 'pending' => self::PENDING,
                    $status === 'failed'  => self::FAILED,
                    default               => self::WHITE,
                };
                $ws->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusBg);
                if ($row % 2 === 0) {
                    $ws->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_GRAY);
                }
                $row++;
            }
        });

        if ($row > 3) {
            $ws->getStyle("A2:J" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDER);
            $ws->setCellValue("G{$row}", 'TOTAL');
            $ws->setCellValue("H{$row}", "=SUM(H3:H" . ($row - 1) . ")");
            $ws->getStyle("G{$row}:J{$row}")->getFont()->setBold(true);
            $ws->getStyle("H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle("G{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_LIGHT);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSACTIONS
    // ─────────────────────────────────────────────────────────────────────────
    private function buildTransactions(int $idx): void
    {
        $ws = $idx === 0 ? $this->wb->getActiveSheet() : $this->wb->createSheet($idx);
        $ws->setTitle('TRANSACTIONS');

        foreach (['A'=>6,'B'=>18,'C'=>14,'D'=>14,'E'=>26,'F'=>16,'G'=>14,'H'=>14,'I'=>28,'J'=>28,'K'=>26] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }

        $ws->mergeCells('A1:K1');
        $ws->setCellValue('A1', 'TRANSACTIONS DETAIL REPORT');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_BLUE_HD]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(28);

        $this->tableHeader($ws, 2, ['#','Date','Gateway','Category','Event','Amount (₦)','Fee (₦)','Status','Donor','Project','Reference'], 'K');
        $ws->getStyle('A2:K2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_BLUE_HD]],
        ]);
        $ws->freezePane('A3');
        $ws->setAutoFilter('A2:K2');

        $row = 3;
        $this->transactionQuery()->with(['donor', 'project'])->chunk(500, function ($recs) use ($ws, &$row) {
            foreach ($recs as $t) {
                $status = strtolower($t->status ?? '');
                $ws->setCellValue("A{$row}", $row - 2);
                $ws->setCellValue("B{$row}", $t->created_at->format('Y-m-d H:i'));
                $ws->setCellValue("C{$row}", ucfirst($t->payment_gateway ?? ''));
                $ws->setCellValue("D{$row}", ucfirst($t->category ?? 'N/A'));
                $ws->setCellValue("E{$row}", ucfirst(str_replace(['.','_'], ' ', $t->event_type ?? '')));
                $ws->setCellValue("F{$row}", (float)($t->amount ?? 0));
                $ws->setCellValue("G{$row}", (float)($t->fee ?? 0));
                $ws->setCellValue("H{$row}", ucfirst($t->status ?? ''));
                $ws->setCellValue("I{$row}", optional($t->donor)->full_name ?? 'N/A');
                $ws->setCellValue("J{$row}", optional($t->project)->project_title ?? 'General');
                $ws->setCellValue("K{$row}", $t->payment_reference ?? '');
                $ws->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                $statusBg = match(true) {
                    in_array($status, ['completed','success']) => self::COMPLETED,
                    $status === 'pending' => self::PENDING,
                    $status === 'failed'  => self::FAILED,
                    default               => self::WHITE,
                };
                $ws->getStyle("H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusBg);
                if ($row % 2 === 0) {
                    $ws->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_BLUE_LT);
                }
                $row++;
            }
        });

        if ($row > 3) {
            $ws->getStyle("A2:K" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDER);
            $ws->setCellValue("E{$row}", 'TOTAL');
            $ws->setCellValue("F{$row}", "=SUM(F3:F" . ($row - 1) . ")");
            $ws->setCellValue("G{$row}", "=SUM(G3:G" . ($row - 1) . ")");
            $ws->getStyle("E{$row}:G{$row}")->getFont()->setBold(true);
            $ws->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle("E{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_BLUE_LT);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOP DONORS
    // ─────────────────────────────────────────────────────────────────────────
    private function buildTopDonors(int $idx): void
    {
        $ws = $idx === 0 ? $this->wb->getActiveSheet() : $this->wb->createSheet($idx);
        $ws->setTitle('TOP DONORS');

        foreach (['A'=>8,'B'=>30,'C'=>32,'D'=>16,'E'=>14,'F'=>20] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }

        $ws->mergeCells('A1:F1');
        $ws->setCellValue('A1', 'TOP DONORS RANKING  —  Top 20 by Total Donated');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(28);

        $this->tableHeader($ws, 2, ['Rank', 'Donor Name', 'Email', 'Type', '# Gifts', 'Total (₦)'], 'F');
        $ws->getStyle('A2:F2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_MID]],
        ]);
        $ws->freezePane('A3');

        $donors = Donation::where('donations.status', 'completed')
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('donations.created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('donations.created_at', '<=', $this->filters['date_to']))
            ->join('donors', 'donations.donor_id', '=', 'donors.id')
            ->selectRaw('donors.surname, donors.name, donors.email, donors.donor_type, count(*) as gifts, COALESCE(sum(donations.amount),0) as total')
            ->groupBy('donors.id','donors.surname','donors.name','donors.email','donors.donor_type')
            ->orderByDesc('total')->take(20)->get();

        $row = 3;
        foreach ($donors as $rank => $d) {
            $rn = $rank + 1;
            $bg = match($rn) { 1 => self::GOLD_BG, 2 => self::SILVER_BG, 3 => self::BRONZE_BG, default => ($row % 2 === 0 ? self::BG_GRAY : self::WHITE) };
            $ws->setCellValue("A{$row}", $rn);
            $ws->setCellValue("B{$row}", trim(($d->surname ?? '') . ' ' . ($d->name ?? '')));
            $ws->setCellValue("C{$row}", $d->email ?? '');
            $ws->setCellValue("D{$row}", ucfirst($d->donor_type ?? 'N/A'));
            $ws->setCellValue("E{$row}", (int)$d->gifts);
            $ws->setCellValue("F{$row}", (float)$d->total);
            $ws->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
            $ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($rn <= 3) { $ws->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); }
            $row++;
        }

        $ws->getStyle("A2:F" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDER);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRENDS
    // ─────────────────────────────────────────────────────────────────────────
    private function buildTrends(int $idx): void
    {
        $ws = $idx === 0 ? $this->wb->getActiveSheet() : $this->wb->createSheet($idx);
        $ws->setTitle('TRENDS');

        foreach (['A'=>16,'B'=>18,'C'=>18,'D'=>18,'E'=>18,'F'=>18,'G'=>18] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }

        $ws->mergeCells('A1:G1');
        $ws->setCellValue('A1', 'MONTHLY DONATION TRENDS');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(28);

        $this->tableHeader($ws, 2, ['Month', 'Total (₦)', 'Completed (₦)', 'Pending (₦)', 'Failed (₦)', 'Paystack (₦)', 'Squad (₦)'], 'G');
        $ws->getStyle('A2:G2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_MID]],
        ]);
        $ws->freezePane('A3');
        $ws->setAutoFilter('A2:G2');

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthDon = Donation::query()
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->selectRaw("{$monthSql} as month,
                COALESCE(sum(amount),0) as total,
                COALESCE(sum(CASE WHEN status IN ('completed','success','paid') THEN amount ELSE 0 END),0) as completed,
                COALESCE(sum(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending,
                COALESCE(sum(CASE WHEN status='failed'  THEN amount ELSE 0 END),0) as failed")
            ->groupBy('month')->orderBy('month')->get()->keyBy('month');

        $monthGw = PaymentTransaction::query()
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->whereIn('status', ['completed', 'success'])
            ->selectRaw("{$monthSql} as month, payment_gateway, COALESCE(sum(amount),0) as total")
            ->groupBy('month', 'payment_gateway')->get()->groupBy('month');

        $months = $monthDon->keys()->merge($monthGw->keys())->unique()->sort()->values();

        $row = 3;
        foreach ($months as $m) {
            $d  = $monthDon[$m] ?? null;
            $gw = $monthGw[$m]  ?? collect();
            $ps = (float)($gw->firstWhere('payment_gateway', 'paystack')?->total ?? 0);
            $sq = (float)($gw->firstWhere('payment_gateway', 'squad')?->total    ?? 0);

            $ws->setCellValue("A{$row}", Carbon::parse($m . '-01')->format('M Y'));
            $ws->setCellValue("B{$row}", (float)($d?->total     ?? 0));
            $ws->setCellValue("C{$row}", (float)($d?->completed ?? 0));
            $ws->setCellValue("D{$row}", (float)($d?->pending   ?? 0));
            $ws->setCellValue("E{$row}", (float)($d?->failed    ?? 0));
            $ws->setCellValue("F{$row}", $ps);
            $ws->setCellValue("G{$row}", $sq);
            $ws->getStyle("B{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            if ($row % 2 === 0) {
                $ws->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_GRAY);
            }
            $row++;
        }

        if ($row > 3) {
            $ws->setCellValue("A{$row}", 'TOTAL');
            foreach (['B','C','D','E','F','G'] as $c) {
                $ws->setCellValue("{$c}{$row}", "=SUM({$c}3:{$c}" . ($row - 1) . ")");
                $ws->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $ws->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
            $ws->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BG_LIGHT);
            $ws->getStyle("A2:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDER);

            // Charts are generated by VBA (BuildMonthlyBarChart macro) to ensure full Excel compatibility
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
    private function sectionHeader($ws, string $range, string $label): void
    {
        $ws->mergeCells($range);
        [$cell] = explode(':', $range);
        $ws->setCellValue($cell, $label);
        $ws->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => self::WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => self::BG_MID]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);
        preg_match('/(\d+)/', explode(':', $range)[0], $m);
        $ws->getRowDimension((int)($m[1] ?? 1))->setRowHeight(20);
    }

    private function tableHeader($ws, int $row, array $headers, string $lastCol): void
    {
        $col = 'A';
        foreach ($headers as $h) {
            $ws->setCellValue($col . $row, $h);
            $col++;
        }
        $ws->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF374151']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE5E7EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(18);
    }

    private function donationQuery()
    {
        return Donation::query()
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->when($this->filters['project']   ?? null, fn($q) => $q->where('project_id', $this->filters['project']))
            ->when($this->filters['programme'] ?? null, function ($q) {
                $q->whereHas('donor', function ($sub) {
                    $sub->whereIn('donor_type', ['alumni', 'addressable_alumni', 'addresssible_alumni'])
                        ->where('department_id', $this->filters['programme']);
                });
            })
            ->when($this->filters['search']    ?? null, function ($q) {
                $s = '%' . $this->filters['search'] . '%';
                $q->where(fn($sub) => $sub
                    ->whereHas('donor', fn($d) => $d->where('name', 'like', $s)->orWhere('surname', 'like', $s)->orWhere('email', 'like', $s))
                    ->orWhereHas('project', fn($p) => $p->where('project_title', 'like', $s))
                    ->orWhere('payment_reference', 'like', $s)
                );
            })
            ->latest();
    }

    private function transactionQuery()
    {
        return PaymentTransaction::query()
            ->when($this->filters['date_from'] ?? null, fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to']   ?? null, fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->when($this->filters['project']   ?? null, fn($q) => $q->where('project_id', $this->filters['project']))
            ->when($this->filters['programme'] ?? null, function ($q) {
                $q->whereHas('donor', function ($sub) {
                    $sub->whereIn('donor_type', ['alumni', 'addressable_alumni', 'addresssible_alumni'])
                        ->where('department_id', $this->filters['programme']);
                });
            })
            ->when($this->filters['search']    ?? null, function ($q) {
                $s = '%' . $this->filters['search'] . '%';
                $q->where(fn($sub) => $sub
                    ->whereHas('donor', fn($d) => $d->where('name', 'like', $s)->orWhere('surname', 'like', $s)->orWhere('email', 'like', $s))
                    ->orWhere('payment_reference', 'like', $s)
                    ->orWhere('payment_gateway', 'like', $s)
                );
            })
            ->latest();
    }
}
