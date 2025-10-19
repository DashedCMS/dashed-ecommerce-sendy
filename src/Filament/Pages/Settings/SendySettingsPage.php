<?php

namespace Dashed\DashedEcommerceSendy\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Card;
use Dashed\DashedCore\Classes\Sites;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Tabs\Tab;
use Dashed\DashedCore\Models\Customsetting;
use Filament\Infolists\Components\TextEntry;
use Dashed\DashedEcommerceSendy\Classes\Sendy;
use Dashed\DashedEcommerceSendy\Models\SendyShippingMethod;

class SendySettingsPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Sendy';

    protected string $view = 'dashed-core::settings.pages.default-settings';
    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["sendy_api_key_{$site['id']}"] = Customsetting::get('sendy_api_key', $site['id']);
            $formData["sendy_connected_{$site['id']}"] = Customsetting::get('sendy_connected', $site['id'], 0) ? true : false;

            foreach (SendyShippingMethod::get() as $shippingMethod) {
                $formData["shipping_method_{$shippingMethod->id}_enabled"] = $shippingMethod->enabled;
                foreach ($shippingMethod->sendyShippingMethodServices as $service) {
                    $formData["shipping_method_service_{$service->id}_enabled"] = $service->enabled;
                    foreach ($service->sendyShippingMethodServiceOptions as $option) {
                        $formData["shipping_method_service_option_{$option->id}_default"] = $option->default;
                    }
                }
            }
        }
        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $newSchema = [
                TextEntry::make("Sendy voor {$site['name']}")
                    ->state('Activeer Sendy.')
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextEntry::make("Sendy is " . (! Customsetting::get('sendy_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->state(Customsetting::get('sendy_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("sendy_api_key_{$site['id']}")
                    ->label('Sendy API key')
                    ->maxLength(255)
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
            ];

            foreach (SendyShippingMethod::get() as $shippingMethod) {
                $newSchema[] = Toggle::make("shipping_method_{$shippingMethod->id}_enabled")
                    ->label("Verzendmethod {$shippingMethod->name} activeren")
                    ->reactive();
            }

            foreach (SendyShippingMethod::get() as $shippingMethod) {
                foreach ($shippingMethod->sendyShippingMethodServices as $service) {
                    $serviceSchema = [];

                    $serviceSchema[] = Toggle::make("shipping_method_service_{$service->id}_enabled")
                        ->reactive();

                    $optionsSchema = [];
                    foreach ($service->sendyShippingMethodServiceOptions as $option) {
                        if ($option->type == 'textbox') {
                            $optionsSchema[] = TextInput::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name)
                                ->maxLength(255);
                        } elseif ($option->type == 'checkbox') {
                            $optionsSchema[] = Toggle::make("shipping_method_service_option_{$option->id}_default")
                                ->label($option->name);
                        } elseif ($option->type == 'email') {
                            $optionsSchema[] = TextInput::make("shipping_method_service_option_{$option->id}_default")
                                ->type('email')
                                ->label($option->name)
                                ->email()
                                ->maxLength(255);
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
                            dump('Contacteer je beheerder om dit in te bouwen');
                        }
                    }

                    $serviceSchema[] = Card::make()
                        ->schema($optionsSchema)
                        ->hidden(fn ($get) => ! $get("shipping_method_service_{$service->id}_enabled"));

                    $schema[] = Section::make($service->name)->columnSpanFull()
                        ->label($service->name)
                        ->schema($serviceSchema)
                        ->hidden(fn ($get) => ! $get("shipping_method_{$shippingMethod->id}_enabled"));
                }
            }

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($newSchema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $schema->schema($tabGroups)
            ->statePath('data');
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('sendy_api_key', $this->form->getState()["sendy_api_key_{$site['id']}"], $site['id']);
            Customsetting::set('sendy_connected', Sendy::isConnected($site['id']), $site['id']);

            foreach (SendyShippingMethod::get() as $shippingMethod) {
                if (isset($this->form->getState()["shipping_method_{$shippingMethod->id}_enabled"])) {
                    $shippingMethod->enabled = $this->form->getState()["shipping_method_{$shippingMethod->id}_enabled"];
                    $shippingMethod->save();
                }

                foreach ($shippingMethod->sendyShippingMethodServices as $service) {
                    if (isset($this->form->getState()["shipping_method_service_{$service->id}_enabled"])) {
                        $service->enabled = $this->form->getState()["shipping_method_service_{$service->id}_enabled"];
                        $service->save();
                    }

                    foreach ($service->sendyShippingMethodServiceOptions as $option) {
                        if (isset($this->form->getState()["shipping_method_service_option_{$option->id}_default"])) {
                            $option->default = $this->form->getState()["shipping_method_service_option_{$option->id}_default"];
                            $option->save();
                        }
                    }
                }
            }

            if (Customsetting::get('sendy_connected', $site['id'], 0)) {
                Sendy::syncShippingMethods($site['id']);
            }
        }

        Notification::make()
            ->title('De Sendy instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(SendySettingsPage::getUrl());
    }
}
