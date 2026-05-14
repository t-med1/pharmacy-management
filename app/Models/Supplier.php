<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'address', 'product', 'comment',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
