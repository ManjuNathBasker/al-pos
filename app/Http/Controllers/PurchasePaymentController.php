<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchasePaymentController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function store(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'paid_amount'      => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $this->purchaseService->addPayment($purchase, $validated);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(PurchasePayment $payment)
    {
        $purchase = $payment->purchase;
        $payment->delete();
        
        // Recalculate status
        $this->purchaseService->updatePaymentStatus($purchase);

        return redirect()->back()->with('success', 'Payment deleted successfully.');
    }
}
