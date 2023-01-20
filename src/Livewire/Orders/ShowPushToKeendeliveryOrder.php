<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Livewire\Orders;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Qubiqx\QcommerceEcommerceCore\Models\OrderLog;
use Qubiqx\QcommerceEcommerceKeendelivery\Classes\KeenDelivery;
use Qubiqx\QcommerceEcommerceKeendelivery\Mail\TrackandTraceMail;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryOrder;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryShippingMethod;

class ShowPushToKeendeliveryOrder extends Component implements HasForms
{
    use InteractsWithForms;

    public $order;
    public $data;

    public function mount($order)
    {
        $this->order = $order;

        $shippingMethods = KeendeliveryShippingMethod::where('enabled', 1)->where('site_id', $this->order->site_id)->get();
        foreach ($shippingMethods as $shippingMethod) {
            $services = $shippingMethod->keendeliveryShippingMethodServices()->where('enabled', 1)->get();
            foreach ($services as $service) {
                foreach ($service->keendeliveryShippingMethodServiceOptions as $option) {
                    $this->data["shipping_method_service_option_$option->field"] = $option->default ?: null;
                }
            }
        }
    }

    public function render()
    {
        return view('qcommerce-ecommerce-keendelivery::orders.components.show-push-to-keendelivery-order');
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        $shippingMethods = KeendeliveryShippingMethod::where('enabled', 1)->where('site_id', $this->order->site_id)->get();

        $schema = [];
        $schema[] = Select::make('shipping_method')
            ->label('Kies een verzendmethode')
            ->required()
            ->reactive()
            ->options($shippingMethods->pluck('name', 'value'));

        foreach ($shippingMethods as $shippingMethod) {
            $services = $shippingMethod->keendeliveryShippingMethodServices()->where('enabled', 1)->get();
            $schema[] = Select::make('service')
                ->label('Kies een service')
                ->required()
                ->reactive()
                ->options($services->pluck('name', 'value'))
                ->hidden(fn (\Closure $get) => $get("shipping_method") != $shippingMethod->value);

            foreach ($services as $service) {
                foreach ($service->keendeliveryShippingMethodServiceOptions as $option) {
                    if ($option->type == 'textbox') {
                        $schema[] = TextInput::make("shipping_method_service_option_{$option->field}")
                            ->label($option->name)
                            ->rules([
                                'max:255',
                            ])
                            ->required($option->mandatory)
                            ->hidden(fn (\Closure $get) => $get("service") != $service->value);
                    } elseif ($option->type == 'checkbox') {
                        $schema[] = Toggle::make("shipping_method_service_option_{$option->field}")
                            ->label($option->name)
                            ->required($option->mandatory)
                            ->hidden(fn (\Closure $get) => $get("service") != $service->value);
                    } elseif ($option->type == 'email') {
                        $schema[] = TextInput::make("shipping_method_service_option_{$option->field}")
                            ->type('email')
                            ->label($option->name)
                            ->required($option->mandatory)
                            ->rules([
                                'max:255',
                                'email',
                            ])
                            ->hidden(fn (\Closure $get) => $get("service") != $service->value);
                    } elseif ($option->type == 'date') {
                        $schema[] = DatePicker::make("shipping_method_service_option_{$option->field}")
                            ->label($option->name)
                            ->required($option->mandatory)
                            ->hidden(fn (\Closure $get) => $get("service") != $service->value);
                    } elseif ($option->type == 'selectbox') {
                        $choices = [];
                        foreach ($option->choices as $choice) {
                            $choices[$choice['value']] = $choice['text'];
                        }
                        $schema[] = Select::make("shipping_method_service_option_{$option->field}")
                            ->label($option->name)
                            ->options($choices)
                            ->required($option->mandatory)
                            ->hidden(fn (\Closure $get) => $get("service") != $service->value);
                    } else {
                        dump('Contacteer Qubiqx om dit in te bouwen');
                    }
                }
            }
        }

        return [
            Section::make('Verzenden via KeenDelivery')
                ->schema($schema),
        ];
    }

    public function submit()
    {
        $this->validate();

        $response = KeenDelivery::createShipment($this->order, $this->data);
        if (isset($response['shipment_id'])) {
            $keendeliveryOrder = new KeendeliveryOrder();
            $keendeliveryOrder->order_id = $this->order->id;
            $keendeliveryOrder->shipment_id = $response['shipment_id'];
            $keendeliveryOrder->label = $response['label'];
            Storage::put('/qcommerce/orders/keendelivery/labels/label-' . $this->order->invoice_id . '.pdf', base64_decode($response['label']));
            $keendeliveryOrder->label_url = '/keendelivery/labels/label-' . $this->order->invoice_id . '.pdf';
            $keendeliveryOrder->track_and_trace = $response['track_and_trace'];
            $keendeliveryOrder->save();

            $orderLog = new OrderLog();
            $orderLog->order_id = $this->order->id;
            $orderLog->user_id = Auth::user()->id;
            $orderLog->tag = 'order.pushed-to-keendelivery';
            $orderLog->save();

//            try {
            Mail::to($this->order->email)->send(new TrackandTraceMail($keendeliveryOrder));

            $orderLog = new OrderLog();
            $orderLog->order_id = $this->order->id;
            $orderLog->user_id = Auth::user()->id;
            $orderLog->tag = 'order.t&t.send';
            $orderLog->save();
//            } catch (\Exception $e) {
//                $orderLog = new OrderLog();
//                $orderLog->order_id = $this->order->id;
//                $orderLog->user_id = Auth::user()->id;
//                $orderLog->tag = 'order.t&t.not-send';
//                $orderLog->save();
//            }


            $this->emit('refreshPage');
            $this->emit('notify', [
                'status' => 'success',
                'message' => 'De bestelling wordt binnen enkele minuten naar KeenDelivery gepushed.',
            ]);
        } else {
            foreach ($response as $error) {
                if (is_array($error)) {
                    foreach ($error as $errorItem) {
                        $this->emit('notify', [
                            'status' => 'danger',
                            'message' => $errorItem,
                        ]);
                    }
                } else {
                    $this->emit('notify', [
                        'status' => 'danger',
                        'message' => $error,
                    ]);
                }
            }
        }
    }
}
