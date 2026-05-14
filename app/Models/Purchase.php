<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product', 'category_id', 'supplier_id',
        'cost_price', 'quantity', 'expiry_date', 'image',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'cost_price'  => 'float',
        'quantity'    => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeExpired($query)
    {
        return $query->whereDate('expiry_date', '<=', Carbon::today());
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('quantity', '>', 0)->where('quantity', '<=', $threshold);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity > 0 && $this->quantity <= 10;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/purchases/' . $this->image) : null;
    }
}
