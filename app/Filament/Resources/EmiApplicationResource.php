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
                        TextInput::make('applicant_full_name')
                            ->label('Full Name')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('applicant_permanent_address')
                            ->label('Permanent Address')
                            ->helperText('Tole name, ahile ko naya naam'),
                        TextInput::make('applicant_current_address')
                            ->label('Current Address')
                            ->helperText('Tole name, ahile ko naya naam'),
                        TextInput::make('applicant_phone_number')->label('Phone Number')->tel(),
                        TextInput::make('applicant_email')->label('Email')->email(),
                        TextInput::make('applicant_grandfather_name')->label('Grandfather\'s Name'),
                        TextInput::make('applicant_father_name')->label('Father\'s Name'),
                        TextInput::make('applicant_mother_name')->label('Mother\'s Name'),
                        TextInput::make('applicant_wife_name')->label('Wife\'s Name (if married)'),
                        TextInput::make('applicant_occupation')->label('Occupation'),
                        TextInput::make('applicant_occupation_office_name')->label('Occupation Office Name'),
                        TextInput::make('applicant_occupation_office_address')
                            ->label('Occupation Office Address')
                            ->columnSpanFull(),
                        FileUpload::make('applicant_citizenship_front')
                            ->label('Citizenship Front')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('applicant_citizenship_back')
                            ->label('Citizenship Back')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('applicant_live_photo')
                            ->label('Live Photo')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('applicant_phone_verification')
                            ->label('Phone Number Verification')
                            ->helperText('Screenshot of balance check: NTC *922# / Ncell *9966#')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        TextInput::make('applicant_relation_with_guarantor')->label('Relation with Guarantor'),
                        TextInput::make('applicant_source_of_income')->label('Source of Income'),
                    ])->columns(2),

                Section::make('Guarantor Information')
                    ->schema([
                        TextInput::make('guarantor_full_name')
                            ->label('Full Name')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('guarantor_permanent_address')
                            ->label('Permanent Address')
                            ->helperText('Tole name, ahile ko naya naam'),
                        TextInput::make('guarantor_current_address')
                            ->label('Current Address')
                            ->helperText('Tole name, ahile ko naya naam'),
                        TextInput::make('guarantor_phone_number')->label('Phone Number')->tel(),
                        TextInput::make('guarantor_email')->label('Email')->email(),
                        TextInput::make('guarantor_grandfather_name')->label('Grandfather\'s Name'),
                        TextInput::make('guarantor_father_name')->label('Father\'s Name'),
                        TextInput::make('guarantor_mother_name')->label('Mother\'s Name'),
                        TextInput::make('guarantor_wife_name')->label('Wife\'s Name (if married)'),
                        TextInput::make('guarantor_occupation')->label('Occupation'),
                        TextInput::make('guarantor_occupation_office_name')->label('Occupation Office Name'),
                        TextInput::make('guarantor_occupation_office_address')
                            ->label('Occupation Office Address')
                            ->columnSpanFull(),
                        FileUpload::make('guarantor_citizenship_front')
                            ->label('Citizenship Front')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('guarantor_citizenship_back')
                            ->label('Citizenship Back')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('guarantor_live_photo')
                            ->label('Live Photo')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        FileUpload::make('guarantor_phone_verification')
                            ->label('Phone Number Verification')
                            ->helperText('Screenshot of balance check: NTC *922# / Ncell *9966#')
                            ->image()
                            ->disk('public')
                            ->directory('emi-documents')
                            ->downloadable()
                            ->openable(),
                        TextInput::make('guarantor_relation')->label('Relation'),
                        TextInput::make('guarantor_source_of_income')->label('Source of Income'),
                    ])->columns(2),

                Section::make('Reference')
                    ->schema([
                        TextInput::make('reference_full_name')->label('Full Name'),
                        TextInput::make('reference_address')->label('Address'),
                        TextInput::make('reference_phone_number')->label('Phone Number')->tel(),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('applicant_full_name')->label('Applicant')->searchable(),
                Tables\Columns\TextColumn::make('applicant_phone_number')->label('Applicant Phone')->searchable(),
                Tables\Columns\TextColumn::make('guarantor_full_name')->label('Guarantor')->searchable(),
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