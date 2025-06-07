<?php

namespace App\Enums;

use App\Models\Customer;
use App\Models\User;
use App\Traits\Enumable;

enum UserType: string
{
    use Enumable;

    // case User = 'user';

    case Customer = 'customer';

    public static function fromMorphType(string $morphType): ?self
    {
        $typeMap = [
           // 'App\Models\User' => self::User,
            'App\Models\Customer' => self::Customer,
        ];

        return $typeMap[$morphType] ?? null;
    }

    public function getPath(): string
    {
        return match ($this) {
           // self::User => User::class,
            self::Customer => Customer::class,
        };
    }

    public static function getPathFromString(string $userType): string
    {
        return match ($userType) {
           // self::User->value => User::class,
            self::Customer->value => Customer::class,
            default => 'Not Found',
        };
    }
}
