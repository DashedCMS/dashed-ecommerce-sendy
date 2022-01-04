<div class="space-y-2">
    @foreach($order->keendeliveryOrders as $keendeliveryOrder)
        <span
            class="bg-green-100 text-green-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling naar Keendelivery gepushed met shipment ID: {{$keendeliveryOrder->shipment_id}}
                                </span>
        @if(!$loop->last)
            <hr>
        @endif
    @endforeach
</div>
