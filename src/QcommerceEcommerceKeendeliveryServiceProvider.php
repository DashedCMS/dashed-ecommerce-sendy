<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Qubiqx\QcommerceEcommerceKeendelivery\Filament\Pages\Settings\KeendeliverySettingsPage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QcommerceEcommerceKeendeliveryServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-keendelivery';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
//            $schedule->command(SyncOrdersFromChannableCommand::class)->everyFiveMinutes();
//            $schedule->command(SyncStockFromChannableCommand::class)->everyFiveMinutes();
        });

//        Order::addDynamicRelation('channableOrder', function (Order $model) {
//            return $model->hasOne(ChannableOrder::class);
//        });
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'keendelivery' => [
                    'name' => 'KeenDelivery',
                    'description' => 'Koppel KeenDelivery',
                    'icon' => 'archive',
                    'page' => KeendeliverySettingsPage::class,
                ],
            ])
        );

        $package
            ->name('qcommerce-ecommerce-channable')
            ->hasViews()
            ->hasCommands([
//                SyncOrdersFromChannableCommand::class,
//                SyncStockFromChannableCommand::class,
            ]);
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            KeendeliverySettingsPage::class,
        ]);
    }
}
