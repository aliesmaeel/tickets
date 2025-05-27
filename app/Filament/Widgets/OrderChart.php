<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrderChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ordersChart';
    protected static ?string $heading = 'Orders in Last 30 Days';

    protected function getFilters(): ?array
    {
        return [
            'last7' => 'Last 7 Days',
            'this_month' => 'This Month',
            'last30' => 'Last 30 Days',
            'this_year' => 'This Year',
        ];
    }

    protected function getOptions(): array
    {
        $range = match ($this->filter) {
            'last7' => now()->subDays(7),
            'this_month' => now()->startOfMonth(),
            default => now()->subDays(30),
            'this_year' => now()->startOfYear(),
        };

        $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', $range)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
            ],
            'series' => [
                [
                    'name' => 'Orders',
                    'data' => $orders->pluck('total')->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $orders->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray(),
            ],
        ];
    }

}
