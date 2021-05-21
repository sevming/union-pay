<?php

namespace Sevming\UnionPay\Events;

class MethodCalled extends Event
{
    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var array
     */
    public $payload;

    /**
     * Constructor.
     *
     * @param string $gateway
     * @param string $endpoint
     * @param array  $payload
     */
    public function __construct(string $gateway, string $endpoint, array $payload = [])
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;
        parent::__construct($gateway);
    }
}
