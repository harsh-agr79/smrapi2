<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetaTagResource\Pages;
use App\Filament\Resources\MetaTagResource\RelationManagers;
use App\Models\MetaTag;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MetaTagResource extends Resource
{
    protected static ?string $model = MetaTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = "Pages";

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\TextInput::make('slug')
                ->label('slug')
                ->disabled()
                ->required()
                ->maxLength(255),
                 Forms\Components\TextInput::make('meta_title')
                ->label('Meta Title')
                ->required()
                ->maxLength(255),
                 Forms\Components\TextInput::make('meta_description')
                ->label('Meta Description')
                ->required()
                ->maxLength(255),
                 Forms\Components\FileUpload::make('meta_image')
                ->label('Meta Image')
                ->image()
                ->directory('meta-images') // optional: uploads to storage/app/meta-images
                ->nullable(),
                TextInput::make("meta_image_alt"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('meta_image'),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('meta_title')->searchable(),
                Tables\Columns\TextColumn::make('meta_description')->searchable(),
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
    public static function canCreate(): bool
    {
        return false;
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
            'index' => Pages\ListMetaTags::route('/'),
            'create' => Pages\CreateMetaTag::route('/create'),
            'edit' => Pages\EditMetaTag::route('/{record}/edit'),
        ];
    }
}
