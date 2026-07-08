<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Card;
use App\Models\Branch;
use App\Models\BankOffer;
use App\Models\Customer;
use App\Models\Account;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\CardTransaction;
use App\Models\BankSettlement;
use App\Services\AccountingService;
use App\Services\BankOfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $branch;
    protected $settlementAccount;
    protected $card;
    protected $offer;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Company (Multi-tenant root)
        $this->company = Company::create([
            'name' => 'Test POS Inc',
            'email' => 'test@pos.com',
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        // Put company in session for tenant scoping
        session(['company_id' => $this->company->id]);
        
        // 2. Create User
        $this->user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@pos.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->companies()->attach($this->company->id);

        // Run seeders to set up Spatie permissions and roles
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->user->assignRole('Admin');

        // 3. Create a Branch
        $this->branch = Branch::create([
            'company_id' => $this->company->id,
            'name' => 'Downtown Branch',
            'code' => 'DT01',
            'address' => '123 Main St',
            'is_active' => true,
        ]);

        // 4. Create Category
        $this->category = Category::create([
            'company_id' => $this->company->id,
            'name' => 'General Products',
        ]);

        // 5. Create Card Settlement Account (Chart of Accounts asset)
        $this->settlementAccount = Account::create([
            'company_id' => $this->company->id,
            'account_name' => 'Card Clearing Account',
            'account_code' => '1025',
            'account_type' => 'Asset',
            'status' => true,
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'is_system' => false,
            'show_in_pos' => true,
        ]);

        // 6. Create a Card Master record
        $this->card = Card::create([
            'company_id' => $this->company->id,
            'bank_name' => 'Apex Bank',
            'card_network' => 'Visa',
            'card_type' => 'Credit',
            'settlement_account_id' => $this->settlementAccount->id,
            'service_charge' => 2.00, // 2% service charge
            'mdr' => 1.50, // 1.5% MDR
            'processing_fee' => 0.50, // $0.50 processing fee
            'settlement_days' => 2,
            'is_active' => true,
        ]);

        // 7. Create a Bank Offer associated with the Card
        $this->offer = BankOffer::create([
            'company_id' => $this->company->id,
            'name' => 'Apex Visa 10% Discount',
            'discount_type' => 'percent',
            'discount_value' => 10.00, // 10% discount
            'min_purchase' => 50.00,
            'max_discount' => 20.00,
            'merchant_contribution' => 60.00, // Merchant pays 60%
            'bank_contribution' => 40.00, // Bank pays 40%
            'cashback' => 2.00, // $2 cashback
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'usage_limit' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);
        
        // Link offer to card via pivot table
        $this->offer->cards()->attach($this->card->id);

        // Link offer to branch
        $this->offer->branches()->attach($this->branch->id);

        // 8. Create a Customer
        $this->customer = Customer::create([
            'company_id' => $this->company->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'wallet_balance' => 100.00,
        ]);
    }

    /** @test */
    public function test_can_create_and_manage_cards()
    {
        $this->actingAs($this->user);

        // Create card
        $response = $this->post(route('cards.store'), [
            'bank_name' => 'Global Bank',
            'card_network' => 'Mastercard',
            'card_type' => 'Debit',
            'settlement_account_id' => $this->settlementAccount->id,
            'service_charge' => 1.00,
            'mdr' => 1.00,
            'processing_fee' => 0.20,
            'settlement_days' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cards', [
            'bank_name' => 'Global Bank',
            'card_network' => 'Mastercard',
        ]);
    }

    /** @test */
    public function test_can_resolve_bank_offers_pos_endpoint()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/pos/resolve-offers', [
            'card_id' => $this->card->id,
            'subtotal' => 100.00,
            'cart' => [],
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        // 10% discount on $100 = $10
        $this->assertEquals(10.00, $response->json('offers.0.discount'));
        $this->assertEquals(2.00, $response->json('offers.0.cashback'));
    }

    /** @test */
    public function test_pos_checkout_creates_card_transaction_with_financials()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);

        // Open Register Session
        \App\Models\RegisterSession::create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'opened_at' => now(),
            'opening_amount' => 100.00,
            'status' => 'open',
        ]);

        // Create dummy product with category_id
        $product = Product::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'name' => 'Test Item',
            'price' => 100.00,
            'sku' => 'TEST-01',
            'stock' => 10,
        ]);

        $response = $this->postJson(route('pos.checkout'), [
            'service_type' => 'retail',
            'subtotal' => 100.00,
            'discount_percent' => 0,
            'discount_type' => 'percent',
            'tax_amount' => 0.00,
            'total' => 91.80, // 100 - 10 (discount) + 1.80 (service charge)
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => 100.00,
                    'qty' => 1,
                ]
            ],
            'customer_name' => 'John Doe',
            'customer_phone' => '1234567890',
            'payment_details' => [
                $this->settlementAccount->id => 100.00 // swiped amount base
            ],
            'card_details' => [
                $this->settlementAccount->id => [
                    'card_id' => $this->card->id,
                    'offer_id' => $this->offer->id,
                ]
            ],
            'use_wallet' => false,
            'branch_id' => $this->branch->id,
            'is_split' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Assert CardTransaction was recorded
        $this->assertDatabaseHas('card_transactions', [
            'card_id' => $this->card->id,
            'gross_amount' => 100.00,
            'discount_amount' => 10.00,
            'service_charge_amount' => 1.80, // (100 - 10) * 2% = $1.80
        ]);
        
        $tx = CardTransaction::first();
        $this->assertEquals(91.80, $tx->gross_amount - $tx->discount_amount + $tx->service_charge_amount); // swipe amount
        $this->assertEquals(4.00, $tx->bank_discount_share);
    }

    /** @test */
    public function test_bank_settlement_reconciliation_calculates_discrepancies()
    {
        $this->actingAs($this->user);

        // Create an Order
        $order = Order::create([
            'company_id' => $this->company->id,
            'order_number' => 'ORD-100',
            'subtotal' => 100.00,
            'discount_amount' => 10.00,
            'tax_amount' => 0.00,
            'total_amount' => 92.00,
            'status' => 'paid',
        ]);

        // Create transaction
        $tx = CardTransaction::create([
            'company_id' => $this->company->id,
            'card_id' => $this->card->id,
            'order_id' => $order->id,
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'bank_name' => 'Apex Bank',
            'card_network' => 'Visa',
            'card_type' => 'Credit',
            'gross_amount' => 100.00,
            'discount_amount' => 10.00,
            'cashback_amount' => 2.00,
            'service_charge_amount' => 2.00,
            'processing_fee_amount' => 0.50,
            'merchant_discount_share' => 6.00,
            'bank_discount_share' => 4.00,
            'net_settlement_amount' => 93.62,
            'settlement_days' => 2,
            'settlement_status' => 'pending',
        ]);

        // Submit Settlement Run
        $response = $this->post(route('settlements.store'), [
            'card_transaction_id' => $tx->id,
            'settlement_date' => now()->toDateString(),
            'actual_settlement_amount' => 90.00, // $3.62 lower than expected (settlement difference)
            'bank_charges' => 0.50, // actual charges
            'processing_charges' => 0.00,
            'bank_statement_reference' => 'SETT-REF-100',
            'notes' => 'Test reconciliation run',
        ]);

        $response->assertRedirect();
        
        // Verify settlement run record
        $this->assertDatabaseHas('bank_settlements', [
            'card_transaction_id' => $tx->id,
            'expected_settlement_amount' => 93.62,
            'actual_settlement_amount' => 90.00,
            'settlement_difference' => -3.12, // 90.00 (actual) + 0.50 (charges) - 93.62 (expected) = -3.12
            'bank_charges' => 0.50,
        ]);

        // Verify transaction is now completed (settled)
        $this->assertEquals('completed', $tx->fresh()->settlement_status);
    }
}
