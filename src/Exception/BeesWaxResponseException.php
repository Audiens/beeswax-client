<?php
declare(strict_types=1);

namespace Audiens\BeesWax\Exception;

use Throwable;

class BeesWaxResponseException extends BeesWaxGenericException
{
    /** @var resource */
    protected $curlHandler;

    /**
     * BeesWaxResponseException constructor.
     *
     * @param resource       $curlHandler
     * @param string         $message
     * @param Throwable|null $previous
     */
    public function __construct($curlHandler, string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, static::CODE_WAX_RESPONSE_EXCEPTION, $previous);
        $this->curlHandler = $curlHandler;
    }

    /**
     * @return resource
     */
    public function getCurlHandler()
    {
        return $this->curlHandler;
    }
}
