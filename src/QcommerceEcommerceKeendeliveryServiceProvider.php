<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery;

use Filament\Pages\Actions\ButtonAction;
use Filament\PluginServiceProvider;
use Livewire\Livewire;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Qubiqx\QcommerceEcommerceKeendelivery\Filament\Pages\Settings\KeendeliverySettingsPage;
use Qubiqx\QcommerceEcommerceKeendelivery\Livewire\Orders\ShowKeendeliveryOrders;
use Qubiqx\QcommerceEcommerceKeendelivery\Livewire\Orders\ShowPushToKeendeliveryOrder;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryOrder;
use Spatie\LaravelPackageTools\Package;

class QcommerceEcommerceKeendeliveryServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-keendelivery';

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
                        ButtonAction::make('downloadKeendeliveryLabels')
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
            ->name('qcommerce-ecommerce-keendelivery')
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
                    'icon' => 'archive',
                    'page' => KeendeliverySettingsPage::class,
                ],
            ])
        );

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-push-to-keendelivery-order' => [
                    'name' => 'show-push-to-keendelivery-order',
                    'width' => 'full',
                ],
                'show-keendelivery-orders' => [
                    'name' => 'show-keendelivery-orders',
                    'width' => 'sidebar',
                ],
            ])
        );
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            KeendeliverySettingsPage::class,
        ]);
    }
}
