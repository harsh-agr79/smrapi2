<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;


class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('heading')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->rules(['regex:/^[a-zA-Z0-9\-]+$/'])
                ->helperText('Only letters, numbers, and hyphens (-) are allowed')
                ->maxLength(255),
            
            // Forms\Components\TextInput::make('subheading')
            //     ->maxLength(255),

            Forms\Components\FileUpload::make('cover_photo')
                ->image()
                ->directory('blog-covers')
                ->label('Cover Photo')
                ->imageEditor(),

            Forms\Components\TextInput::make('meta_title')
                ->label('Meta Title')
                ->maxLength(255),
            Forms\Components\Textarea::make('meta_description')
                ->label('Meta Description'),

            Forms\Components\DatePicker::make('published_on')
                ->label('Publish Date')
                ->required(),

            Forms\Components\RichEditor::make('content')
                ->label('Blog Content')
                ->toolbarButtons([
                    'attachFiles',
                    'blockquote',
                    'bold',
                    'bulletList',
                    'codeBlock',
                    'h1',
                    'h2',
                    'h3',
                    'italic',
                    'link',
                    'orderedList',
                    'redo',
                    'table',
                    'strike',
                    'underline',
                    'undo',
                ])
                ->columnSpanFull()
                ->required(),
            Forms\Components\Toggle::make('pinned')
                ->label('Pin this blog')
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $record) {
                    if ($state) {
                        // Unpin other blogs
                        Blog::where('id', '!=', $record->id)->update(['pinned' => false]);
                    }
                }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_photo')
                    ->label('Cover'),
                Tables\Columns\TextColumn::make('heading')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_on')
                    ->label('Published On')
                    ->sortable()
                    ->date(),
                Tables\Columns\BooleanColumn::make('pinned')->label('Pinned'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([
                Filter::make('Pinned Blogs')->query(fn (Builder $query) => $query->where('pinned', true)),
            ])
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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
