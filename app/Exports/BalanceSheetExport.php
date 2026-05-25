<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BalanceSheetExport implements FromArray, WithHeadings
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        
        $data[] = ['ASSETS', ''];
        foreach ($this->report['assets'] as $asset) {
            $data[] = ['  ' . $asset['name'], $asset['amount']];
        }
        $data[] = ['Total Assets', $this->report['total_assets']];
        $data[] = ['', ''];
        
        $data[] = ['LIABILITIES', ''];
        foreach ($this->report['liabilities'] as $liability) {
            $data[] = ['  ' . $liability['name'], $liability['amount']];
        }
        $data[] = ['Total Liabilities', $this->report['total_liabilities']];
        $data[] = ['', ''];
        
        $data[] = ['EQUITY', ''];
        foreach ($this->report['equity'] as $equity) {
            $data[] = ['  ' . $equity['name'], $equity['amount']];
        }
        $data[] = ['Total Equity', $this->report['total_equity']];
        $data[] = ['', ''];
        
        $data[] = ['TOTAL LIABILITIES & EQUITY', $this->report['total_liabilities'] + $this->report['total_equity']];

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
