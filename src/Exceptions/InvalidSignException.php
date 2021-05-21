<?php

namespace Sevming\UnionPay\Exceptions;

use Sevming\Foundation\Exceptions\Exception;

class InvalidSignException extends Exception
{
    /**
     * @var array|string
     */
    protected $raw;

    /**
     * Constructor.
     *
     * @param string       $message
     * @param array|string $raw
     */
    public function __construct(string $message, $raw)
    {
        $this->raw = $raw;
        parent::__construct($message);
    }
}
