<?php

namespace Dashed\DashedEcommerceSendy\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SendyShippingMethodService extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__sendy_shipping_method_services';

    protected $fillable = [
        'sendy_shipping_method_id',
        'name',
        'value',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function sendyShippingMethod()
    {
        return $this->belongsTo(SendyShippingMethod::class);
    }

    public function SendyShippingMethodServiceOptions()
    {
        return $this->hasMany(SendyShippingMethodServiceOption::class);
    }
}
