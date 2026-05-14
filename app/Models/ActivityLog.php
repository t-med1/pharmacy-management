<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Action color helper for UI badges ─────────────────────────────────────

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created'   => 'success',
            'updated'   => 'info',
            'deleted'   => 'danger',
            'sold'      => 'primary',
            'purchased' => 'warning',
            default     => 'secondary',
        };
    }

    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created'   => 'fa-plus-circle',
            'updated'   => 'fa-pencil-alt',
            'deleted'   => 'fa-trash',
            'sold'      => 'fa-cash-register',
            'purchased' => 'fa-box',
            default     => 'fa-circle',
        };
    }
}
