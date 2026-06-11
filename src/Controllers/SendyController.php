<?php

namespace Dashed\DashedEcommerceSendy\Controllers;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\Storage;
use Dashed\DashedEcommerceSendy\Classes\Sendy;
use Dashed\DashedEcommerceSendy\Models\SendyOrder;

;

class SendyController extends Controller
{
    public function downloadLabels()
    {
        // Scope op de actieve site zodat een beheerder geen labels van een andere site kan trekken.
        $sendyOrders = SendyOrder::whereHas('order', fn ($q) => $q->where('site_id', Sites::getActive()))
            ->where('label_printed', 0)
            ->get();

        $response = Sendy::getLabelsFromShipments($sendyOrders->pluck('shipment_id')->toArray());
        if (isset($response['labels'])) {
            $fileName = '/dashed/sendy/labels/labels-' . Str::random(40) . '.pdf';
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
