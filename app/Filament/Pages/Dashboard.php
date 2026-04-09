<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        // By explicitly returning only these two, we block all your 
        // custom analytics widgets from cluttering the main dashboard!
        return [
            \App\Filament\Widgets\ProductAnalyticsStats::class,
            \App\Filament\Widgets\DailySalesTable::class,
            \App\Filament\Widgets\CategoryRevenueChart::class,
            \App\Filament\Widgets\BrandPerformanceTable::class,
        ];
    }
}