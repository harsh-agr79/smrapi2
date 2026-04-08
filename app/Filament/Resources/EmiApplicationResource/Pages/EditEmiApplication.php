<?php

namespace App\Filament\Resources\EmiApplicationResource\Pages;

use App\Filament\Resources\EmiApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmiApplication extends EditRecord
{
    protected static string $resource = EmiApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
