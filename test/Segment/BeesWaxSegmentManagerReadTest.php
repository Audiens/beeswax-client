<?php

namespace Audiens\Test\BeesWax\Segment;

use Audiens\BeesWax\Segment\BeesWaxSegment;
use Audiens\BeesWax\Segment\BeesWaxSegmentManager;
use Audiens\Test\BeesWax\AbstractBeesWaxTestCase;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BeesWaxSegmentManagerReadTest extends AbstractBeesWaxTestCase
{
    public function testFullFeaturesReadSegmentWillSuccess(): void
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
        $readSegment = $manager->read($segment->getId());

        TestCase::assertTrue($createdSegment->equals($readSegment));
    }
}
