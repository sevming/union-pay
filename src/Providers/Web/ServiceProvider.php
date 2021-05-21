<?php

namespace Sevming\UnionPay\Providers\Web;

use Pimple\{Container, ServiceProviderInterface};

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['web'] = function ($app) {
            return new Web($app);
        };
    }
}
