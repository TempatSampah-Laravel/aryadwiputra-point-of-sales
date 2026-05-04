<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PricingRuleController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly LoyaltyService $loyaltyService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'target_type' => $request->input('target_type'),
        ];

        $rules = PricingRule::query()
            ->with(['product:id,title', 'category:id,name', 'creator:id,name'])
            ->when($filters['search'], function ($query, $search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($filters['status'] !== null && $filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when($filters['target_type'], function ($query, $targetType) {
                $query->where('target_type', $targetType);
            })
            ->orderByDesc('is_active')
            ->orderByDesc('priority')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/PricingRules/Index', [
            'rules' => $rules,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Dashboard/PricingRules/Create', [
            'products' => Product::orderBy('title')->get(['id', 'title', 'sell_price', 'category_id']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tierOptions' => $this->loyaltyService->tierOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRule($request);

        $rule = PricingRule::create([
            ...$validated,
            'created_by' => $request->user()?->id,
        ]);

        $this->auditLogService->log(
            event: 'pricing_rule.created',
            module: 'pricing_rules',
            auditable: $rule,
            description: 'Rule promo/harga dibuat.',
            after: $this->auditPayload($rule->fresh())
        );

        return redirect()
            ->route('pricing-rules.index')
            ->with('success', 'Rule promo berhasil dibuat.');
    }

    public function edit(PricingRule $pricingRule)
    {
        return Inertia::render('Dashboard/PricingRules/Edit', [
            'rule' => $pricingRule,
            'products' => Product::orderBy('title')->get(['id', 'title', 'sell_price', 'category_id']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tierOptions' => $this->loyaltyService->tierOptions(),
        ]);
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        $before = $this->auditPayload($pricingRule);
        $validated = $this->validateRule($request);

        $pricingRule->update($validated);

        $this->auditLogService->log(
            event: 'pricing_rule.updated',
            module: 'pricing_rules',
            auditable: $pricingRule,
            description: 'Rule promo/harga diperbarui.',
            before: $before,
            after: $this->auditPayload($pricingRule->fresh())
        );

        return redirect()
            ->route('pricing-rules.index')
            ->with('success', 'Rule promo berhasil diperbarui.');
    }

    public function destroy(PricingRule $pricingRule)
    {
        $before = $this->auditPayload($pricingRule);
        $pricingRule->delete();

        $this->auditLogService->log(
            event: 'pricing_rule.deleted',
            module: 'pricing_rules',
            auditable: $pricingRule,
            description: 'Rule promo/harga dihapus.',
            before: $before
        );

        return back()->with('success', 'Rule promo berhasil dihapus.');
    }

    private function validateRule(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'priority' => ['required', 'integer', 'min:0'],
            'target_type' => ['required', Rule::in([
                PricingRule::TARGET_ALL,
                PricingRule::TARGET_PRODUCT,
                PricingRule::TARGET_CATEGORY,
            ])],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'customer_scope' => ['required', Rule::in([
                PricingRule::SCOPE_ALL,
                PricingRule::SCOPE_WALK_IN,
                PricingRule::SCOPE_REGISTERED,
                PricingRule::SCOPE_MEMBER,
            ])],
            'eligible_loyalty_tiers' => ['nullable', 'array'],
            'eligible_loyalty_tiers.*' => ['string', Rule::in(array_keys(app(\App\Services\LoyaltyService::class)->tiers()))],
            'discount_type' => ['required', Rule::in([
                PricingRule::TYPE_PERCENTAGE,
                PricingRule::TYPE_FIXED_AMOUNT,
                PricingRule::TYPE_FIXED_PRICE,
            ])],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['target_type'] === PricingRule::TARGET_PRODUCT && empty($validated['product_id'])) {
            $request->validate([
                'product_id' => ['required'],
            ]);
        }

        if ($validated['target_type'] === PricingRule::TARGET_CATEGORY && empty($validated['category_id'])) {
            $request->validate([
                'category_id' => ['required'],
            ]);
        }

        if ($validated['target_type'] !== PricingRule::TARGET_PRODUCT) {
            $validated['product_id'] = null;
        }

        if ($validated['target_type'] !== PricingRule::TARGET_CATEGORY) {
            $validated['category_id'] = null;
        }

        if (
            $validated['discount_type'] === PricingRule::TYPE_PERCENTAGE
            && (float) $validated['discount_value'] > 100
        ) {
            $request->validate([
                'discount_value' => ['max:100'],
            ]);
        }

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $validated['eligible_loyalty_tiers'] = $validated['customer_scope'] === PricingRule::SCOPE_MEMBER
            ? array_values(array_unique($validated['eligible_loyalty_tiers'] ?? []))
            : null;

        return $validated;
    }

    private function auditPayload(PricingRule $rule): array
    {
        return [
            'name' => $rule->name,
            'is_active' => (bool) $rule->is_active,
            'priority' => (int) $rule->priority,
            'target_type' => $rule->target_type,
            'product_id' => $rule->product_id,
            'category_id' => $rule->category_id,
            'customer_scope' => $rule->customer_scope,
            'eligible_loyalty_tiers' => $rule->eligible_loyalty_tiers,
            'discount_type' => $rule->discount_type,
            'discount_value' => (float) $rule->discount_value,
            'starts_at' => optional($rule->starts_at)?->toIso8601String(),
            'ends_at' => optional($rule->ends_at)?->toIso8601String(),
            'notes' => $rule->notes,
        ];
    }
}
