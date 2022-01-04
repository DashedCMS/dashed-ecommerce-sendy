<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Qubiqx\QcommerceEcommerceKeendelivery\Models\KeendeliveryOrder;

class CreateKeendeliveryOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qcommerce__order_keendelivery', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('qcommerce__orders');
            $table->string('shipment_id');
            $table->longText('label')->nullable();
            $table->string('label_url')->nullable();
            $table->json('track_and_trace')->nullable();
            $table->boolean('label_printed')->default(0);

            $table->timestamps();
        });

        foreach (\Qubiqx\QcommerceEcommerceCore\Models\Order::whereNotNull('keen_delivery_shipment_id')->get() as $order) {
            $keendeliveryOrder = new KeendeliveryOrder();
            $keendeliveryOrder->order_id = $order->id;
            $keendeliveryOrder->shipment_id = $order->keen_delivery_shipment_id;
            $keendeliveryOrder->label = $order->keen_delivery_label;
            if (Storage::disk('qcommerce')->exists('/keendelivery/labels/label-' . $order->invoice_id . '.pdf')) {
                Storage::disk('qcommerce')->copy('/keendelivery/labels/label-' . $order->invoice_id . '.pdf', '/orders/keendelivery/labels/label-' . $order->invoice_id . '.pdf');
                $keendeliveryOrder->label_url = '/qcommerce/orders/keendelivery/labels/label-' . $order->invoice_id . '.pdf';
            }
            $keendeliveryOrder->label_printed = $order->keen_delivery_label_printed;
            $keendeliveryOrder->track_and_trace = json_decode($order->keen_delivery_track_and_trace, true);
            $keendeliveryOrder->save();
        }

        Schema::table('qcommerce__orders', function (Blueprint $table) {
            $table->dropColumn('keen_delivery_shipment_id');
            $table->dropColumn('keen_delivery_label');
            $table->dropColumn('keen_delivery_label_url');
            $table->dropColumn('keen_delivery_label_printed');
            $table->dropColumn('keen_delivery_track_and_trace');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keendelivery_order');
    }
}
