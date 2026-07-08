<?php

namespace App\Http\Controllers;

use App\Models\BankOffer;
use App\Models\Card;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Branch;
use App\Services\BankOfferService;
use Illuminate\Http\Request;

class BankOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = BankOffer::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $offers = $query->paginate(15)->withQueryString();

        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $cards = Card::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $customers = Customer::all();
        $branches = Branch::where('is_active', true)->get();

        return view('offers.create', compact('cards', 'products', 'categories', 'customers', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'min_purchase'          => 'required|numeric|min:0',
            'max_discount'          => 'required|numeric|min:0',
            'discount_type'         => 'required|in:percent,flat',
            'discount_value'        => 'required|numeric|min:0',
            'cashback'              => 'required|numeric|min:0',
            'is_emi_offer'          => 'boolean',
            'usage_limit'           => 'required|integer|min:0',
            'merchant_contribution' => 'required|numeric|min:0|max:100',
            'bank_contribution'     => 'required|numeric|min:0|max:100',
            'is_active'             => 'boolean',
            'cards'                 => 'nullable|array',
            'cards.*'               => 'exists:cards,id',
            'products'              => 'nullable|array',
            'products.*'            => 'exists:products,id',
            'categories'            => 'nullable|array',
            'categories.*'          => 'exists:categories,id',
            'customers'             => 'nullable|array',
            'customers.*'           => 'exists:customers,id',
            'branches'              => 'nullable|array',
            'branches.*'            => 'exists:branches,id',
        ]);

        $validated['is_emi_offer'] = $request->has('is_emi_offer');
        $validated['is_active'] = $request->has('is_active');
        $validated['company_id'] = session('company_id');

        $offer = BankOffer::create($validated);

        // Sync relationships
        if ($request->has('cards')) $offer->cards()->sync($request->cards);
        if ($request->has('products')) $offer->products()->sync($request->products);
        if ($request->has('categories')) $offer->categories()->sync($request->categories);
        if ($request->has('customers')) $offer->customers()->sync($request->customers);
        if ($request->has('branches')) $offer->branches()->sync($request->branches);

        return redirect()->route('offers.index')->with('success', 'Bank Offer created successfully.');
    }

    public function edit(BankOffer $offer)
    {
        $cards = Card::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $customers = Customer::all();
        $branches = Branch::where('is_active', true)->get();

        return view('offers.edit', compact('offer', 'cards', 'products', 'categories', 'customers', 'branches'));
    }

    public function update(Request $request, BankOffer $offer)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'min_purchase'          => 'required|numeric|min:0',
            'max_discount'          => 'required|numeric|min:0',
            'discount_type'         => 'required|in:percent,flat',
            'discount_value'        => 'required|numeric|min:0',
            'cashback'              => 'required|numeric|min:0',
            'is_emi_offer'          => 'boolean',
            'usage_limit'           => 'required|integer|min:0',
            'merchant_contribution' => 'required|numeric|min:0|max:100',
            'bank_contribution'     => 'required|numeric|min:0|max:100',
            'is_active'             => 'boolean',
            'cards'                 => 'nullable|array',
            'cards.*'               => 'exists:cards,id',
            'products'              => 'nullable|array',
            'products.*'            => 'exists:products,id',
            'categories'            => 'nullable|array',
            'categories.*'          => 'exists:categories,id',
            'customers'             => 'nullable|array',
            'customers.*'           => 'exists:customers,id',
            'branches'              => 'nullable|array',
            'branches.*'            => 'exists:branches,id',
        ]);

        $validated['is_emi_offer'] = $request->has('is_emi_offer');
        $validated['is_active'] = $request->has('is_active');

        $offer->update($validated);

        // Sync relationships
        $offer->cards()->sync($request->cards ?? []);
        $offer->products()->sync($request->products ?? []);
        $offer->categories()->sync($request->categories ?? []);
        $offer->customers()->sync($request->customers ?? []);
        $offer->branches()->sync($request->branches ?? []);

        return redirect()->route('offers.index')->with('success', 'Bank Offer updated successfully.');
    }

    public function destroy(BankOffer $offer)
    {
        $offer->delete();
        return redirect()->route('offers.index')->with('success', 'Bank Offer deleted successfully.');
    }

    // API endpoint to resolve eligible offers for POS
    public function resolveOffers(Request $request, BankOfferService $offerService)
    {
        $request->validate([
            'card_id'     => 'required|exists:cards,id',
            'subtotal'    => 'required|numeric|min:0',
            'cart'        => 'nullable|array',
            'customer_id' => 'nullable|integer',
            'branch_id'   => 'nullable|integer',
        ]);

        $offers = $offerService->getEligibleOffers(
            $request->card_id,
            $request->subtotal,
            $request->cart ?? [],
            $request->customer_id,
            $request->branch_id
        );

        return response()->json([
            'success' => true,
            'offers'  => $offers,
        ]);
    }
}
