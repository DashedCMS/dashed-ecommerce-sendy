<?php

namespace Dashed\DashedEcommerceKeendelivery;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedEcommerceKeendelivery\Filament\Pages\Settings\KeendeliverySettingsPage;

class DashedEcommerceKeendeliveryPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-ecommerce-keendelivery';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                KeendeliverySettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
