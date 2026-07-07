<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitLossExport implements FromArray, WithHeadings
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        
        $data[] = ['INCOME', ''];
        foreach ($this->report['income'] as $income) {
            $data[] = ['  ' . $income['name'], $income['amount']];
        }
        $data[] = ['Total Income', $this->report['total_income']];
        $data[] = ['', ''];
        
        $data[] = ['EXPENSES', ''];
        foreach ($this->report['expenses'] as $expense) {
            $data[] = ['  ' . $expense['name'], $expense['amount']];
        }
        $data[] = ['Total Expenses', $this->report['total_expense']];
        $data[] = ['', ''];
        
        $data[] = ['NET PROFIT', $this->report['net_profit']];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Account / Category',
            'Amount',
        ];
    }
}
