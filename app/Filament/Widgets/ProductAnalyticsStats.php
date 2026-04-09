<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsStats extends BaseWidget
{
    // protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $outOfStock = Product::where('stock', false)->count(); 
        
        $bestSeller = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->first();

        return [
            Stat::make('Total Catalog Size', number_format($totalProducts))
                ->description('Active products in database')
                ->icon('heroicon-o-rectangle-stack'),

            Stat::make('Out of Stock Items', number_format($outOfStock))
                ->description('Requires inventory attention')
                ->color($outOfStock > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('All-Time Best Seller', $bestSeller && $bestSeller->product ? $bestSeller->product->name : 'N/A')
                ->description($bestSeller ? number_format($bestSeller->total_sold) . ' units sold' : 'No sales yet')
                ->color('primary')
                ->icon('heroicon-o-star'),
        ];
    }
}