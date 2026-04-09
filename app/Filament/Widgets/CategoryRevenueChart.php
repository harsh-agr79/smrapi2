<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue by Category';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';
    // protected static bool $isLazy = false;

    protected function getData(): array
    {
        $categoryData = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.category', DB::raw('SUM(order_items.quantity * order_items.discounted_price) as total_revenue'))
            ->groupBy('categories.category')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Revenue (₹)',
                    'data' => $categoryData->pluck('total_revenue')->toArray(),
                    'backgroundColor' => '#10b981',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $categoryData->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}