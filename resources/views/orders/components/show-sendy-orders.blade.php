<div class="space-y-2">
    @foreach($order->sendyOrders as $sendyOrder)
        <span
            class="bg-green-100 text-green-800 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium">
                                Bestelling naar Sendy verstuurd met ID: {{$sendyOrder->shipment_id}}
                                </span>
        @if(!$loop->last)
            <hr>
        @endif
    @endforeach
</div>
