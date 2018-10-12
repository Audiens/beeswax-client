<?php

namespace Audiens\Test\BeesWax\Segment;

use Audiens\BeesWax\Segment\BeesWaxSegment;
use Audiens\BeesWax\Segment\BeesWaxSegmentManager;
use Audiens\BeesWax\Segment\BeesWaxSegmentUserData;
use Audiens\Test\BeesWax\AbstractBeesWaxTestCase;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BeesWaxSegmentManagerUsersUploadTest extends AbstractBeesWaxTestCase
{
    public function testSuccessfulUpload(): void
    {
        $session = $this->getSession();
        $session->login(true);

        $manager = new BeesWaxSegmentManager($session);
        $segment = new BeesWaxSegment(Uuid::uuid4()->toString());
        $segmentKeyType = BeesWaxSegment::SEGMENT_KEY_TYPE_DEFAULT;
        $userIdType = BeesWaxSegmentUserData::USER_ID_TYPE_AD_ID;
        $operationType = BeesWaxSegmentManager::USERS_UPLOAD_OPERATION_TYPE_ADD_SEGMENTS;
        $continent = BeesWaxSegmentManager::USERS_UPLOAD_CONTINENT_EMEA;

        $manager->create($segment);
        $segmentId = $segment->getId();

        $userData = [];
        for ($i = 0; $i < 100; $i++) {
            $userData[] = new BeesWaxSegmentUserData(Uuid::uuid4()->toString(), [$segmentId]);
        }

        $manager->usersUpload($segment, $userData, $segmentKeyType, $userIdType, $operationType, $continent);

        // Ensure there are no exceptions running usersUpload
        TestCase::assertTrue(true);
    }
}
