<?php
declare(strict_types=1);

namespace Audiens\BeesWax;

use Audiens\BeesWax\Exception\BeesWaxGenericException;
use Audiens\BeesWax\Exception\BeesWaxResponseException;

class BeesWaxRequest
{
    public const METHOD_GET  = 'GET';
    public const METHOD_POST = 'POST';

    protected const BEESWAX_ENDPOINT = 'https://%s.api.beeswax.com%s';
    protected const BEESWAX_UA       = 'Audiens / BeesWax SDK';

    /** @var string */
    protected $path;

    /** @var string[] */
    protected $queryParams;

    /** @var string */
    protected $method;

    /** @var BeesWaxSession */
    protected $session;

    /** @var ?string */
    protected $payload;

    /**
     * BeesWaxRequest constructor.
     *
     * @param BeesWaxSession $session
     * @param string         $path
     * @param array          $queryParams
     * @param string         $method
     * @param null|string    $payload
     */
    public function __construct(
        BeesWaxSession $session,
        string $path,
        array $queryParams,
        string $method,
        ?string $payload = null
    ) {
        $this->path = $path;
        $this->queryParams = $queryParams;
        $this->method = $method;
        $this->session = $session;
        $this->payload = $payload;
    }

    /**
     * @throws BeesWaxResponseException
     * @throws BeesWaxGenericException
     */
    public function doRequest(): BeesWaxResponse
    {
        $curlUrl = sprintf(static::BEESWAX_ENDPOINT, $this->session->getBuzzKey(), $this->path);
        if (!empty($this->queryParams)) {
            $query = \http_build_query($this->queryParams);
            $curlUrl .= '?'.$query;
        }
        $curlHandler = curl_init($curlUrl);
        $cookieFileHandler = tmpfile();

        if ($cookieFileHandler === false) {
            throw new BeesWaxGenericException(
                'Impossible to create a temporary cookie file',
                BeesWaxGenericException::CODE_CANT_CREATE_COOKIE_FILE
            );
        }

        $cookieFilePath = stream_get_meta_data($cookieFileHandler)['uri'];

        if (!$cookieFilePath) {
            throw new BeesWaxGenericException(
                'An error occurred creating a temporary cookie file',
                BeesWaxGenericException::CODE_CANT_CREATE_COOKIE_FILE
            );
        }

        $cookieFilePath = realpath($cookieFilePath);
        fwrite($cookieFileHandler, $this->session->getSessionCookies());

        curl_setopt_array($curlHandler, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT      => static::BEESWAX_UA,
            CURLOPT_COOKIEFILE     => $cookieFilePath,
            CURLOPT_COOKIEJAR      => $cookieFilePath,
        ]);

        if ($this->method === static::METHOD_POST) {
            curl_setopt($curlHandler, CURLOPT_POST, 1);
        }

        if ($this->payload) {
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $this->payload);
        }

        $responsePayload = curl_exec($curlHandler);
        if ($responsePayload === false) {
            throw new BeesWaxResponseException($curlHandler, 'An error occurred attempting to log into BeesWax');
        }
        $statusCode = (int) curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
        curl_close($curlHandler);
        $cookiesContent = file_get_contents($cookieFilePath);
        unlink($cookieFilePath);

        $response = new BeesWaxResponse($curlHandler, $responsePayload, $statusCode, $cookiesContent);

        return $response;
    }
}
