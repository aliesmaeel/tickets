<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->with(['customer', 'event'])
            ->where('reservation_status', false)
            ->where('reservation_type', 'Cache');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.name.en')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_coupon')
                    ->label('Discount Code')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_wallet_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_price')
                    ->label('Final Price')
                    ->getStateUsing(function ($record) {
                        return number_format($record->total_price - $record->discount_wallet_value, 2);
                    }),
                Tables\Columns\IconColumn::make('reservation_status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirmReservation')
                    ->label('Confirm Reservation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => !$record->reservation_status)
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $wallet = $record->customer->wallet;
                        $wallet->increment('points', (int)($record->total_price * Setting::getRate('money_to_point_rate')));

                        $record->update([
                            'reservation_status' => true,
                        ]);

                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }
public static function canCreate(): bool
{
    return false;
}

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
