<?php

namespace Sevming\UnionPay\Events;

class SignFailed extends Event
{
    /**
     * @var array
     */
    public $data;

    /**
     * Constructor.
     *
     * @param string $gateway
     * @param array  $data
     */
    public function __construct(string $gateway, array $data)
    {
        $this->data = $data;
        parent::__construct($gateway);
    }
}
