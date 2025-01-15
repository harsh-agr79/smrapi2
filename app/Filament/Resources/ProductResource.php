<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource {
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form( Form $form ): Form {
        return $form
        ->schema( [
            Forms\Components\TextInput::make( 'name' )->required()->label( 'Product Name' ),
            Forms\Components\Select::make( 'brand_id' )
            ->relationship( 'brand', 'name' )
            ->label( 'Brand' )
            ->required(),
            Forms\Components\Select::make( 'category_id' )
            ->relationship( 'category', 'category' )
            ->label( 'Category' )
            ->required(),
            Forms\Components\TextInput::make( 'price' )->numeric()->required(),
            Forms\Components\TextInput::make( 'offer' )->numeric(),
            Forms\Components\RichEditor::make('details')
                ->label('Details')
                ->toolbarButtons([
                    'blockquote',
                    'bold',
                    'bulletList',
                    'h1',
                    'h2',
                    'h3',
                    'italic',
                    'link',
                    'orderedList',
                    'redo',
                    'strike',
                    'underline',
                    'undo',
                ])
                ->columnSpanFull()
                ->required(),
            Forms\Components\FileUpload::make( 'images' )
            ->multiple()
            ->directory( 'product' )
            ->image()
            ->enableReordering()
            ->label( 'Product Images' )
            ->required(),
            Forms\Components\Repeater::make( 'variations' )
            ->schema( [
                Forms\Components\TextInput::make( 'specification_1' )->required(),
                Forms\Components\TextInput::make( 'specification_2' )->nullable(),
                Forms\Components\TagsInput::make('colors') // Use TagsInput to handle colors as tags
                ->placeholder('Add Color')
                ->label('Colors')
                ->required(),
                Forms\Components\TextInput::make( 'price' )->numeric()->required(),
            ] )
            ->label( 'Variations' ),
            Forms\Components\Toggle::make( 'hide' )->label( 'Hide Product' )->nullable(),
            Forms\Components\Toggle::make( 'featured' )->label( 'Featured' )->nullable(),
            Forms\Components\Toggle::make( 'trending' )->label( 'Trending' )->nullable(),
            Forms\Components\Toggle::make( 'flash' )->label( 'Flash Sale' )->nullable(),
            Forms\Components\Toggle::make( 'new' )->label( 'New Arrival' )->nullable(),
            Forms\Components\Toggle::make( 'stock' )->label( 'Out of Stock' )->nullable(),
        ] );
    }

    public static function table( Table $table ): Table {
        return $table
        ->columns( [
            Tables\Columns\TextColumn::make( 'name' )->label( 'Name' )->searchable(),
            Tables\Columns\TextColumn::make( 'price' )->label( 'Price' )->sortable(),
            Tables\Columns\BooleanColumn::make( 'featured' )->label( 'Featured' ),
            Tables\Columns\BooleanColumn::make( 'new' )->label( 'New' ),
            Tables\Columns\BooleanColumn::make( 'flash' )->label( 'Flash' ),
            Tables\Columns\BooleanColumn::make( 'trending' )->label( 'Trending' ),
            Tables\Columns\BooleanColumn::make('stock')
            ->label('Stock')
            ->trueIcon('heroicon-o-x-circle') // Icon for true value
            ->falseIcon('heroicon-o-check-circle')
            ->trueColor('danger') // Optional: Color for true value
            ->falseColor('success'), // Icon for false value
            Tables\Columns\TextColumn::make( 'offer' )->label( 'Offer' ),
            Tables\Columns\ImageColumn::make( 'images' )->label( 'Images' ),
            Tables\Columns\TextColumn::make( 'ordernum' )->label( 'Order' )->sortable(),
        ] )
        ->filters( [
            // Tables\Filters\Filter::make( 'featured' )->toggle()->label( 'Featured' ),
            // Tables\Filters\Filter::make( 'offer' )->toggle()->label( 'Offer' ),
            // Tables\Filters\Filter::make( 'trending' )->toggle()->label( 'Trending' ),
            // Tables\Filters\Filter::make( 'new' )->toggle()->label( 'New Arrival' ),
        ] )
        ->reorderable('ordernum')
        ->defaultSort('ordernum')
        ->actions( [
            Tables\Actions\EditAction::make(),
        ] )
        ->bulkActions( [
            Tables\Actions\BulkActionGroup::make( [
                Tables\Actions\DeleteBulkAction::make(),
            ] ),
        ] );
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListProducts::route( '/' ),
            'create' => Pages\CreateProduct::route( '/create' ),
            'edit' => Pages\EditProduct::route( '/{record}/edit' ),
        ];
    }
}
