<?php

namespace Audiens\Test\BeesWax;

use Audiens\BeesWax\BeesWaxRequest;
use Audiens\BeesWax\BeesWaxRequestBuilder;
use Audiens\BeesWax\BeesWaxResponse;
use Audiens\BeesWax\BeesWaxSession;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

abstract class AbstractBeesWaxTestCase extends TestCase
{
    protected function setUp()
    {
        try {
            $dotenv = new Dotenv(__DIR__ . DIRECTORY_SEPARATOR . '..');
            $dotenv->load();
        } catch (\Exception $ignored) {
            // .env file might not exist, but it's not a problem
        }
    }

    protected function getSession(): BeesWaxSession
    {
        $email = getenv('BEESWAX_USER_EMAIL');
        $password = getenv('BEESWAX_USER_PASSWORD');

        return new BeesWaxSession(Config::SANDBOX_BUZZKEY, $email, $password, '');
    }

    protected function getMockedLoginRequestBuilder(bool $successful): BeesWaxRequestBuilder
    {
        $cookies = 'some cookies here';
        $payload = json_encode(['success' => $successful]);
        /** @var BeesWaxRequest|ObjectProphecy $request */
        $request = $this->prophesize(BeesWaxRequest::class);
        $ch = curl_init();
        $request->doRequest()->willReturn(new BeesWaxResponse($ch, $payload, $successful ? 200 : 401, ''));
        /** @var BeesWaxRequestBuilder|ObjectProphecy $requestBuilder */
        $requestBuilder = $this->prophesize(BeesWaxRequestBuilder::class);
        $requestBuilder->build(Argument::cetera())->willReturn($request->reveal());

        return $requestBuilder->reveal();
    }
}
