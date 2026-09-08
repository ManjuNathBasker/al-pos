<?php

namespace App\Http\Controllers;

use App\Models\DeliveryPartner;
use App\Models\Account;
use Illuminate\Http\Request;

class DeliveryPartnerController extends Controller
{
    public function index()
    {
        $partners = DeliveryPartner::where('company_id', session('company_id'))->get();
        return view('delivery_partners.index', compact('partners'));
    }

    public function create()
    {
        return view('delivery_partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $companyId = session('company_id');

        // Create a dedicated receivables account for this partner
        $account = Account::create([
            'company_id' => $companyId,
            'account_name' => $request->name . ' Receivables',
            'account_code' => '1150-' . rand(100, 999), // Example code format
            'account_type' => 'Asset',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_system' => true,
            'show_in_pos' => false,
            'status' => true,
        ]);

        DeliveryPartner::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'commission_percentage' => $request->commission_percentage,
            'receivable_account_id' => $account->id,
            'status' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('delivery-partners.index')->with('success', 'Delivery Partner created successfully.');
    }

    public function edit(DeliveryPartner $deliveryPartner)
    {
        return view('delivery_partners.edit', compact('deliveryPartner'));
    }

    public function update(Request $request, DeliveryPartner $deliveryPartner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ]);

        $deliveryPartner->update([
            'name' => $request->name,
            'commission_percentage' => $request->commission_percentage,
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('delivery-partners.index')->with('success', 'Delivery Partner updated successfully.');
    }

    public function getActive()
    {
        $partners = DeliveryPartner::where('company_id', session('company_id'))
            ->where('status', true)
            ->get();
        
        return response()->json(['success' => true, 'data' => $partners]);
    }

    public function settlements(DeliveryPartner $deliveryPartner)
    {
        $orders = \App\Models\Order::where('company_id', session('company_id'))
            ->where('delivery_partner_id', $deliveryPartner->id)
            ->where('service_type', 'delivery')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('delivery_partners.settlements', compact('deliveryPartner', 'orders'));
    }

    public function markSettled(Request $request, \App\Models\Order $order)
    {
        if ($order->company_id !== session('company_id')) {
            abort(403);
        }
        
        $order->update([
            'settlement_status' => 'settled',
        ]);
        
        return back()->with('success', 'Order marked as settled with delivery partner.');
    }
}
