<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DailySalesTable extends BaseWidget {
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Daily Sales Ledger';
    // protected static bool $isLazy = false;

    public function table( Table $table ): Table {
        return $table
        ->query(
            Order::query()
            ->whereNotIn( 'current_status', [ 'cancelled', 'failed', 'refunded' ] )
            ->select(
                DB::raw( 'MAX(id) as id' ), // Gives Filament the unique key it needs
                DB::raw( 'DATE(order_date) as date' ),
                DB::raw( 'COUNT(id) as total_orders' ),
                DB::raw( 'SUM(total_amount) as gross_sales' ),
                DB::raw( 'SUM(discount) as total_discounts' ),
                DB::raw( 'SUM(net_total) as net_sales' )
            )
            ->groupBy( 'date' )
        )
        ->columns( [
            Tables\Columns\TextColumn::make( 'date' )
            ->label( 'Date' )
            ->date()
            ->sortable()
            ->searchable(),
            Tables\Columns\TextColumn::make( 'total_orders' )
            ->label( 'Orders Placed' )
            ->numeric()
            ->sortable(),
            Tables\Columns\TextColumn::make( 'gross_sales' )
            ->label( 'Gross Sales' )
            ->money( 'inr' )
            ->sortable(),
            Tables\Columns\TextColumn::make( 'total_discounts' )
            ->label( 'Discounts Given' )
            ->money( 'inr' )
            ->color( 'danger' )
            ->sortable(),
            Tables\Columns\TextColumn::make( 'net_sales' )
            ->label( 'Net Sales' )
            ->money( 'inr' )
            ->color( 'success' )
            ->sortable()
            ->weight( 'bold' ),
        ] )
        ->defaultSort( 'date', 'desc' );
    }
}