<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class KeendeliveryShippingMethodService extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__keendelivery_shipping_method_services';

    protected $fillable = [
        'keendelivery_shipping_method_id',
        'name',
        'value',
        'enabled',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function keendeliveryShippingMethod()
    {
        return $this->belongsTo(KeendeliveryShippingMethod::class);
    }

    public function KeendeliveryShippingMethodServiceOptions()
    {
        return $this->hasMany(KeendeliveryShippingMethodServiceOption::class);
    }
}
