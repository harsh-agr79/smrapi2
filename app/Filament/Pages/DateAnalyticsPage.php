<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DateAnalyticsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Deep Analytics';
    protected static ?string $title = 'Date-Wise Insights';

    protected static string $view = 'filament.pages.date-analytics-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MonthlyRevenueChart::class,
            \App\Filament\Widgets\DailySalesTable::class,
        ];
    }
}
