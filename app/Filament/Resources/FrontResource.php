<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FrontResource\Pages;
use App\Filament\Resources\FrontResource\RelationManagers;
use App\Models\Front;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FrontResource extends Resource
{
    protected static ?string $model = Front::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Front Page';

    protected static ?string $navigationLabel = 'Cover Photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                ->label('Image')
                ->directory('docs')
                ->image(),
                Forms\Components\Hidden::make('type')
                ->default('image')
                ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Image'),
                Tables\Columns\TextColumn::make( 'ordernum' )->label( 'Order' )->sortable(),
            ])
            ->filters([
                //
            ])
            ->reorderable('ordernum')
            ->defaultSort('ordernum')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFronts::route('/'),
            'create' => Pages\CreateFront::route('/create'),
            'edit' => Pages\EditFront::route('/{record}/edit'),
        ];
    }
}
