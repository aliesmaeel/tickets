<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventSeatResource\Pages;
use App\Filament\Resources\EventSeatResource\RelationManagers;
use App\Models\EventSeat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventSeatResource extends Resource
{
    protected static ?string $model = EventSeat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_name')
                    ->relationship('event', 'name')
                    ->required(),
                Forms\Components\TextInput::make('seat_class_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('seat_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_reserved')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seat_class_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seat_number')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_reserved')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListEventSeats::route('/'),
            'create' => Pages\CreateEventSeat::route('/create'),
            'edit' => Pages\EditEventSeat::route('/{record}/edit'),
        ];
    }
}
