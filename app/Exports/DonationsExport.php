<?php

namespace App\Exports;

use App\Models\Donation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Database\Eloquent\Builder;

class DonationsExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    protected $query;
    protected $totalAmount;
    protected $count;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        // Clone query to calculate total without affecting the main query execution
        $this->totalAmount = (clone $query)->sum('amount');
        $this->count = (clone $query)->count();
    }

    public function query()
    {
        return $this->query;
    }

    public function map($donation): array
    {
        return [
            $donation->created_at->format('Y-m-d H:i'),
            $donation->payment_reference,
            $donation->donor->full_name ?? 'N/A',
            $donation->donor->email ?? 'N/A',
            $donation->donor->phone ?? 'N/A',
            $donation->donor->faculty->current_name ?? 'N/A',
            $donation->donor->department->current_name ?? 'N/A',
            $donation->project->project_title ?? 'General Endowment',
            number_format($donation->amount, 2),
            ucfirst($donation->status),
            ucfirst($donation->type),
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reference',
            'Donor Name',
            'Email',
            'Phone',
            'Faculty',
            'Department',
            'Project / Purpose',
            'Amount (NGN)',
            'Status',
            'Type',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = $this->count + 2; // +1 for header, +1 for next row
                
                // Add Total Label
                $event->sheet->setCellValue('H' . $lastRow, 'TOTAL:');
                
                // Add Total Amount in Amount Column (I)
                $event->sheet->setCellValue('I' . $lastRow, number_format($this->totalAmount, 2));
                
                // Styling
                $event->sheet->getStyle('H' . $lastRow . ':I' . $lastRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFEFEF'],
                    ],
                ]);
            },
        ];
    }
}
