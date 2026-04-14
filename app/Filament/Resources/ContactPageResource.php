<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactPageResource\Pages;
use App\Filament\Resources\ContactPageResource\RelationManagers;
use App\Models\ContactPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ContactPageResource extends Resource
{
    protected static ?string $model = ContactPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('SEO Settings')
                ->schema([
                    TextInput::make('meta_title')
                        ->maxLength(60)
                        ->required()
                        ->helperText('Max 60 characters'),

                    Textarea::make('meta_description')
                        ->maxLength(160)
                        ->rows(3)
                        ->required()
                        ->helperText('Max 160 characters'),

                    FileUpload::make('meta_image')
                        ->image()
                        ->directory('seo')
                        ->imagePreviewHeight('150')
                        ->helperText('Recommended: 1200x630'),
                ])->columns(2),

            Section::make('Hero Section')
                ->schema([
                    TextInput::make('hero_text_above_title'),
                    TextInput::make('hero_title')->required(),
                    Textarea::make('hero_description')->rows(3),
                ])->columns(1),

            Section::make('Contact Information')
                ->schema([
                    Repeater::make('contact_info')
                        ->schema([
                            TextInput::make('label')->required(),
                            Repeater::make('values')
                                ->schema([
                                    TextInput::make('value')->required(),
                                ]),
                        ])
                        ->columns(2)
                        ->defaultItems(3),
                ]),            
            ]);
    }

    public static function table(Table $table): Table
    {
         return $table
        ->paginated(false)
            ->columns([
                TextColumn::make('meta_title')
                    ->limit(40),

                TextColumn::make('hero_title')
                    ->limit(30),

                ImageColumn::make('meta_image')
                    ->height(40),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListContactPages::route('/'),
            // 'create' => Pages\CreateContactPage::route('/create'),
            'edit' => Pages\EditContactPage::route('/{record}/edit'),
        ];
    }
}
