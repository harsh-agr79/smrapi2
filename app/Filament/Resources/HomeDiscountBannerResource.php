<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeDiscountBannerResource\Pages;
use App\Filament\Resources\HomeDiscountBannerResource\RelationManagers;
use App\Models\HomeDiscountBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HomeDiscountBannerResource extends Resource
{
    protected static ?string $model = HomeDiscountBanner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sale_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_title')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('discount_amount')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeDiscountBanners::route('/'),
            'create' => Pages\CreateHomeDiscountBanner::route('/create'),
            'edit' => Pages\EditHomeDiscountBanner::route('/{record}/edit'),
        ];
    }
}
