<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Year-to-Date Monthly Revenue';
    protected int | string | array $columnSpan = 'full';
    // protected static bool $isLazy = false;

    protected function getData(): array
    {
        $data = Trend::query(Order::whereNotIn('current_status', ['cancelled', 'failed', 'refunded']))
            ->dateColumn('order_date')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('net_total');

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Revenue (₹)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)', // Purple
                    'borderColor' => '#8b5cf6',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M Y')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}