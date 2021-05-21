<?php

namespace Sevming\UnionPay\Events;

class Requested extends Event
{
    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var array
     */
    public $result;

    /**
     * Constructor.
     *
     * @param string $gateway
     * @param string $endpoint
     * @param array  $result
     */
    public function __construct(string $gateway, string $endpoint, array $result)
    {
        $this->endpoint = $endpoint;
        $this->result = $result;
        parent::__construct($gateway);
    }
}
