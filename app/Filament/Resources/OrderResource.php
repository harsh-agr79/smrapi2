<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Section, Textarea, Grid};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\KeyValue;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Details')->schema([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required(),

                    Select::make('store_id')
                        ->label('Store')
                        ->relationship('store', 'name')
                        ->searchable()
                        ->required(),
    
                    Select::make('current_status')
                        ->label('Order Status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'packing' => 'Packing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'returned' => 'Returned',
                            'refunded' => 'Refunded',
                        ])
                        ->required(),

                    Select::make('payment_status')
                        ->label('Payment Status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid By any method',
                            'cod' => 'COD',
                        ])
                        ->required(),
    
                    DatePicker::make('order_date')->label('Order Date')->required(),
    
                    TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->prefix('रु')
                        ->numeric()
                        ->required()
                        ->readOnly(),
                    TextInput::make('delivery_charge')
                        ->label('Delivery Charge')
                        ->prefix('रु')
                        ->numeric()
                        ->required()
                        ->readOnly(),
    
                    TextInput::make('discount')
                        ->label('Discount')
                        ->prefix('रु')
                        ->numeric()
                        ->readOnly(),
    
                    TextInput::make('discounted_total')
                        ->label('Discounted Total')
                        ->prefix('रु')
                        ->numeric()
                        ->required()
                        ->readOnly(),
    
                    TextInput::make('net_total')
                        ->label('Net Total')
                        ->prefix('रु')
                        ->numeric()
                        ->required()
                        ->readOnly(),
                    Section::make('Billing Address')
                        ->schema(function ($get) {
                            $json = $get('billing_address');
                    
                            $decoded = json_decode($json, true);
                    
                            if (!is_array($decoded)) {
                                return [
                                    Forms\Components\Placeholder::make('billing_address_invalid')
                                        ->content('Invalid billing address.')
                                ];
                            }
                    
                            return collect($decoded)->map(function ($value, $key) {
                                return Forms\Components\Placeholder::make("billing_address_{$key}")
                                    ->label(ucwords(str_replace('_', ' ', $key)))
                                    ->content(is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?: '—'));
                            })->values()->all();
                        })
                        ->columns(2)
                        ->collapsible(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
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
            ->defaultPaginationPageOption(25)
            ->filters([
                Filter::make('created_from')
                    ->form([
                        DatePicker::make('created_from'),
                        // DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                            // ->when(
                            //     $data['created_until'],
                            //     fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            // );
                    }),
                    Filter::make('created_until')
                    ->form([
                        // DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            // ->when(
                            //     $data['created_from'],
                            //     fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            // );
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                
                ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemRelationManager::class,
            RelationManagers\StatusHistoryRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = static::$model;

        return (string) $modelClass::where('current_status', 'pending')->count();
    }
}
