<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'code', 'type', 'value', 'used_count',
        'expires_at', 'is_active', 'max_uses'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public static function findValidCoupon(string $code): ?self
    {
        $coupon = self::where('code', $code)->first();

        return $coupon && $coupon->isValid() ? $coupon : null;
    }

    public function isValid(): bool
    {
        $notExpired = is_null($this->expires_at) || $this->expires_at->isFuture();
        $notUsedUp = is_null($this->max_uses) || $this->used_count < $this->max_uses;

        return $this->is_active && $notExpired && $notUsedUp;
    }

    public function applyDiscount(float $total): float
    {
        return match ($this->type) {
            'fixed' => max(0, $total - $this->value),
            'percentage' => max(0, $total - ($total * $this->value / 100)),
            default => $total,
        };
    }

    public function Orders()
    {
        return $this->hasMany(Order::class);
    }
}
