<?php

namespace Audiens\Test\BeesWax;

use Audiens\BeesWax\BeesWaxSession;
use Audiens\BeesWax\Exception\BeesWaxLoginException;
use PHPUnit\Framework\TestCase;

class BeesWaxSessionTest extends AbstractBeesWaxTestCase
{
    public function testSuccessfulLogin(): void
    {
        $session = $this->getSession();
        $session->login(true);

        TestCase::assertNotEmpty($session->getSessionCookies(), "The session's cookies should be set");
    }

    public function testSuccessfulLoginWontLoginTwiceIfNotForced(): void
    {
        $builder = $this->getMockedLoginRequestBuilder(true);

        $session = new BeesWaxSession(Config::SANDBOX_BUZZKEY, 'some email', 'some password', '');
        $session->setRequestBuilder($builder);

        $session->login(true);
        $session->login(true);
        $session->login(true, true);

        TestCase::assertTrue(true, 'This test should throw no exceptions in case of successful login');
    }

    public function testUnsuccessfulLogin(): void
    {
        $builder = $this->getMockedLoginRequestBuilder(false);
        $session = new BeesWaxSession(Config::SANDBOX_BUZZKEY, 'crowd@place.com', '(^_(^_^)_^)', '');
        $session->setRequestBuilder($builder);

        $this->expectException(BeesWaxLoginException::class); // Giving a wrong user or password a specific exception class should rise
        $this->expectExceptionCode(BeesWaxLoginException::CODE_INVALID_LOGIN); // Giving a wrong user or password a specific exception code should rise

        $session->login();
    }
}
