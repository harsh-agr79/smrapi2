<?php

namespace App\Filament\Resources\HomeDiscountBannerResource\Pages;

use App\Filament\Resources\HomeDiscountBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeDiscountBanner extends EditRecord
{
    protected static string $resource = HomeDiscountBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
