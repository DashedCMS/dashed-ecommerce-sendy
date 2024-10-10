<?php

namespace Dashed\DashedEcommerceSendy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dashed\DashedEcommerceSendy\DashedEcommerceSendy
 */
class DashedEcommerceSendy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashed-ecommerce-sendy';
    }
}
