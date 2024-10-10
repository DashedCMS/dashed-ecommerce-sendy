<?php

namespace Dashed\DashedEcommerceSendy\Livewire\Orders;

use Livewire\Component;

class ShowSendyOrders extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function render()
    {
        return view('dashed-ecommerce-sendy::orders.components.show-sendy-orders');
    }
}
