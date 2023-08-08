<?php

namespace Dashed\DashedEcommerceKeendelivery\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KeendeliveryShippingMethod extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__keendelivery_shipping_methods';

    protected $fillable = [
        'name',
        'value',
        'site_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function KeendeliveryShippingMethodServices()
    {
        return $this->hasMany(KeendeliveryShippingMethodService::class);
    }
}
