<?php

namespace Dashed\DashedEcommerceKeendelivery\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedTranslations\Models\Translation;
use Dashed\DashedEcommerceKeendelivery\Models\KeendeliveryOrder;

class TrackandTraceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(KeendeliveryOrder $keendeliveryOrder)
    {
        $this->keendeliveryOrder = $keendeliveryOrder;
    }

    public function build()
    {
        return $this->view('dashed-ecommerce-keendelivery::emails.track-and-trace')->from(Customsetting::get('site_from_email'), Customsetting::get('company_name'))->subject(Translation::get('order-keendelivery-track-and-trace-email-subject', 'keendelivery', 'Your order #:orderId: has been updated', 'text', [
            'orderId' => $this->keendeliveryOrder->order->invoice_id,
        ]))->with([
            'order' => $this->keendeliveryOrder->order,
            'keendeliveryOrder' => $this->keendeliveryOrder,
        ]);
    }
}
