<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProductAnalyticsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Deep Analytics';
    protected static ?string $title = 'Product Performance';

    protected static string $view = 'filament.pages.product-analytics-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ProductAnalyticsStats::class,
            \App\Filament\Widgets\ProductPerformanceTable::class,
        ];
    }
}
