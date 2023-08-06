<?php

namespace Dashed\DashedEcommerceKeendelivery\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dashed\DashedEcommerceKeendelivery\DashedEcommerceKeendelivery
 */
class DashedEcommerceKeendelivery extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashed-ecommerce-keendelivery';
    }
}
