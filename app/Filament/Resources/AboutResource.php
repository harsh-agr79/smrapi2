<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutResource\Pages;
use App\Filament\Resources\AboutResource\RelationManagers;
use App\Models\About;
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

class AboutResource extends Resource
{
    protected static ?string $model = About::class;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';


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

            Section::make('Statistics')
                ->schema([
                    Repeater::make('statistics')
                        ->schema([
                            TextInput::make('value')->required()->label('Number'),
                            TextInput::make('label')->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(3),
                ]),

            Section::make('Who We Are')
                ->schema([
                    TextInput::make('who_title')->required(),
                    Textarea::make('who_description')->rows(5),
                ]),

            Section::make('Mission & Vision')
                ->schema([
                    Textarea::make('mission_text')->rows(3)->required(),
                    Textarea::make('vision_text')->rows(3)->required(),
                    Textarea::make('team_quote')->rows(2),
                ])->columns(1),

            Section::make('Why Choose Us')
                ->schema([
                    TextInput::make('why_choose_title')->required(),

                    Repeater::make('why_choose_cards')
                        ->schema([
                            Grid::make(2)->schema([

                                FileUpload::make('icon')
                                    ->image()
                                    ->directory('icons')
                                    ->required()
                                    ->imagePreviewHeight('80'),

                                TextInput::make('icon_alt')
                                    ->label('Icon Alt Text')
                                    ->required(),

                                TextInput::make('title')
                                    ->required(),

                                Textarea::make('description')
                                    ->rows(2)
                                    ->required(),

                            ])
                        ])
                        ->defaultItems(6)
                        ->columns(1),
                ]),

            Section::make('Call To Action (CTA)')
                ->schema([
                    TextInput::make('cta_title')->required(),
                    Textarea::make('cta_description')->rows(2),

                    Grid::make(2)->schema([
                        TextInput::make('cta_button_text')->required(),
                        TextInput::make('cta_button_link')->url(),

                        TextInput::make('cta_button_text2'),
                        TextInput::make('cta_button_link2')->url(),
                    ]),
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
            'index' => Pages\ListAbouts::route('/'),
            // 'create' => Pages\CreateAbout::route('/create'),
            'edit' => Pages\EditAbout::route('/{record}/edit'),
        ];
    }
}
