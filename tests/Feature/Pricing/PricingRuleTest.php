<?php

namespace Tests\Feature\Pricing;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PricingRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'pricing-rules-access',
            'pricing-rules-create',
            'pricing-rules-update',
            'pricing-rules-delete',
            'transactions-access',
            'cashier-shifts-access',
            'cashier-shifts-open',
            'cashier-shifts-close',
        ] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_authorized_user_can_create_pricing_rule(): void
    {
        $user = $this->createUserWithPermissions([
            'pricing-rules-access',
            'pricing-rules-create',
        ]);
        $category = Category::create([
            'name' => 'Minuman',
            'description' => 'Kategori uji',
            'image' => 'category.png',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('pricing-rules.store'), [
                'name' => 'Promo Minuman Pagi',
                'is_active' => true,
                'priority' => 120,
                'target_type' => 'category',
                'category_id' => $category->id,
                'customer_scope' => 'all',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addHour()->format('Y-m-d\TH:i'),
                'notes' => 'Promo aktif pagi ini',
            ]);

        $response->assertRedirect(route('pricing-rules.index'));
        $this->assertDatabaseHas('pricing_rules', [
            'name' => 'Promo Minuman Pagi',
            'target_type' => 'category',
            'category_id' => $category->id,
            'discount_type' => 'percentage',
            'customer_scope' => 'all',
            'created_by' => $user->id,
        ]);
    }

    public function test_pricing_preview_respects_customer_scope(): void
    {
        $cashier = $this->createUserWithPermissions([
            'transactions-access',
            'cashier-shifts-access',
            'cashier-shifts-open',
            'cashier-shifts-close',
        ]);
        $this->openShiftFor($cashier);
        $product = $this->createProduct();
        $customer = Customer::create([
            'name' => 'Registered Customer',
            'no_telp' => '62812345678',
            'address' => 'Jl. Uji Pelanggan',
        ]);

        Cart::create([
            'cashier_id' => $cashier->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->sell_price,
        ]);

        PricingRule::create([
            'name' => 'Harga Member',
            'is_active' => true,
            'priority' => 200,
            'target_type' => 'product',
            'product_id' => $product->id,
            'customer_scope' => 'registered',
            'discount_type' => 'fixed_amount',
            'discount_value' => 10000,
        ]);

        $walkInResponse = $this
            ->actingAs($cashier)
            ->postJson(route('transactions.pricing-preview'), []);

        $registeredResponse = $this
            ->actingAs($cashier)
            ->postJson(route('transactions.pricing-preview'), [
                'customer_id' => $customer->id,
            ]);

        $walkInResponse->assertOk();
        $registeredResponse->assertOk();
        $this->assertSame(
            0,
            data_get($walkInResponse->json(), 'data.summary.promo_discount_total')
        );
        $this->assertSame(
            10000,
            data_get($registeredResponse->json(), 'data.summary.promo_discount_total')
        );
    }

    public function test_transaction_checkout_recalculates_grand_total_using_pricing_rules(): void
    {
        $cashier = $this->createUserWithPermissions([
            'transactions-access',
            'cashier-shifts-access',
            'cashier-shifts-open',
            'cashier-shifts-close',
        ]);
        $shift = $this->openShiftFor($cashier);
        $product = $this->createProduct();
        $customer = Customer::create([
            'name' => 'Customer Promo',
            'no_telp' => '628777888999',
            'address' => 'Jl. Promo No. 1',
        ]);

        Cart::create([
            'cashier_id' => $cashier->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => $product->sell_price * 2,
        ]);

        PricingRule::create([
            'name' => 'Harga Spesial Produk',
            'is_active' => true,
            'priority' => 300,
            'target_type' => 'product',
            'product_id' => $product->id,
            'customer_scope' => 'all',
            'discount_type' => 'fixed_price',
            'discount_value' => 50000,
        ]);

        $response = $this
            ->actingAs($cashier)
            ->post(route('transactions.store'), [
                'customer_id' => $customer->id,
                'discount' => 5000,
                'shipping_cost' => 0,
                'grand_total' => 999999,
                'cash' => 100000,
                'change' => 0,
            ]);

        $transaction = Transaction::with(['details', 'profits'])->latest('id')->first();

        $response->assertRedirect(route('transactions.print', $transaction->invoice));
        $this->assertNotNull($transaction);
        $this->assertSame($shift->id, $transaction->cashier_shift_id);
        $this->assertSame(95000, (int) $transaction->grand_total);
        $this->assertSame(5000, (int) $transaction->discount);
        $this->assertSame(100000, (int) $transaction->cash);
        $this->assertSame(5000, (int) $transaction->change);

        $detail = $transaction->details->first();
        $this->assertSame(60000, (int) $detail->base_unit_price);
        $this->assertSame(50000, (int) $detail->unit_price);
        $this->assertSame(100000, (int) $detail->price);
        $this->assertSame(20000, (int) $detail->discount_total);
        $this->assertSame('Harga Spesial Produk', $detail->pricing_rule_name);

        $profit = $transaction->profits->first();
        $this->assertSame(5000, (int) $profit->total);
        $this->assertDatabaseMissing('carts', [
            'cashier_id' => $cashier->id,
        ]);
        $this->assertSame(23, $product->fresh()->stock);
    }

    private function createUserWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function openShiftFor(User $cashier)
    {
        return \App\Models\CashierShift::create([
            'user_id' => $cashier->id,
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'expected_cash' => 100000,
            'status' => 'open',
        ]);
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Snack Promo',
            'description' => 'Kategori promo',
            'image' => 'category.png',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'image' => 'product.png',
            'barcode' => 'BRCD-'.Str::upper(Str::random(10)),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'title' => 'Produk Promo',
            'description' => 'Produk untuk pengujian promo.',
            'buy_price' => 45000,
            'sell_price' => 60000,
            'stock' => 25,
        ]);
    }
}
