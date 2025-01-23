<?php

namespace Modules\Ipay\Filament\Resources;

use Modules\Base\Filament\Resources\BaseResource;
use Modules\Ipay\Models\Ipay;

class IpayResource extends BaseResource
{
    protected static ?string $model = Ipay::class;

    protected static ?string $slug = 'ipay/ipay';

    protected static ?string $navigationGroup = 'Account';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationParentItem = 'Gateway';

}
