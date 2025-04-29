<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Translate::make()
                    ->columnSpanFull()
                    ->label('Description')
                    ->schema([
                    Forms\Components\Textarea::make('description')
                        ->maxLength(255)
                        ->default(null)
                ])->locales(['en', 'ar']),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('address_link')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\DateTimePicker::make('start_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('display_start_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('display_end_date')
                    ->required(),

                Forms\Components\Toggle::make('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('address_link')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_start_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_end_date')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
