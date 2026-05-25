<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request)
    {
        $query = Purchase::with('supplier', 'creator')->orderBy('purchase_date', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('purchase_number', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::all();
        
        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->get();
        $inventoryItems = InventoryItem::where('status', true)->get();
        return view('purchases.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'purchase_date'  => 'required|date',
            'items'          => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_cost'  => 'required|numeric|min:0',
            'items.*.total_cost' => 'required|numeric|min:0',
            'subtotal'       => 'required|numeric',
            'discount'       => 'nullable|numeric',
            'tax'            => 'nullable|numeric',
            'total_amount'   => 'required|numeric',
            'notes'          => 'nullable|string',
            'status'         => 'required|in:draft,approved,received,completed',
            'paid_amount'    => 'nullable|numeric|min:0',
            'payment_method' => 'required_with:paid_amount',
        ]);

        $purchase = $this->purchaseService->createPurchase($validated);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.inventoryItem', 'payments.creator', 'creator']);
        return view('purchases.show', compact('purchase'));
    }

    public function updateStatus(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,approved,received,completed,cancelled',
        ]);

        $this->purchaseService->updateStatus($purchase, $validated['status']);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        if (in_array($purchase->status, ['received', 'completed'])) {
            return redirect()->back()->with('error', 'Cannot delete a received/completed purchase. Cancel it first to reverse stock.');
        }

        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }
}
