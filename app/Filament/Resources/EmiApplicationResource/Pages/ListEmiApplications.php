<?php

namespace App\Filament\Resources\EmiApplicationResource\Pages;

use App\Filament\Resources\EmiApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmiApplications extends ListRecords
{
    protected static string $resource = EmiApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
