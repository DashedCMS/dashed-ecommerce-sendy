<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Models;

use Illuminate\Database\Eloquent\Model;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Spatie\Activitylog\Traits\LogsActivity;

class KeendeliveryOrder extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__order_keendelivery';

    protected $fillable = [
        'order_id',
        'shipment_id',
        'label',
        'label_url',
        'track_and_trace',
        'label_printed',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'track_and_trace' => 'array',
        'label_printed' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
