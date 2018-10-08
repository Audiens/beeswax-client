<?php
declare(strict_types=1);

namespace Audiens\BeesWax;

use Audiens\BeesWax\Exception\BeesWaxGenericException;
use Audiens\BeesWax\Exception\BeesWaxLoginException;
use Audiens\BeesWax\Exception\BeesWaxResponseException;

class BeesWaxSession
{
    /** @var string */
    private $buzzKey;

    /** @var string */
    private $email;
    
    /** @var string */
    private $password;

    /** @var string */
    private $cookiesContent;

    /** @var BeesWaxRequestBuilder */
    private $requestBuilder;

    /** @var bool */
    private $loggedIn;

    public function __construct(string $buzzKey, string $email, string $password, string $cookiesContent = '')
    {
        $this->buzzKey = $buzzKey;
        $this->email = $email;
        $this->password = $password;
        $this->cookiesContent = $cookiesContent;

        $this->loggedIn = false;
        $this->requestBuilder = new BeesWaxRequestBuilder($this);
    }

    public function setRequestBuilder(BeesWaxRequestBuilder $requestBuilder): BeesWaxSession
    {
        $this->requestBuilder = $requestBuilder;

        return $this;
    }

    /**
     * @param bool $longerLastingSession
     * @param bool $forceRefresh
     *
     * @see https://docs.beeswax.com/v0.5/docs/authentication
     *
     * @throws BeesWaxLoginException
     * @throws BeesWaxResponseException
     * @throws BeesWaxGenericException
     */
    public function login(bool $longerLastingSession = true, bool $forceRefresh = false): void
    {
        if (!$forceRefresh && $this->loggedIn && $this->cookiesContent) {
            return;
        }

        $payload = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if ($longerLastingSession) {
            $payload['keep_logged_in'] = 1;
        }

        $request = $this->requestBuilder->build(
            '/rest/authenticate',
            [],
            BeesWaxRequest::METHOD_POST,
            json_encode($payload)
        );

        $response = $request->doRequest();

        $json = json_decode($response->getPayload());

        if ($json === false || $json->success !== true) {
            throw new BeesWaxLoginException($response, 'Impossible to login with the given information');
        }

        $this->loggedIn = true;
        $this->cookiesContent = $response->getCookiesContent();
    }

    public function getSessionCookies(): string
    {
        return $this->cookiesContent;
    }

    public function getBuzzKey(): string
    {
        return $this->buzzKey;
    }

    public function getRequestBuilder(): BeesWaxRequestBuilder
    {
        return $this->requestBuilder;
    }
}
