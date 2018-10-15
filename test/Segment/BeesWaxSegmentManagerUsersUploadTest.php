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

        $fileId = $this->uploadRandomSegmentUsersCsvFile($manager);

        TestCase::assertGreaterThan(0, $fileId);
    }

    public function testUsersFileUploadStatusPending(): void
    {
        $session = $this->getSession();
        $session->login(true);
        $manager = new BeesWaxSegmentManager($session);

        $fileId = $this->uploadRandomSegmentUsersCsvFile($manager);

        TestCase::assertGreaterThan(0, $fileId);

        $status = $manager->getUploadSegmentUsersFileStatus($fileId);

        TestCase::assertEquals(BeesWaxSegmentManager::FILE_UPLOAD_STATUS_PENDING, $status);
    }

    private function uploadRandomSegmentUsersCsvFile(BeesWaxSegmentManager $manager): int
    {
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

        return $manager->usersUpload($segment, $userData, $segmentKeyType, $userIdType, $operationType, $continent);
    }
}
