<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CategoryBrandAnalyticsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Deep Analytics';
    protected static ?string $title = 'Category & Brand Insights';

    protected static string $view = 'filament.pages.category-brand-analytics-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CategoryRevenueChart::class,
            \App\Filament\Widgets\BrandPerformanceTable::class,
        ];
    }
}
