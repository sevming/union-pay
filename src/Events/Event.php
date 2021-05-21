<?php

namespace Sevming\UnionPay\Events;

class Event extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var string
     */
    public $gateway;

    /**
     * Constructor.
     *
     * @param string $gateway
     */
    public function __construct(string $gateway)
    {
        $this->gateway = $gateway;
    }
}
