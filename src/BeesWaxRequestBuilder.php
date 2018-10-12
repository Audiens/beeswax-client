<?php

namespace Audiens\BeesWax;

class BeesWaxRequestBuilder
{
    /** @var BeesWaxSession */
    protected $session;

    public function __construct(BeesWaxSession $session)
    {
        $this->session = $session;
    }

    /**
     * @param string               $path
     * @param array                $queryParams
     * @param string               $method
     * @param null|string|mixed    $payload
     *
     * @return BeesWaxRequest
     */
    public function build(
        string $path,
        array $queryParams,
        string $method,
        $payload = null
    ): BeesWaxRequest {
        return new BeesWaxRequest($this->session, $path, $queryParams, $method, $payload);
    }
}
