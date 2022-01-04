<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Models;

use Illuminate\Database\Eloquent\Model;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Spatie\Activitylog\Traits\LogsActivity;

class KeendeliveryShippingMethodServiceOption extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__keendelivery_shipping_method_service_options';

    protected $fillable = [
        'keendelivery_shipping_method_service_id',
        'name',
        'field',
        'type',
        'mandatory',
        'choices',
        'default'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'mandatory' => 'boolean',
        'choices' => 'array',
    ];

    public function keendeliveryShippingMethodService()
    {
        return $this->belongsTo(KeendeliveryShippingMethodService::class);
    }
}
