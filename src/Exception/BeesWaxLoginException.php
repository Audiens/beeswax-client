<?php

namespace Audiens\BeesWax\Exception;

use Audiens\BeesWax\BeesWaxResponse;
use Throwable;

class BeesWaxLoginException extends BeesWaxGenericException
{
    /** @var BeesWaxResponse */
    protected $response;

    public function __construct(BeesWaxResponse $response, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, static::CODE_INVALID_LOGIN, $previous);

        $this->response = $response;
    }
}
