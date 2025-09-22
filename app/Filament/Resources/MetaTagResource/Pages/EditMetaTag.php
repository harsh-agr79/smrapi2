<?php

namespace App\Filament\Resources\MetaTagResource\Pages;

use App\Filament\Resources\MetaTagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMetaTag extends EditRecord
{
    protected static string $resource = MetaTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
