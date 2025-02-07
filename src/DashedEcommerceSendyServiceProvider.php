<?php

namespace Dashed\DashedEcommerceSendy;

use Livewire\Livewire;
use Filament\Actions\Action;
use Spatie\LaravelPackageTools\Package;
use Dashed\DashedEcommerceCore\Models\Order;
use Dashed\DashedEcommerceSendy\Models\SendyOrder;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedEcommerceSendy\Livewire\Orders\ShowSendyOrders;
use Dashed\DashedEcommerceSendy\Livewire\Orders\ShowPushToSendyOrder;
use Dashed\DashedEcommerceSendy\Filament\Pages\Settings\SendySettingsPage;

class DashedEcommerceSendyServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-ecommerce-sendy';

    public function bootingPackage()
    {
        Livewire::component('show-push-to-sendy-order', ShowPushToSendyOrder::class);
        Livewire::component('show-sendy-orders', ShowSendyOrders::class);

        Order::addDynamicRelation('sendyOrders', function (Order $model) {
            return $model->hasMany(SendyOrder::class);
        });

        if (! app()->runningInConsole()) {
            if (SendyOrder::where('label_printed', 0)->count()) {
                ecommerce()->buttonActions(
                    'orders',
                    array_merge(ecommerce()->buttonActions('orders'), [
                        Action::make('downloadSendyLabels')
                            ->button()
                            ->label('Download Sendy Labels')
                            ->url(url(config('filament.path', 'dashed') . '/sendy/download-labels'))
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
            ->name('dashed-ecommerce-sendy')
            ->hasRoutes([
                'SendyRoutes',
            ])
            ->hasViews();

        cms()->registerSettingsPage(SendySettingsPage::class, 'Sendy', 'archive-box', 'Koppel Sendy');

        ecommerce()->widgets(
            'orders',
            array_merge(ecommerce()->widgets('orders'), [
                'show-push-to-sendy-order' => [
                    'name' => 'show-push-to-sendy-order',
                    'width' => 'sidebar',
                ],
                'show-sendy-orders' => [
                    'name' => 'show-sendy-orders',
                    'width' => 'sidebar',
                ],
            ])
        );

        cms()->builder('plugins', [
            new DashedEcommerceSendyPlugin(),
        ]);
    }
}
