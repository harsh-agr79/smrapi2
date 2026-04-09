<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class BrandPerformanceTable extends BaseWidget {
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Brand Performance Metrics';
    // protected static bool $isLazy = false;

    public function table( Table $table ): Table {
        return $table
        ->query(
            OrderItem::query()
            ->join( 'products', 'order_items.product_id', '=', 'products.id' )
            ->join( 'brands', 'products.brand_id', '=', 'brands.id' )
            ->select(
                DB::raw( 'MAX(order_items.id) as id' ), // Filament uses this automatically as the key
                'brands.name as brand_name',
                DB::raw( 'COUNT(DISTINCT order_items.order_id) as total_orders' ),
                DB::raw( 'SUM(order_items.quantity) as total_units' ),
                DB::raw( 'SUM(order_items.quantity * order_items.discounted_price) as total_revenue' )
            )
            ->groupBy( 'brands.id', 'brands.name' )
        )
        ->columns( [
            Tables\Columns\TextColumn::make( 'brand_name' )
            ->label( 'Brand' )
            ->searchable()
            ->sortable(),
            Tables\Columns\TextColumn::make( 'total_orders' )
            ->label( 'Orders Containing Brand' )
            ->numeric()
            ->sortable(),
            Tables\Columns\TextColumn::make( 'total_units' )
            ->label( 'Total Units Sold' )
            ->numeric()
            ->sortable(),
            Tables\Columns\TextColumn::make( 'total_revenue' )
            ->label( 'Total Revenue' )
            ->money( 'inr' )
            ->sortable(),
        ] )
        ->defaultSort( 'total_revenue', 'desc' );
    }
}