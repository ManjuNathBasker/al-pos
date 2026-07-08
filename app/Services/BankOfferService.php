<?php

namespace App\Services;

use App\Models\Card;
use App\Models\BankOffer;
use App\Models\Product;
use App\Models\Category;

class BankOfferService
{
    /**
     * Resolve all eligible offers for a given card, cart subtotal, cart items, customer, and branch.
     */
    public function getEligibleOffers(
        int $cardId,
        float $subtotal,
        array $cart = [],
        ?int $customerId = null,
        ?int $branchId = null
    ): array {
        $card = Card::find($cardId);
        if (!$card || !$card->is_active) {
            return [];
        }

        // Get all active offers associated with this card
        $offers = $card->bankOffers()
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->get();

        $eligibleOffers = [];

        foreach ($offers as $offer) {
            // 1. Min Purchase Check
            if ($subtotal < $offer->min_purchase) {
                continue;
            }

            // 2. Usage Limit Check
            if ($offer->usage_limit > 0 && $offer->used_count >= $offer->usage_limit) {
                continue;
            }

            // 3. Customer Check
            if ($offer->customers()->exists()) {
                if (!$customerId || !$offer->customers()->where('customers.id', $customerId)->exists()) {
                    continue;
                }
            }

            // 4. Branch Check
            if ($offer->branches()->exists()) {
                if (!$branchId || !$offer->branches()->where('branches.id', $branchId)->exists()) {
                    continue;
                }
            }

            // 5. Products / Categories Check
            if ($offer->products()->exists() || $offer->categories()->exists()) {
                $hasMatchingProduct = false;

                foreach ($cart as $item) {
                    $productId = $item['id'] ?? null;
                    if (!$productId) continue;

                    // Direct product match
                    if ($offer->products()->where('products.id', $productId)->exists()) {
                        $hasMatchingProduct = true;
                        break;
                    }

                    // Category match
                    $product = Product::find($productId);
                    if ($product && $product->category_id) {
                        if ($offer->categories()->where('categories.id', $product->category_id)->exists()) {
                            $hasMatchingProduct = true;
                            break;
                        }
                    }
                }

                if (!$hasMatchingProduct) {
                    continue;
                }
            }

            // Calculate Discount Amount
            $discount = 0.0;
            if ($offer->discount_type === 'percent') {
                $discount = $subtotal * ($offer->discount_value / 100);
            } else {
                $discount = $offer->discount_value;
            }

            // Apply Max Discount limit
            if ($offer->max_discount > 0 && $discount > $offer->max_discount) {
                $discount = $offer->max_discount;
            }

            // Capped at subtotal
            $discount = min($discount, $subtotal);

            $merchantShare = $discount * ($offer->merchant_contribution / 100);
            $bankShare = $discount * ($offer->bank_contribution / 100);

            $eligibleOffers[] = [
                'offer' => $offer,
                'discount' => round($discount, 2),
                'cashback' => round($offer->cashback, 2),
                'merchant_share' => round($merchantShare, 2),
                'bank_share' => round($bankShare, 2),
            ];
        }

        // Sort by highest discount savings first
        usort($eligibleOffers, function ($a, $b) {
            return $b['discount'] <=> $a['discount'];
        });

        return $eligibleOffers;
    }

    /**
     * Compute full transaction financial breakdown.
     */
    public function calculateFinancials(Card $card, float $subtotal, ?array $selectedOfferData = null): array
    {
        $discountAmount = 0.00;
        $cashbackAmount = 0.00;
        $offerId = null;
        $merchantContributionPercent = 100.00;
        $bankContributionPercent = 0.00;

        if ($selectedOfferData) {
            $offerId = $selectedOfferData['offer_id'] ?? null;
            $offer = BankOffer::find($offerId);
            if ($offer) {
                $discountAmount = (float) ($selectedOfferData['discount'] ?? 0.00);
                $cashbackAmount = (float) $offer->cashback;
                $merchantContributionPercent = (float) $offer->merchant_contribution;
                $bankContributionPercent = (float) $offer->bank_contribution;
            }
        }

        $grossAmount = $subtotal;
        $taxableBase = max(0.00, $grossAmount - $discountAmount);

        // Service charge is applied to the customer's swiped amount (adds to the bill)
        $serviceChargeAmount = round($taxableBase * ($card->service_charge / 100), 2);

        // MDR is deducted from the merchant payout
        $mdrAmount = round($taxableBase * ($card->mdr / 100), 2);

        // Flat processing fee is deducted from the merchant payout
        $processingFeeAmount = (float) $card->processing_fee;

        // Calculate discount shares
        $merchantDiscountShare = round($discountAmount * ($merchantContributionPercent / 100), 2);
        $bankDiscountShare = round($discountAmount * ($bankContributionPercent / 100), 2);

        // Expected Net Payout = swiped gross + bank discount share - MDR - processing fee
        // Note: Service charge is received from the customer, so it increases the gross amount swiped.
        // Let's check: Does the customer swipe gross including or excluding service charge?
        // Customer swiped amount = Gross + Service Charge.
        // Payout to merchant = Customer swiped amount + Bank Discount Share - MDR - Processing Fee.
        $totalCustomerSwiped = $grossAmount - $discountAmount + $serviceChargeAmount;
        $netSettlementAmount = $totalCustomerSwiped + $bankDiscountShare - $mdrAmount - $processingFeeAmount;

        return [
            'gross_amount' => $grossAmount,
            'discount_amount' => $discountAmount,
            'cashback_amount' => $cashbackAmount,
            'service_charge_amount' => $serviceChargeAmount,
            'mdr_amount' => $mdrAmount,
            'processing_fee_amount' => $processingFeeAmount,
            'total_customer_swiped' => $totalCustomerSwiped,
            'net_settlement_amount' => round($netSettlementAmount, 2),
            'offer_id' => $offerId,
            'bank_discount_share' => $bankDiscountShare,
            'merchant_discount_share' => $merchantDiscountShare,
        ];
    }
}
