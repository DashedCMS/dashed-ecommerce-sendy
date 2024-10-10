<?php

namespace Dashed\DashedEcommerceSendy\Controllers;

use Illuminate\Support\Facades\Storage;
use Dashed\DashedEcommerceSendy\Classes\Sendy;
use Dashed\DashedCore\Controllers\Frontend\FrontendController;
use Dashed\DashedEcommerceSendy\Models\SendyOrder;

class SendyController extends FrontendController
{
    public function downloadLabels()
    {
        $sendyOrders = SendyOrder::where('label_printed', 0)->get();

        $response = Sendy::getLabelsFromShipments($sendyOrders->pluck('shipment_id')->toArray());
        if (isset($response['labels'])) {
            $fileName = '/dashed/sendy/labels/labels-' . time() . '.pdf';
            Storage::disk('dashed')->put($fileName, base64_decode($response['labels']));
            foreach ($sendyOrders as $sendyOrder) {
                $sendyOrder->label_printed = 1;
                $sendyOrder->save();
            }

            return Storage::disk('dashed')->download($fileName);
        } else {
            echo "<script>window.close();</script>";
        }
    }
}
