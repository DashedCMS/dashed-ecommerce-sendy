<?php

namespace Dashed\DashedEcommerceKeendelivery;

use Dashed\DashedEcommerceKeendelivery\Filament\Pages\Settings\KeendeliverySettingsPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

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
