<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function createPurchase(array $data)
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'supplier_id'     => $data['supplier_id'],
                'purchase_number' => $this->generatePurchaseNumber(),
                'purchase_date'   => $data['purchase_date'],
                'subtotal'        => $data['subtotal'] ?? 0,
                'discount'        => $data['discount'] ?? 0,
                'tax'             => $data['tax'] ?? 0,
                'total_amount'    => $data['total_amount'],
                'status'          => $data['status'] ?? 'draft',
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id'       => $purchase->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity'          => $item['quantity'],
                    'unit_cost'         => $item['unit_cost'],
                    'total_cost'        => $item['total_cost'],
                ]);
            }

            if (isset($data['paid_amount']) && $data['paid_amount'] > 0) {
                $this->addPayment($purchase, [
                    'paid_amount'    => $data['paid_amount'],
                    'payment_method' => $data['payment_method'],
                    'payment_date'   => $data['purchase_date'],
                ]);
            }

            if ($purchase->status === 'received' || $purchase->status === 'completed') {
                $this->inventoryService->addStockFromPurchase($purchase);
            }

            DB::commit();
            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create purchase: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStatus(Purchase $purchase, $newStatus)
    {
        if ($purchase->status === $newStatus) return $purchase;

        DB::beginTransaction();
        try {
            $oldStatus = $purchase->status;
            $purchase->update(['status' => $newStatus]);

            // Handle stock updates
            if (in_array($newStatus, ['received', 'completed']) && !in_array($oldStatus, ['received', 'completed'])) {
                $this->inventoryService->addStockFromPurchase($purchase);
            } elseif ($newStatus === 'cancelled' && in_array($oldStatus, ['received', 'completed'])) {
                $this->inventoryService->reverseStockFromPurchase($purchase);
            }

            DB::commit();
            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update purchase status: " . $e->getMessage());
            throw $e;
        }
    }

    public function addPayment(Purchase $purchase, array $data)
    {
        DB::beginTransaction();
        try {
            $payment = PurchasePayment::create([
                'purchase_id'      => $purchase->id,
                'paid_amount'      => $data['paid_amount'],
                'payment_method'   => $data['payment_method'],
                'payment_date'     => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]);

            $this->updatePaymentStatus($purchase);

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to add purchase payment: " . $e->getMessage());
            throw $e;
        }
    }

    public function updatePaymentStatus(Purchase $purchase)
    {
        $paid = $purchase->payments()->sum('paid_amount');
        
        if ($paid >= $purchase->total_amount) {
            $purchase->update(['payment_status' => 'paid']);
        } elseif ($paid > 0) {
            $purchase->update(['payment_status' => 'partial']);
        } else {
            $purchase->update(['payment_status' => 'unpaid']);
        }
    }

    protected function generatePurchaseNumber()
    {
        $prefix = "PO-";
        $lastPurchase = Purchase::where('company_id', session('company_id'))
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPurchase ? (int) str_replace($prefix, '', $lastPurchase->purchase_number) + 1 : 1001;
        
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
