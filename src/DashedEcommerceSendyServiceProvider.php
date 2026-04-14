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

        if (cms()->isCMSRoute() && SendyOrder::where('label_printed', 0)->count()) {
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

        cms()->registerSettingsDocs(
            page: \Dashed\DashedEcommerceSendy\Filament\Pages\Settings\SendySettingsPage::class,
            title: 'Sendy instellingen',
            intro: 'Op deze pagina koppel je jouw webshop aan Sendy, een verzendplatform waarmee je automatisch verzendlabels kunt aanmaken voor je bestellingen. Let op: dit is het Nederlandse verzendplatform Sendy en niet de open source e-mail marketing tool met dezelfde naam. Werk je met meerdere sites? Dan kun je per site een eigen Sendy account koppelen.',
            sections: [
                [
                    'heading' => 'Wat kun je hier instellen?',
                    'body' => <<<MARKDOWN
Op deze pagina regel je twee dingen:

- De API key waarmee jouw webshop verbinding maakt met je Sendy account.
- Per verzendmethode de services en service-opties die je wilt gebruiken. Deze velden verschijnen automatisch nadat de koppeling actief is.
MARKDOWN,
                ],
                [
                    'heading' => 'Hoe zet je dit op?',
                    'body' => <<<MARKDOWN
1. Log in op je Sendy account.
2. Vraag je API key op of maak een nieuwe aan.
3. Plak de API key in het veld op deze pagina en sla op.
4. Zodra de koppeling werkt, worden de beschikbare verzendmethodes opgehaald uit Sendy.
5. Per verzendmethode verschijnen extra velden waarin je de services en service-opties kunt activeren en instellen.
6. Configureer per service welke opties je wilt gebruiken en sla de instellingen op.
MARKDOWN,
                ],
                [
                    'heading' => 'Dynamische verzendvelden',
                    'body' => 'De velden onder de API key worden dynamisch opgehaald uit Sendy. Welke verzendmethodes, services en opties je ziet hangt af van wat er in jouw Sendy account beschikbaar is. Verandert er iets in je Sendy account? Dan kunnen ook de velden op deze pagina veranderen.',
                ],
            ],
            fields: [
                'API key' => 'De API key uit je Sendy account. Deze sleutel is nodig om verzendmethodes op te halen en om automatisch verzendlabels aan te maken voor je bestellingen. Behandel deze key als een wachtwoord.',
            ],
            tips: [
                'Vul eerst alleen de API key in en sla op. Daarna verschijnen de extra velden voor de verzendmethodes en kun je deze rustig instellen.',
                'Controleer of de juiste services aan staan voordat je live gaat. Alleen geactiveerde services zijn beschikbaar bij het afrekenen in je webshop.',
            ],
        );
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
