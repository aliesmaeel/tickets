<?php

namespace App\Services;

use Filament\Resources\Resource;

class BaseResource extends Resource
{

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete ', static::getModel());
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view-any', static::getModel());
    }
//    public static function canViewAny(): bool
//    {
//        return auth()->user()->can('view-any', self::model);
//    }
//
//    public static function canView(Model $record): bool
//    {
//        return auth()->user()->can('view', $record);
//    }
//
//    public static function canCreate(): bool
//    {
//        return auth()->user()->can('create', Post::class);
//    }
//
//    public static function canEdit(Model $record): bool
//    {
//        return auth()->user()->can('update', $record);
//    }
//
//    public static function canDelete(Model $record): bool
//    {
//        return auth()->user()->can('delete', $record);
//    }
}
