<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $inventory;

    public function __construct($inventory)
    {
        $this->inventory = $inventory;
    }

    public function collection()
    {
        return $this->inventory;
    }

    public function headings(): array
    {
        return [
            'Item Name',
            'Item Code',
            'Quantity',
            'Unit',
            'Min Quantity',
            'Unit Cost',
            'Total Value',
        ];
    }

    public function map($item): array
    {
        return [
            $item->name,
            $item->code,
            $item->current_stock,
            $item->unit_type,
            $item->minimum_stock,
            $item->cost_price,
            $item->current_stock * $item->cost_price,
        ];
    }
}
