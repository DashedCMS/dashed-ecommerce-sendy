<?php

namespace Dashed\DashedEcommerceSendy\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedTranslations\Models\Translation;
use Dashed\DashedEcommerceSendy\Models\SendyOrder;

class TrackandTraceMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(SendyOrder $sendyOrder)
    {
        $this->sendyOrder = $sendyOrder;
    }

    public function build()
    {
        return $this->view('dashed-ecommerce-sendy::emails.track-and-trace')->from(Customsetting::get('site_from_email'), Customsetting::get('company_name'))->subject(Translation::get('order-sendy-track-and-trace-email-subject', 'sendy', 'Your order #:orderId: has been updated', 'text', [
            'orderId' => $this->sendyOrder->order->invoice_id,
        ]))->with([
            'order' => $this->sendyOrder->order,
            'sendyOrder' => $this->sendyOrder,
        ]);
    }
}
