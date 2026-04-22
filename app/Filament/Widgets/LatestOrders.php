<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\KeyValue;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action;
use App\Models\Order;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Section, Textarea, Grid};
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Latest Orders';
    public function table(Table $table): Table
    {
        return $table
           ->query(
                Order::query()
                    ->orderBy('created_at', 'desc') // Order by created_at descending
            )
            ->columns([
                TextColumn::make('id')->label('Order ID')->sortable(),
                TextColumn::make('customer.name')->label('Customer')->sortable(),
                TextColumn::make('current_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'processing' => 'info',
                        'packing' => 'secondary',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'returned' => 'danger',
                        'refunded' => 'dark',
                    })
                    ->sortable(),
                TextColumn::make('order_date')->label('Order Date')->date(),
                TextColumn::make('total_amount')->label('Total Amount')->money('NPR')->sortable(),
                TextColumn::make('discounted_total')->label('Discounted Total')->money('NPR'),
                TextColumn::make('net_total')->label('Net Total')->money('NPR')->sortable(),
            ])
            ->searchable()
            ->defaultPaginationPageOption(5)
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('download_invoice')
                    ->label('Download Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        // Eager load relationships to prevent N+1 issues during PDF generation
                        $record->load(['customer', 'OrderItem.product', 'store']);
                        
                        // Load the view and pass the order data
                        $pdf = Pdf::loadView('invoices.pdf', ['order' => $record]);
                        
                        // Return the downloaded PDF directly to the user's browser
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "Invoice-{$record->id}.pdf"
                        );
                    })
            ])
            ->recordUrl(fn (Order $record) => route('filament.admin.resources.orders.edit', ['record' => $record->getKey()]));
    }
}
