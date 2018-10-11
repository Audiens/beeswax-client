<?php

namespace Audiens\Test\BeesWax\Segment;

use Audiens\BeesWax\Segment\BeesWaxSegment;
use Audiens\BeesWax\Segment\BeesWaxSegmentManager;
use Audiens\Test\BeesWax\AbstractBeesWaxTestCase;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BeesWaxSegmentManagerUpdateTest extends AbstractBeesWaxTestCase
{
    public function testFullFeaturesUpdateSegmentWillSuccess(): void
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

        $manager->create($segment);

        $segment->setName(Uuid::uuid4()->toString());
        $segment->setDescription(null);
        $segment->setAlternativeId(Uuid::uuid4()->toString());
        $segment->setCpmCost(2.7);
        $segment->setAggregateExcludes(false);

        $manager->update($segment);
        $readSegment = $manager->read($segment->getId());

        TestCase::assertTrue($segment->equals($readSegment));
    }
}
