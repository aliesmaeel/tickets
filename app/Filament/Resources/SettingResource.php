<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->disabled()
                    ->required(),

                Forms\Components\TextInput::make('value')
                    ->label(function (Forms\Get $get) {
                        return match ($get('key')) {
                            'money_to_point_rate' => 'Value (1 IQD to Points Rate)',
                            'point_to_money_rate' => 'Value (1 Point to IQD Rate)',
                            default => 'Value',
                        };
                    })
                    ->hint(function (Forms\Get $get) {
                        $value = $get('value');
                        $key = $get('key');

                        if (!$value || $value == 0) {
                            return 'Enter a value to see conversion info.';
                        }

                        return match ($key) {
                            'money_to_point_rate' => '1000 IQD = ' . (1000 * $value) . ' points',
                            'point_to_money_rate' => '1000 points = ' . (1000 * $value) . ' IQD',
                            default => null,
                        };
                    })
                    ->numeric()
                    ->required()
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('value'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
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
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
