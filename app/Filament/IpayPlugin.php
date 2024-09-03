<?php

namespace Modules\Ipay\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class IpayPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Ipay';
    }

    public function getId(): string
    {
        return 'ipay';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
