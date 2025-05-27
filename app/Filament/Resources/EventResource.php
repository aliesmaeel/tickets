<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Filament\Widgets\GenderDistributionChart;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\FilamentTranslateField\Forms\Component\Translate;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Translate::make()
                    ->label('Name')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->default(null)
                    ])->locales(['en', 'ar','kur']),

                Forms\Components\FileUpload::make('image')
                    ->label('Event Image')
                    ->image()
                    ->required()
                    ->maxSize(1024)
                    ->disk('public')
                    ->directory('events')
                    ->preserveFilenames()
                    ->columnSpanFull(),
                Translate::make()
                    ->columnSpanFull()
                    ->label('Address')
                    ->schema([
                    Forms\Components\Textarea::make('address')
                        ->maxLength(255)
                        ->default(null)
                ])->locales(['en', 'ar','kur']),

                Translate::make()
                    ->columnSpanFull()
                    ->label('Address')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(255)
                            ->default(null)
                    ])->locales(['en', 'ar','kur']),
                Forms\Components\TextInput::make('address_link')
                    ->maxLength(250)
                    ->default(null)
                ->helperText('Please the link from browser address bar, not the Google Maps link.
                Example : https://www.google.com/maps/@52.0890557,9.5394413,186096m/data=!3m1!1e3?entry=ttu&g_ep=EgoyMDI1MDUyMS4wIKXMDSoASAFQAw%3D%3D
                ')
                ->columnSpanFull(),
                Forms\Components\FileUpload::make('address_image')
                    ->label('Address Image')
                    ->image()
                    ->required()
                    ->maxSize(1024)
                    ->disk('public')
                    ->preserveFilenames()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('start_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('end_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('display_start_date')
                    ->required(),
                Forms\Components\DateTimePicker::make('display_end_date')
                    ->required(),
               Forms\Components\TextInput::make('time_to_place_cache_order')
                    ->label('Time to Place Cache Order')
                    ->helperText('Time in minutes So Customers can place the order.')
                    ->numeric()
                    ->minValue(0)
                    ->default(60)
                    ->required(),
                Forms\Components\TextInput::make('max_cache_orders')
                    ->label('Max Cache Orders')
                    ->helperText('Maximum number of cache orders allowed for this event.')
                    ->numeric()
                    ->minValue(0)
                    ->default(100)
                    ->required(),

               Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->required()
                   ->getOptionLabelFromRecordUsing(fn ($record) => $record->name['en'] ?? $record->name),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name['en'] ?? $record->name)
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
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->name['en'] ?? $record->name;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->description['en'] ?? $record->description;
                    }),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('start_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('end_time')
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
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye'),
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
            'view' => Pages\ViewEvent::route('/{record}'),
        ];
    }
}
