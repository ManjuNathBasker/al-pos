<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $purchases;

    public function __construct($purchases)
    {
        $this->purchases = $purchases;
    }

    public function collection()
    {
        return $this->purchases;
    }

    public function headings(): array
    {
        return [
            'Reference No',
            'Date',
            'Supplier',
            'Purchase Status',
            'Payment Status',
            'Total Amount',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->reference_no,
            $purchase->purchase_date,
            $purchase->supplier ? $purchase->supplier->name : 'N/A',
            ucfirst($purchase->status),
            ucfirst($purchase->payment_status),
            $purchase->total_amount,
        ];
    }
}
