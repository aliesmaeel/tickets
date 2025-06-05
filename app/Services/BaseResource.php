<?php

namespace App\Services;

use Filament\Resources\Resource;

class BaseResource extends Resource
{

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete ', static::getModel());
    }
}
