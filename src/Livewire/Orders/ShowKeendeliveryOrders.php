<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Livewire\Orders;

use Livewire\Component;

class ShowKeendeliveryOrders extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('qcommerce-ecommerce-keendelivery::orders.components.show-keendelivery-orders');
    }
}
