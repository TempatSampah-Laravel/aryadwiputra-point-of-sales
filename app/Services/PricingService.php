<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\PricingRule;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PricingService
{
    public function __construct(
        private readonly LoyaltyService $loyaltyService
    ) {}

    public function getActiveRules(?CarbonInterface $at = null): Collection
    {
        $at = $at ?? now();

        return PricingRule::query()
            ->with(['product:id,title,sell_price', 'category:id,name'])
            ->where('is_active', true)
            ->where(function ($query) use ($at) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function ($query) use ($at) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            })
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();
    }

    public function previewCart(iterable $carts, ?Customer $customer = null, ?CarbonInterface $at = null): array
    {
        $cartCollection = collect($carts)
            ->filter(fn ($cart) => $cart instanceof Cart && $cart->product);
        $rules = $this->getActiveRules($at);

        return $this->buildPreview($cartCollection, $customer, $rules);
    }

    public function previewProducts(iterable $products, ?Customer $customer = null, ?CarbonInterface $at = null): Collection
    {
        $rules = $this->getActiveRules($at);

        return collect($products)
            ->filter(fn ($product) => $product instanceof Product)
            ->mapWithKeys(function (Product $product) use ($customer, $rules) {
                return [$product->id => $this->calculateProductPrice($product, 1, $customer, $rules)];
            });
    }

    public function calculateProductPrice(
        Product $product,
        int $qty = 1,
        ?Customer $customer = null,
        ?Collection $rules = null
    ): array {
        $rules = $rules ?? $this->getActiveRules();
        $baseUnitPrice = (int) $product->sell_price;
        $quantity = max(1, $qty);

        $candidate = collect($rules)
            ->filter(fn (PricingRule $rule) => $this->matchesCustomerScope($rule, $customer))
            ->filter(fn (PricingRule $rule) => $this->matchesTarget($rule, $product))
            ->map(function (PricingRule $rule) use ($baseUnitPrice, $quantity) {
                $unitDiscount = $this->resolveUnitDiscount($rule, $baseUnitPrice);
                $lineDiscount = $unitDiscount * $quantity;

                return [
                    'rule' => $rule,
                    'unit_discount' => $unitDiscount,
                    'line_discount' => $lineDiscount,
                ];
            })
            ->filter(fn (array $match) => $match['unit_discount'] > 0)
            ->sortBy([
                ['rule.priority', 'desc'],
                ['line_discount', 'desc'],
                ['rule.id', 'asc'],
            ])
            ->first();

        $unitDiscount = (int) ($candidate['unit_discount'] ?? 0);
        $lineDiscount = (int) ($candidate['line_discount'] ?? 0);
        $appliedRule = $candidate['rule'] ?? null;
        $effectiveUnitPrice = max(0, $baseUnitPrice - $unitDiscount);

        return [
            'base_unit_price' => $baseUnitPrice,
            'effective_unit_price' => $effectiveUnitPrice,
            'quantity' => $quantity,
            'line_base_total' => $baseUnitPrice * $quantity,
            'line_total' => $effectiveUnitPrice * $quantity,
            'line_discount_total' => $lineDiscount,
            'pricing_rule' => $appliedRule ? [
                'id' => $appliedRule->id,
                'name' => $appliedRule->name,
                'label' => $this->ruleLabel($appliedRule),
                'priority' => (int) $appliedRule->priority,
                'target_type' => $appliedRule->target_type,
                'customer_scope' => $appliedRule->customer_scope,
                'eligible_loyalty_tiers' => $appliedRule->eligible_loyalty_tiers,
            ] : null,
        ];
    }

    public function ruleLabel(PricingRule $rule): string
    {
        return match ($rule->discount_type) {
            PricingRule::TYPE_PERCENTAGE => rtrim(rtrim(number_format((float) $rule->discount_value, 2, '.', ''), '0'), '.').'% OFF',
            PricingRule::TYPE_FIXED_AMOUNT => 'Hemat Rp '.number_format((float) $rule->discount_value, 0, ',', '.'),
            PricingRule::TYPE_FIXED_PRICE => 'Harga Rp '.number_format((float) $rule->discount_value, 0, ',', '.'),
            default => $rule->name,
        };
    }

    private function buildPreview(Collection $carts, ?Customer $customer, Collection $rules): array
    {
        $items = $carts->values()->map(function (Cart $cart) use ($customer, $rules) {
            $pricing = $this->calculateProductPrice($cart->product, (int) $cart->qty, $customer, $rules);

            return [
                'cart_id' => $cart->id,
                'product_id' => $cart->product_id,
                'product_title' => $cart->product?->title,
                'qty' => (int) $cart->qty,
                ...$pricing,
            ];
        });

        $baseSubtotal = (int) $items->sum('line_base_total');
        $promoDiscountTotal = (int) $items->sum('line_discount_total');
        $subtotalAfterPromo = max(0, $baseSubtotal - $promoDiscountTotal);

        return [
            'items' => $items->all(),
            'summary' => [
                'base_subtotal' => $baseSubtotal,
                'promo_discount_total' => $promoDiscountTotal,
                'subtotal_after_promo' => $subtotalAfterPromo,
            ],
        ];
    }

    private function matchesCustomerScope(PricingRule $rule, ?Customer $customer): bool
    {
        return match ($rule->customer_scope) {
            PricingRule::SCOPE_ALL => true,
            PricingRule::SCOPE_WALK_IN => $customer === null,
            PricingRule::SCOPE_REGISTERED => $customer !== null,
            PricingRule::SCOPE_MEMBER => $this->matchesMemberRule($rule, $customer),
            default => false,
        };
    }

    private function matchesMemberRule(PricingRule $rule, ?Customer $customer): bool
    {
        if (! $customer || ! $customer->is_loyalty_member) {
            return false;
        }

        $eligibleTiers = collect($rule->eligible_loyalty_tiers ?? [])
            ->filter()
            ->values();

        if ($eligibleTiers->isEmpty()) {
            return true;
        }

        return $eligibleTiers->contains($customer->loyalty_tier);
    }

    private function matchesTarget(PricingRule $rule, Product $product): bool
    {
        return match ($rule->target_type) {
            PricingRule::TARGET_ALL => true,
            PricingRule::TARGET_PRODUCT => (int) $rule->product_id === (int) $product->id,
            PricingRule::TARGET_CATEGORY => (int) $rule->category_id === (int) $product->category_id,
            default => false,
        };
    }

    private function resolveUnitDiscount(PricingRule $rule, int $baseUnitPrice): int
    {
        $discount = match ($rule->discount_type) {
            PricingRule::TYPE_PERCENTAGE => (int) round($baseUnitPrice * ((float) $rule->discount_value / 100)),
            PricingRule::TYPE_FIXED_AMOUNT => (int) round((float) $rule->discount_value),
            PricingRule::TYPE_FIXED_PRICE => max(0, $baseUnitPrice - (int) round((float) $rule->discount_value)),
            default => 0,
        };

        return min($baseUnitPrice, max(0, $discount));
    }
}
