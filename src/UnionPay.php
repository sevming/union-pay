<?php

namespace Sevming\UnionPay;

use Sevming\Foundation\Foundation;

/**
 * Class UnionPay
 *
 * @property \Sevming\UnionPay\Providers\Web\Web $web
 */
class UnionPay extends Foundation
{
    protected $providers = [
        Providers\Web\ServiceProvider::class
    ];
}
