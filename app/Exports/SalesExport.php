<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Date',
            'Customer',
            'Service Type',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total Amount',
            'Total Paid',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->customer ? $order->customer->name : 'Walk-in',
            ucfirst($order->service_type),
            ucfirst($order->status),
            $order->subtotal,
            $order->tax_amount,
            $order->discount_amount,
            $order->total_amount,
            $order->total_paid,
        ];
    }
}
