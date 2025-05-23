<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'code', 'type', 'value', 'used_count', 'expires_at', 'is_active','max_uses'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->is_active &&  $this->expires_at->isFuture() && $this->used_count < $this->max_uses;
    }

    public function applyDiscount(float $total): float
    {
        return match ($this->type) {
            'fixed' => max(0, $total - $this->value),
            'percentage' => max(0, $total - ($total * $this->value / 100)),
            default => $total,
        };
    }
}
