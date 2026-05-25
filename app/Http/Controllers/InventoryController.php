<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('name')->get();
        
        $stats = [
            'total_items' => $items->count(),
            'low_stock'   => $items->filter(fn($i) => $i->is_low_stock)->count(),
            'out_of_stock' => $items->filter(fn($i) => $i->current_stock <= 0)->count(),
            'total_value' => $items->sum(fn($i) => $i->current_stock * $i->cost_price),
        ];

        $recentTransactions = InventoryTransaction::with('inventoryItem')
            ->latest()
            ->limit(10)
            ->get();

        return view('inventory.index', compact('items', 'stats', 'recentTransactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50',
            'unit_type'     => 'required|in:kg,gram,liter,ml,piece',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::create($validated);

        // Log initial stock as purchase/adjustment
        InventoryTransaction::create([
            'company_id'        => $item->company_id,
            'inventory_item_id' => $item->id,
            'transaction_type'  => 'adjustment',
            'quantity'          => $item->current_stock,
            'notes'             => 'Initial stock entry',
            'created_by'        => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Inventory item created successfully');
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
        ]);

        $oldStock = $inventoryItem->current_stock;
        $inventoryItem->update($validated);

        if ($oldStock != $validated['current_stock']) {
            InventoryTransaction::create([
                'company_id'        => $inventoryItem->company_id,
                'inventory_item_id' => $inventoryItem->id,
                'transaction_type'  => 'adjustment',
                'quantity'          => $validated['current_stock'] - $oldStock,
                'notes'             => 'Manual stock adjustment',
                'created_by'        => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', 'Inventory item updated successfully');
    }

    public function recipes()
    {
        $products = Product::with('recipeItems.inventoryItem')->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('status', true)->orderBy('name')->get();

        return view('inventory.recipes', compact('products', 'inventoryItems'));
    }

    public function updateRecipe(Request $request, Product $product)
    {
        $request->validate([
            'ingredients' => 'nullable|array',
            'ingredients.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'ingredients.*.quantity'          => 'required|numeric|min:0.001',
        ]);

        DB::beginTransaction();
        try {
            // Remove old recipe items
            RecipeItem::where('product_id', $product->id)->delete();

            // Add new recipe items
            if ($request->ingredients) {
                foreach ($request->ingredients as $ing) {
                    RecipeItem::create([
                        'company_id'        => session('company_id'),
                        'product_id'        => $product->id,
                        'inventory_item_id' => $ing['inventory_item_id'],
                        'quantity'          => $ing['quantity'],
                    ]);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Recipe updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update recipe: ' . $e->getMessage());
        }
    }
}
