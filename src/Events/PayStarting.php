<?php

namespace Sevming\UnionPay\Events;

class PayStarting extends Event
{
    /**
     * @var array
     */
    public $params;

    /**
     * Constructor.
     *
     * @param string $gateway
     * @param array  $params
     */
    public function __construct(string $gateway, array $params)
    {
        $this->params = $params;
        parent::__construct($gateway);
    }
}
