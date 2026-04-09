<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ProductPerformanceTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'In-Depth Product Metrics';
    // protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                ->select(
                    'product_id', 
                    DB::raw('MAX(id) as id'), // <-- ADD THIS LINE: Gives Filament a unique row key
                    DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                    DB::raw('SUM(quantity) as total_units_sold'), 
                    DB::raw('SUM(quantity * discounted_price) as total_revenue')
                )
                ->with(['product.brand', 'product.category'])
                ->groupBy('product_id')
            )
            ->columns([
               Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.category.category')
                    ->label('Category')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Times Ordered')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_units_sold')
                    ->label('Volume (Units)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Net Product Revenue')
                    ->money('inr')
                    ->sortable(),
            ])->defaultSort('total_revenue', 'desc');
    }
}
