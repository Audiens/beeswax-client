<?php
declare(strict_types=1);

namespace Audiens\BeesWax;

class BeesWaxResponse
{
    /** @var string */
    protected $cookiesContent;

    /** @var string */
    protected $payload;

    /** @var resource */
    protected $curlHandler;

    /** @var int */
    protected $statusCode;

    public function __construct($curlHandler, string $payload, int $statusCode, string $cookiesContent)
    {
        $this->curlHandler = $curlHandler;
        $this->payload = $payload;
        $this->statusCode = $statusCode;
        $this->cookiesContent = $cookiesContent;
    }

    public function getCookiesContent(): string
    {
        return $this->cookiesContent;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @return resource
     */
    public function getCurlHandler()
    {
        return $this->curlHandler;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
