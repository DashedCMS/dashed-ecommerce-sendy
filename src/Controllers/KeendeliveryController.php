<?php

namespace Dashed\DashedEcommerceKeendelivery\Controllers;

use Illuminate\Support\Facades\Storage;
use Dashed\DashedEcommerceKeendelivery\Classes\KeenDelivery;
use Dashed\DashedCore\Controllers\Frontend\FrontendController;
use Dashed\DashedEcommerceKeendelivery\Models\KeendeliveryOrder;

class KeendeliveryController extends FrontendController
{
    public function downloadLabels()
    {
        $keendeliveryOrders = KeendeliveryOrder::where('label_printed', 0)->get();

        $response = KeenDelivery::getLabelsFromShipments($keendeliveryOrders->pluck('shipment_id')->toArray());
        if (isset($response['labels'])) {
            $fileName = '/dashed/keendelivery/labels/labels-' . time() . '.pdf';
            Storage::disk('dashed')->put($fileName, base64_decode($response['labels']));
            foreach ($keendeliveryOrders as $keendeliveryOrder) {
                $keendeliveryOrder->label_printed = 1;
                $keendeliveryOrder->save();
            }

            return Storage::disk('dashed')->download($fileName);
        } else {
            echo "<script>window.close();</script>";
        }
    }
}
