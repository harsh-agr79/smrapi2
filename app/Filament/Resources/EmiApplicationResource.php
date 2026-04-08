<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmiApplicationResource\Pages;
use App\Filament\Resources\EmiApplicationResource\RelationManagers;
use App\Models\EmiApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class EmiApplicationResource extends Resource
{
    protected static ?string $model = EmiApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name') // Assuming your products table has a 'name' or 'title' column
                    ->label('Product')
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                Section::make('Applicant Information')
                    ->schema([
                        FileUpload::make('applicant_citizenship_front')
                            ->label('Citizenship Front')
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('applicant_citizenship_back')
                            ->label('Citizenship Back')
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        TextInput::make('applicant_father_name')->label('Father\'s Name'),
                        TextInput::make('applicant_mother_name')->label('Mother\'s Name'),
                        TextInput::make('applicant_grandfather_name')->label('Grandfather\'s Name'),
                        TextInput::make('applicant_wife_name')->label('Wife\'s Name'),
                        TextInput::make('applicant_current_location')->label('Current Location')->columnSpanFull(),
                        TextInput::make('applicant_phone_number')->label('Phone Number')->tel(),
                        TextInput::make('applicant_email')->label('Email')->email(),
                        TextInput::make('applicant_relation_with_guarantor')->label('Relation with Guarantor'),
                        TextInput::make('applicant_source_of_income')->label('Source of Income'),
                    ])->columns(2),

                Section::make('Guarantor Information')
                    ->schema([
                        FileUpload::make('guarantor_citizenship_front')
                            ->label('Citizenship Front')
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('guarantor_citizenship_back')
                            ->label('Citizenship Back')
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        TextInput::make('guarantor_father_name')->label('Father\'s Name'),
                        TextInput::make('guarantor_mother_name')->label('Mother\'s Name'),
                        TextInput::make('guarantor_grandfather_name')->label('Grandfather\'s Name'),
                        TextInput::make('guarantor_wife_name')->label('Wife\'s Name'),
                        TextInput::make('guarantor_current_location')->label('Current Location')->columnSpanFull(),
                        TextInput::make('guarantor_phone_number')->label('Phone Number')->tel(),
                        TextInput::make('guarantor_email')->label('Email')->email(),
                        TextInput::make('guarantor_relation')->label('Relation'),
                        TextInput::make('guarantor_source_of_income')->label('Source of Income'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('product.name') // Fetches the related product's name
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('applicant_phone_number')->label('Applicant Phone')->searchable(),
                Tables\Columns\TextColumn::make('guarantor_phone_number')->label('Guarantor Phone')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListEmiApplications::route('/'),
            'create' => Pages\CreateEmiApplication::route('/create'),
            'edit' => Pages\EditEmiApplication::route('/{record}/edit'),
        ];
    }
}
