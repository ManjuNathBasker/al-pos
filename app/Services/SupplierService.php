<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function getAllSuppliers()
    {
        return Supplier::orderBy('name')->get();
    }

    public function createSupplier(array $data)
    {
        return Supplier::create($data);
    }

    public function updateSupplier(Supplier $supplier, array $data)
    {
        $supplier->update($data);
        return $supplier;
    }

    public function deleteSupplier(Supplier $supplier)
    {
        return $supplier->delete();
    }

    public function getSupplierHistory(Supplier $supplier)
    {
        return $supplier->purchases()->with('payments')->orderBy('purchase_date', 'desc')->get();
    }
}
