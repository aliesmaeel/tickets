<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CustomersChart extends ApexChartWidget
{
    protected static ?string $chartId = 'customersChart';
    protected static ?string $heading = 'Top 10 Customers by Order Count';

    protected function getOptions(): array
    {
        $topCustomersAll = Order::selectRaw('customer_id, COUNT(*) as total')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->take(10)
            ->with('customer') // assuming relation
            ->get();

        $topCustomersEpay = Order::selectRaw('customer_id, COUNT(*) as total')
            ->where('reservation_type', 'Epay')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->take(10)
            ->with('customer')
            ->get();

        $topCustomersCache = Order::selectRaw('customer_id, COUNT(*) as total')
            ->where('reservation_type', 'Cache')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->take(10)
            ->with('customer')
            ->get();

        // Map to customer names
        $labels = $topCustomersAll->pluck('customer.name')->toArray(); // Adjust field if needed

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => true,
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'All Orders',
                    'data' => $topCustomersAll->pluck('total')->toArray(),
                ],
                [
                    'name' => 'Epay Orders',
                    'data' => $this->matchCustomerTotals($labels, $topCustomersEpay),
                ],
                [
                    'name' => 'Cache Orders',
                    'data' => $this->matchCustomerTotals($labels, $topCustomersCache),
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
            ],
        ];
    }

    // Helper method to align datasets
    private function matchCustomerTotals(array $labels, $customers)
    {
        $totalsByName = $customers->mapWithKeys(function ($item) {
            return [$item->customer->name => $item->total];
        });

        return array_map(fn($name) => $totalsByName[$name] ?? 0, $labels);
    }
}
