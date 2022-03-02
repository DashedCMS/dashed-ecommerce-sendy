<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Controllers;

use Illuminate\Support\Facades\Storage;
use Qubiqx\QcommerceEcommerceKeendelivery\Classes\KeenDelivery;
use Qubiqx\QcommerceCore\Controllers\Frontend\FrontendController;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryOrder;

class KeendeliveryController extends FrontendController
{
    public function downloadLabels()
    {
        $keendeliveryOrders = KeendeliveryOrder::where('label_printed', 0)->get();

        $response = KeenDelivery::getLabelsFromShipments($keendeliveryOrders->pluck('shipment_id'));
        if (isset($response['labels'])) {
            $fileName = '/qcommerce/keendelivery/labels/labels-' . time() . '.pdf';
            Storage::put($fileName, base64_decode($response['labels']));
            foreach ($keendeliveryOrders as $keendeliveryOrder) {
                $keendeliveryOrder->label_printed = 1;
                $keendeliveryOrder->save();
            }

            return Storage::download($fileName);
        } else {
            echo "<script>window.close();</script>";
        }
    }
}
