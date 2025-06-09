<?php
namespace App\Filament\Pages;

use App\Support\StaticPermissions;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

use App\Models\Order;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\alert;

class IncomeReport extends Page implements Tables\Contracts\HasTable
{
    public float $totalIncome = 0;

    public function __construct()
    {
        $this->totalIncome = Order::query()->sum('total_price');
    }



    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.income-report';
    protected static ?string $title = 'Income Report';



    public function updatedTableFilters(): void
    {
        $this->updateTotalIncome();
    }

    protected function updateTotalIncome(): void
    {
        $query = $this->getFilteredTableQuery();
        $this->totalIncome = $query->sum('total_price');
    }

    protected function getTableQuery(): Builder
    {
        return Order::query()->with('event');
    }


    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('event.name')
                ->getStateUsing(fn ($record) => $record->event->name['en'] ?? $record->event->name)
                ->label('Event'),
            Tables\Columns\TextColumn::make('reservation_type')->label('Type'),
            Tables\Columns\TextColumn::make('total_price')->label('Total Price'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->label('Created At'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('event_id')
                ->label('Event')
                ->options(Event::all()->pluck('name.en', 'id')),

            Tables\Filters\SelectFilter::make('reservation_type')
                ->label('Reservation Type')
                ->options([
                    'Epay' => 'Epay',
                    'Cache' => 'Cache',
                    'Wallet'=> 'Wallet'
                ]),

            Tables\Filters\Filter::make('created_at')
                ->form([
                    DatePicker::make('from')->label('From Date'),
                    DatePicker::make('to')->label('To Date'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['to'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                }),
        ];
    }


    protected function getTableHeading(): string
    {
        return 'Income / Profit Table';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can(StaticPermissions::INCOME_REPORT);
    }
}

