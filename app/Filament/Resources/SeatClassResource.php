<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeatClassResource\Pages;
use App\Filament\Resources\SeatClassResource\RelationManagers;
use App\Models\Event;
use App\Models\SeatClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;


class SeatClassResource extends Resource
{
    protected static ?string $model = SeatClass::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 0;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->rules(fn (Forms\Get $get, ?\App\Models\SeatClass $record) => [
                        new UniqueTogether(
                            table: 'seat_classes',
                            column1: 'name',
                            column2: 'event_id',
                            value2: $get('event_id'),
                            ignoreId: $record?->id, // null for create, id for edit
                        ),
                    ]),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),

                Forms\Components\ColorPicker::make('color')
                    ->required()
                    ->default('#e02828')
                    ->label('Seat Class Color')
                     ->required(),

                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name['en'] ?? $record->name)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name.en')
                    ->label('Event'),

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
                Tables\Filters\SelectFilter::make('name')
                    ->label('Seat Class Name')
                    ->options(fn () => \App\Models\SeatClass::query()
                        ->where('name', '!=', 'reserved')
                        ->where('name', '!=', 'empty')
                        ->where('name', '!=', 'stage')
                        ->select('name')
                        ->distinct()
                        ->pluck('name', 'name')
                    ),

                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->options(function () {
                        return Event::all()->pluck('name.en', 'id');
                    }),

            ])


            ->actions([
                Tables\Actions\EditAction::make()->disabled(fn (SeatClass $record): bool => in_array($record->name, ['reserved', 'empty', 'stage'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListSeatClasses::route('/'),
            'create' => Pages\CreateSeatClass::route('/create'),
            'edit' => Pages\EditSeatClass::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Seat Events Classes');
    }

   public static function canEdit(Model $record): bool
   {
       return parent::canEdit($record) && !in_array($record->name, ['reserved', 'empty', 'stage']);
   }
    public static function canDelete(Model $record): bool
    {
         return parent::canDelete($record) && !in_array($record->name, ['reserved', 'empty', 'stage']);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereNotIn('name', ['reserved', 'empty', 'stage']);
    }

}
