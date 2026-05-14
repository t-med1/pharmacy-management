<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id', 'price', 'discount', 'description',
    ];

    protected $casts = [
        'price'    => 'float',
        'discount' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeInStock($query)
    {
        return $query->whereHas('purchase', fn ($q) => $q->where('quantity', '>', 0));
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        return $this->purchase->product ?? 'Unknown';
    }

    public function getOriginalPriceAttribute(): float
    {
        if ($this->discount > 0) {
            return round($this->price / (1 - $this->discount / 100), 2);
        }
        return $this->price;
    }
}
