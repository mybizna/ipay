<?php

namespace Modules\Ipay\Filament\Resources\IpayResource\Pages;

use Modules\Ipay\Filament\Resources\IpayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIpays extends ListRecords
{
    protected static string $resource = IpayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
