<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    use HasFactory;

    public const TARGET_ALL = 'all';

    public const TARGET_PRODUCT = 'product';

    public const TARGET_CATEGORY = 'category';

    public const SCOPE_ALL = 'all';

    public const SCOPE_WALK_IN = 'walk_in';

    public const SCOPE_REGISTERED = 'registered';

    public const SCOPE_MEMBER = 'member';

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED_AMOUNT = 'fixed_amount';

    public const TYPE_FIXED_PRICE = 'fixed_price';

    protected $fillable = [
        'name',
        'is_active',
        'priority',
        'target_type',
        'product_id',
        'category_id',
        'customer_scope',
        'eligible_loyalty_tiers',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'product_id' => 'integer',
        'category_id' => 'integer',
        'eligible_loyalty_tiers' => 'array',
        'discount_value' => 'float',
        'created_by' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
