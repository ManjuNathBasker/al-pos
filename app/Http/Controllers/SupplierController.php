<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request)
    {
        $query = Supplier::orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
        ]);

        $this->supplierService->createSupplier($validated);

        return redirect()->back()->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string|max:50',
            'opening_balance' => 'nullable|numeric',
            'status'          => 'required|boolean',
        ]);

        $this->supplierService->updateSupplier($supplier, $validated);

        return redirect()->back()->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete supplier with existing purchases.');
        }

        $this->supplierService->deleteSupplier($supplier);
        return redirect()->back()->with('success', 'Supplier deleted successfully.');
    }

    public function show(Supplier $supplier)
    {
        $history = $this->supplierService->getSupplierHistory($supplier);
        return view('suppliers.show', compact('supplier', 'history'));
    }
}
