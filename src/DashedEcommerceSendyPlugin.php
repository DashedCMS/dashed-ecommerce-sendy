<?php

namespace Dashed\DashedEcommerceSendy;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedEcommerceSendy\Filament\Pages\Settings\SendySettingsPage;

class DashedEcommerceSendyPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-ecommerce-sendy';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                SendySettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
