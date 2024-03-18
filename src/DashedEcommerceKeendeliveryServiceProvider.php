<?php

namespace Dashed\DashedEcommerceKeendelivery;

use Dashed\DashedEcommerceCore\Models\Order;
use Dashed\DashedEcommerceKeendelivery\Filament\Pages\Settings\KeendeliverySettingsPage;
use Dashed\DashedEcommerceKeendelivery\Livewire\Orders\ShowKeendeliveryOrders;
use Dashed\DashedEcommerceKeendelivery\Livewire\Orders\ShowPushToKeendeliveryOrder;
use Dashed\DashedEcommerceKeendelivery\Models\KeendeliveryOrder;
use Filament\Actions\Action;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DashedEcommerceKeendeliveryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-ecommerce-keendelivery';

    public function bootingPackage()
    {
        Livewire::component('show-push-to-keendelivery-order', ShowPushToKeendeliveryOrder::class);
        Livewire::component('show-keendelivery-orders', ShowKeendeliveryOrders::class);

        Order::addDynamicRelation('keendeliveryOrders', function (Order $model) {
            return $model->hasMany(KeendeliveryOrder::class);
        });

        if (! app()->runningInConsole()) {
            if (KeendeliveryOrder::where('label_printed', 0)->count()) {
                ecommerce()->buttonActions(
                    'orders',
                    array_merge(ecommerce()->buttonActions('orders'), [
                        Action::make('downloadKeendeliveryLabels')
                            ->button()
                            ->label('Download KeenDelivery Labels')
                            ->url(url(config('filament.path') . '/keendelivery/download-labels'))
                            ->openUrlInNewTab(),
                    ])
                );
            }
        }
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $package
            ->name('dashed-ecommerce-keendelivery')
            ->hasRoutes([
                'keendeliveryRoutes',
            ])
            ->hasViews();

        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'keendelivery' => [
                    'name' => 'KeenDelivery',
                    'description' => 'Koppel KeenDelivery',
                    'icon' => 'archive-box',
                    'page' => KeendeliverySettingsPage::class,
                ],
            ])
        );

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-push-to-keendelivery-order' => [
                    'name' => 'show-push-to-keendelivery-order',
                    'width' => 'sidebar',
                ],
                'show-keendelivery-orders' => [
                    'name' => 'show-keendelivery-orders',
                    'width' => 'sidebar',
                ],
            ])
        );
    }
}
