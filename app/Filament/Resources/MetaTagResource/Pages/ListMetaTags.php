<?php

namespace App\Filament\Resources\MetaTagResource\Pages;

use App\Filament\Resources\MetaTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMetaTags extends ListRecords
{
    protected static string $resource = MetaTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
