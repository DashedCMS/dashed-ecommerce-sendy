<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Filament\Pages\Settings;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceKeendelivery\Classes\KeenDelivery;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryShippingMethod;

class KeendeliverySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'KeenDelivery';

    protected static string $view = 'qcommerce-core::settings.pages.default-settings';

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["keen_delivery_api_key_{$site['id']}"] = Customsetting::get('keen_delivery_api_key', $site['id']);
            $formData["keen_delivery_connected_{$site['id']}"] = Customsetting::get('keen_delivery_connected', $site['id'], 0) ? true : false;

            foreach (KeendeliveryShippingMethod::get() as $shippingMethod) {
                $formData["shipping_method_{$shippingMethod->id}_enabled"] = $shippingMethod->enabled;
                foreach ($shippingMethod->keenDeliveryShippingMethodServices as $service) {
                    $formData["shipping_method_service_{$service->id}_enabled"] = $service->enabled;
                    foreach ($service->keendeliveryShippingMethodServiceOptions as $option) {
                        $formData["shipping_method_service_option_{$option->id}_default"] = $option->default;
                    }
                }
            }
        }
        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $schema = [
                Placeholder::make('label')
                    ->label("KeenDelivery voor {$site['name']}")
                    ->content('Activeer KeenDelivery.')
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                Placeholder::make('label')
                    ->label("KeenDelivery is " . (! Customsetting::get('keen_delivery_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->content(Customsetting::get('keendelivery_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("keen_delivery_api_key_{$site['id']}")
                    ->label('KeenDelivery API key')
                    ->rules([
                        'max:255',
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
            ];

            foreach (KeendeliveryShippingMethod::get() as $shippingMethod) {
                $schema[] = Toggle::make("shipping_method_{$shippingMethod->id}_enabled")
                    ->reactive();
            }

            foreach (KeendeliveryShippingMethod::get() as $shippingMethod) {
                foreach ($shippingMethod->keenDeliveryShippingMethodServices as $service) {
                    $serviceSchema = [];

                    $serviceSchema[] = Toggle::make("shipping_method_service_{$service->id}_enabled")
                        ->reactive();

                    $optionsSchema = [];
                    foreach ($service->keendeliveryShippingMethodServiceOptions as $option) {
                        if ($option->type == 'textbox') {
                            $optionsSchema[] = TextInput::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name)
                                ->rules([
                                    'max:255',
                                ]);
                        } elseif ($option->type == 'checkbox') {
                            $optionsSchema[] = Toggle::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name);
                        } elseif ($option->type == 'email') {
                            $optionsSchema[] = TextInput::make("shipping_method_service_option_{$option->id}_default")
                                ->type('email')
                                ->label($option->name)
                                ->rules([
                                    'max:255',
                                    'email',
                                ]);
                        } elseif ($option->type == 'date') {
                            $optionsSchema[] = DatePicker::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name);
                        } elseif ($option->type == 'selectbox') {
                            $choices = [];
                            foreach ($option->choices as $choice) {
                                $choices[$choice['value']] = $choice['text'];
                            }
                            $optionsSchema[] = Select::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name)
                                ->options($choices);
                        } else {
                            dump('Contacteer Qubiqx om dit in te bouwen');
                        }
                    }

                    $serviceSchema[] = Card::make()
                        ->schema($optionsSchema)
                        ->hidden(fn ($get) => ! $get("shipping_method_service_{$service->id}_enabled"));

                    $schema[] = Section::make($service->name)
                        ->label($service->name)
                        ->schema($serviceSchema)
                        ->hidden(fn ($get) => ! $get("shipping_method_{$shippingMethod->id}_enabled"));
                }
            }

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($schema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $tabGroups;
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('keen_delivery_api_key', $this->form->getState()["keen_delivery_api_key_{$site['id']}"], $site['id']);
            Customsetting::set('keen_delivery_connected', KeenDelivery::isConnected($site['id']), $site['id']);

            foreach (KeendeliveryShippingMethod::get() as $shippingMethod) {
                if (isset($this->form->getState()["shipping_method_{$shippingMethod->id}_enabled"])) {
                    $shippingMethod->enabled = $this->form->getState()["shipping_method_{$shippingMethod->id}_enabled"];
                    $shippingMethod->save();
                }

                foreach ($shippingMethod->keenDeliveryShippingMethodServices as $service) {
                    if (isset($this->form->getState()["shipping_method_service_{$service->id}_enabled"])) {
                        $service->enabled = $this->form->getState()["shipping_method_service_{$service->id}_enabled"];
                        $service->save();
                    }

                    foreach ($service->keendeliveryShippingMethodServiceOptions as $option) {
                        if (isset($this->form->getState()["shipping_method_service_option_{$option->id}_default"])) {
                            $option->default = $this->form->getState()["shipping_method_service_option_{$option->id}_default"];
                            $option->save();
                        }
                    }
                }
            }

            if (Customsetting::get('keen_delivery_connected', $site['id'], 0)) {
                KeenDelivery::syncShippingMethods($site['id']);
            }
        }

        $this->notify('success', 'De KeenDelivery instellingen zijn opgeslagen');

        return redirect(KeendeliverySettingsPage::getUrl());
    }
}
