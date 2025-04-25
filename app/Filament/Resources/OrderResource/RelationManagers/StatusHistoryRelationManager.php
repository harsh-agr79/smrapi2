<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Section, Textarea, Grid};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('order_id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_id')
            ->paginated(false) 
            ->columns([
                TextColumn::make('status')->label('Status')->sortable(),
                TextColumn::make('changed_at')->label('Changed At')->dateTime(),
                TextColumn::make('user.name')->label('Changed By')->sortable(),
            ])
            ->defaultSort('changed_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
