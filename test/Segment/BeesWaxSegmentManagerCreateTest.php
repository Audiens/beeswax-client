<?php

namespace Audiens\Test\BeesWax\Segment;

use Audiens\BeesWax\Segment\BeesWaxSegment;
use Audiens\BeesWax\Segment\BeesWaxSegmentManager;
use Audiens\Test\BeesWax\AbstractBeesWaxTestCase;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BeesWaxSegmentManagerCreateTest extends AbstractBeesWaxTestCase
{
    public function testMinimalCreateSegmentWillSuccess(): void
    {
        $session = $this->getSession();
        $session->login(true);

        $manager = new BeesWaxSegmentManager($session);

        $segment = new BeesWaxSegment(Uuid::uuid4()->toString());
        $createdSegment = $manager->create($segment);

        TestCase::assertNotEmpty($createdSegment->getId());
        TestCase::assertNotEmpty($segment->getId());
    }

    public function testFullFeaturesCreateSegmentWillSuccess(): void
    {
        $session = $this->getSession();
        $session->login(true);

        $manager = new BeesWaxSegmentManager($session);

        $segment = new BeesWaxSegment(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            null,
            1.5,
            90,
            true
        );
        $createdSegment = $manager->create($segment);

        TestCase::assertNotEmpty($createdSegment->getId());
    }
}
