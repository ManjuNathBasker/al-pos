<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Deduct stock based on an order's items and their recipes.
     */
    public function deductStockFromOrder(Order $order)
    {
        if ($order->is_stock_deducted) {
            return; // Already deducted
        }

        DB::beginTransaction();
        try {
            foreach ($order->items as $orderItem) {
                $product = $orderItem->product;
                if (!$product) continue;

                foreach ($product->recipeItems as $recipe) {
                    $totalDeduction = $recipe->quantity * $orderItem->quantity;
                    
                    $inventoryItem = $recipe->inventoryItem;
                    $inventoryItem->decrement('current_stock', $totalDeduction);

                    $this->logTransaction(
                        $inventoryItem->id,
                        'deduction',
                        $totalDeduction,
                        'Order',
                        $order->id,
                        "Stock deducted for product: {$product->name}"
                    );
                }
            }

            $order->update(['is_stock_deducted' => true]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deduct stock for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore stock if an order is cancelled or refunded.
     */
    public function restoreStockFromOrder(Order $order)
    {
        if (!$order->is_stock_deducted) {
            return; // Nothing to restore
        }

        DB::beginTransaction();
        try {
            foreach ($order->items as $orderItem) {
                $product = $orderItem->product;
                if (!$product) continue;

                foreach ($product->recipeItems as $recipe) {
                    $totalRestoration = $recipe->quantity * $orderItem->quantity;
                    
                    $inventoryItem = $recipe->inventoryItem;
                    $inventoryItem->increment('current_stock', $totalRestoration);

                    $this->logTransaction(
                        $inventoryItem->id,
                        'restoration',
                        $totalRestoration,
                        'Order',
                        $order->id,
                        "Stock restored for cancelled/refunded order."
                    );
                }
            }

            $order->update(['is_stock_deducted' => false]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to restore stock for order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore stock for a single cancelled order item.
     * Prevents double-restoration by verifying $order->is_stock_deducted.
     */
    public function restoreStockFromOrderItem(\App\Models\OrderItem $orderItem)
    {
        $order = $orderItem->order;
        if (!$order || !$order->is_stock_deducted) {
            return; // Stock was not deducted for this order
        }

        $product = $orderItem->product;
        if (!$product) {
            return;
        }

        foreach ($product->recipeItems as $recipe) {
            $totalRestoration = $recipe->quantity * $orderItem->quantity;
            $inventoryItem = $recipe->inventoryItem;
            if ($inventoryItem) {
                $inventoryItem->increment('current_stock', $totalRestoration);

                $this->logTransaction(
                    $inventoryItem->id,
                    'restoration',
                    $totalRestoration,
                    'Order',
                    $order->id,
                    "Stock restored for cancelled item: {$product->name}"
                );
            }
        }
    }

    /**
     * Validate if enough stock is available for the given cart items.
     */
    public function validateStockAvailability(array $cart)
    {
        $requirements = [];

        foreach ($cart as $item) {
            $product = Product::with('recipeItems.inventoryItem')->find($item['id']);
            if (!$product) continue;

            foreach ($product->recipeItems as $recipe) {
                $itemId = $recipe->inventory_item_id;
                $needed = $recipe->quantity * $item['qty'];

                if (!isset($requirements[$itemId])) {
                    $requirements[$itemId] = [
                        'name' => $recipe->inventoryItem->name,
                        'current' => $recipe->inventoryItem->current_stock,
                        'needed' => 0
                    ];
                }
                $requirements[$itemId]['needed'] += $needed;
            }
        }

        foreach ($requirements as $itemId => $data) {
            if ($data['needed'] > $data['current']) {
                return [
                    'success' => false,
                    'message' => "Insufficient stock for: " . $data['name'] . " (Required: " . $data['needed'] . ", Available: " . $data['current'] . ")"
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Log an inventory transaction.
     */
    public function logTransaction($itemId, $type, $qty, $refType = null, $refId = null, $notes = null)
    {
        InventoryTransaction::create([
            'company_id'        => session('company_id'),
            'inventory_item_id' => $itemId,
            'transaction_type'  => $type,
            'quantity'          => $qty,
            'reference_type'    => $refType,
            'reference_id'      => $refId,
            'notes'             => $notes,
            'created_by'        => auth()->id() ?? 1, // Fallback to system user if needed
        ]);
    }

    /**
     * Add stock from a purchase order.
     */
    public function addStockFromPurchase(\App\Models\Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            foreach ($purchase->items as $item) {
                $inventoryItem = $item->inventoryItem;
                if (!$inventoryItem) continue;

                $inventoryItem->increment('current_stock', $item->quantity);

                $this->logTransaction(
                    $inventoryItem->id,
                    'purchase',
                    $item->quantity,
                    'Purchase',
                    $purchase->id,
                    "Stock added from Purchase #{$purchase->purchase_number}"
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add stock from purchase #{$purchase->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse stock from a cancelled purchase order.
     */
    public function reverseStockFromPurchase(\App\Models\Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            foreach ($purchase->items as $item) {
                $inventoryItem = $item->inventoryItem;
                if (!$inventoryItem) continue;

                $inventoryItem->decrement('current_stock', $item->quantity);

                $this->logTransaction(
                    $inventoryItem->id,
                    'restoration', // Reversing the purchase
                    $item->quantity,
                    'Purchase',
                    $purchase->id,
                    "Stock reversed for cancelled Purchase #{$purchase->purchase_number}"
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reverse stock for purchase #{$purchase->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
