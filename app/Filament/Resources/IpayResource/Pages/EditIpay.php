<?php

namespace Modules\Ipay\Filament\Resources\IpayResource\Pages;

use Modules\Ipay\Filament\Resources\IpayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIpay extends EditRecord
{
    protected static string $resource = IpayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
