<?php

namespace Audiens\BeesWax\Segment;

use Audiens\BeesWax\BeesWaxRequest;
use Audiens\BeesWax\BeesWaxResponse;
use Audiens\BeesWax\BeesWaxSession;
use Audiens\BeesWax\Exception\BeesWaxGenericException;
use Audiens\BeesWax\Exception\BeesWaxResponseException;

class BeesWaxSegmentManager
{
    public const USERS_UPLOAD_OPERATION_TYPE_ADD_SEGMENTS         = 'ADD_SEGMENTS';
    public const USERS_UPLOAD_OPERATION_TYPE_REPLACE_ALL_SEGMENTS = 'REPLACE_ALL_SEGMENTS';

    public const USERS_UPLOAD_CONTINENT_NAM  = 'NAM';
    public const USERS_UPLOAD_CONTINENT_EMEA = 'EMEA';
    public const USERS_UPLOAD_CONTINENT_APEC = 'APEC';

    protected const API_CRUD_PATH = '/rest/segment';
    protected const API_UPLOAD_METADATA_PATH = '/rest/segment_upload';
    protected const API_UPLOAD_FILE_PATH = '/rest/segment_upload/upload/%d';

    /** @var BeesWaxSession */
    protected $session;

    public function __construct(BeesWaxSession $session)
    {
        $this->session = $session;
    }

    /**
     * Returns a BeesWax segment.
     * The argument segment will be modified adding the identifier to it
     *
     * @param BeesWaxSegment $segment
     *
     * @return BeesWaxSegment
     * @throws \Audiens\BeesWax\Exception\BeesWaxGenericException (CODE_SEGMENT_ALREADY_CREATED)
     * @throws \Audiens\BeesWax\Exception\BeesWaxResponseException
     */
    public function create(BeesWaxSegment $segment): BeesWaxSegment
    {
        if ($segment->getId()) {
            throw new BeesWaxGenericException(
                sprintf('The segment %s has already been created', $segment->getName()),
                BeesWaxGenericException::CODE_SEGMENT_ALREADY_CREATED
            );
        }

        $payloadData = [
            'segment_name' => $segment->getName(),
            'cpm_cost' => $segment->getCpmCost(),
            'ttl_days' => $segment->getTtlDays(),
            'aggregate_excludes' => $segment->isAggregateExcludes(),
        ];

        if ($advertiserId = $segment->getAdvertiserId()) {
            $payloadData['advertiser_id'] = $advertiserId;
        }

        if ($description = $segment->getDescription()) {
            $payloadData['segment_description'] = $description;
        }

        if ($alternativeId = $segment->getAlternativeId()) {
            $payloadData['alternative_id'] = $alternativeId;
        }

        $payload = json_encode($payloadData);

        $request = $this->session->getRequestBuilder()->build(static::API_CRUD_PATH, [], BeesWaxRequest::METHOD_POST, $payload);

        $response = $request->doRequest();
        $this->manageSuccess($response, 'Error creating segment: %s');

        $responseData = json_decode($response->getPayload());
        $segmentId = $responseData->payload->id ?? null;

        if ($segmentId === null) {
            throw new BeesWaxResponseException(
                $response->getCurlHandler(),
                'Error creating segment: id not found'
            );
        }

        $segment->setId((string) $segmentId);

        return $segment;
    }

    /**
     * @param string $id
     *
     * @return BeesWaxSegment
     * @throws BeesWaxGenericException (CODE_SEGMENT_NOT_FOUND)
     * @throws BeesWaxResponseException
     */
    public function read(string $id): BeesWaxSegment
    {
        $request = $this->session->getRequestBuilder()->build(static::API_CRUD_PATH, ['segment_id' => $id], BeesWaxRequest::METHOD_GET, null);

        $response = $request->doRequest();
        $this->manageSuccess($response, 'Error reading segment: %s');

        $responseData = json_decode($response->getPayload());
        if (
            !isset($responseData->payload)
            || !\is_array($responseData->payload)
            || \count($responseData->payload) !== 1
        ) {
            throw new BeesWaxGenericException(
                sprintf('Segment #%s not found', $id),
                BeesWaxGenericException::CODE_SEGMENT_NOT_FOUND
            );
        }
        $responseData = $responseData->payload[0];

        $segment = new BeesWaxSegment(
            $responseData->segment_name,
            $responseData->segment_description,
            $responseData->alternative_id,
            $responseData->advertiser_id,
            $responseData->cpm_cost,
            $responseData->ttl_days,
            $responseData->aggregate_excludes
        );
        $segment->setId((string)$responseData->segment_id);

        return $segment;
    }

    /**
     * @param BeesWaxSegment $segment
     *
     * @return BeesWaxSegment
     * @throws BeesWaxGenericException (CODE_NON_EXISTING_SEGMENT)
     */
    public function update(BeesWaxSegment $segment): BeesWaxSegment
    {
        if ($segment->getId() === null) {
            throw new BeesWaxGenericException(
                "Can't update a non-existing segment!",
                BeesWaxGenericException::CODE_NON_EXISTING_SEGMENT
            );
        }

        $payloadData = [
            'segment_id' => (int)$segment->getId(),
            'segment_name' => $segment->getName(),
            'alternative_id' => $segment->getAlternativeId(),
            'advertiser_id' => $segment->getAdvertiserId(),
            'segment_description' => $segment->getDescription(),
            'cpm_cost' => $segment->getCpmCost(),
            'aggregate_excludes' => $segment->isAggregateExcludes(),
        ];

        $payload = json_encode($payloadData);

        $request = $this->session->getRequestBuilder()->build(static::API_CRUD_PATH, [], BeesWaxRequest::METHOD_PUT, $payload);

        $response = $request->doRequest();
        $this->manageSuccess($response, 'Error updating segment: %s');

        return $segment;
    }

    /**
     * @param BeesWaxSegment           $segment
     * @param BeesWaxSegmentUserData[] $userData
     * @param string                   $segmentKeyType BeesWaxSegment::SEGMENT_KEY_TYPE_*
     * @param string                   $userIdType BeesWaxSegmentUserData::USER_ID_TYPE_*
     * @param string                   $operationType BeesWaxSegmentManager::USERS_UPLOAD_OPERATION_TYPE_*
     * @param string                   $continent BeesWaxSegmentManager::USERS_UPLOAD_CONTINENT_*
     *
     * @throws BeesWaxGenericException
     *
     * @see BeesWaxSegment::SEGMENT_KEY_TYPE_*
     * @see BeesWaxSegmentUserData::USER_ID_TYPE_*
     * @see BeesWaxSegmentManager::USERS_UPLOAD_OPERATION_TYPE_*
     * @see BeesWaxSegmentManager::USERS_UPLOAD_CONTINENT_*
     */
    public function usersUpload(
        BeesWaxSegment $segment,
        array $userData,
        string $segmentKeyType = BeesWaxSegment::SEGMENT_KEY_TYPE_DEFAULT,
        string $userIdType = BeesWaxSegmentUserData::USER_ID_TYPE_BEESWAX_COOKIE,
        string $operationType = BeesWaxSegmentManager::USERS_UPLOAD_OPERATION_TYPE_ADD_SEGMENTS,
        string $continent = BeesWaxSegmentManager::USERS_UPLOAD_CONTINENT_NAM
    ): void {
        if ($segment->getId() === null) {
            throw new BeesWaxGenericException(
                "Can't add users to a non-existing segment!",
                BeesWaxGenericException::CODE_NON_EXISTING_SEGMENT
            );
        }

        $fh = tmpfile();
        $tmpFilePath = realpath(stream_get_meta_data($fh)['uri']);
        $rows = 0;
        $exceptionToThrow = null;

        try {
            foreach ($userData as $datum) {
                $rowData = array_merge([$datum->getUserId()], $datum->getSegments());
                fputcsv($fh, $rowData, '|');
                $rows++;
            }

            $fileSize = filesize($tmpFilePath);
            if ($fileSize === false || $fileSize === 0) {
                fclose($fh);

                if ($rows > 0) {
                    throw new BeesWaxGenericException(
                        'An error occurred creating the file to upload',
                        BeesWaxGenericException::CODE_ERROR_UPLOADING_SEGMENTS_USERS
                    );
                }

                return;
            }

            $fileId = $this->sendUsersUploadMetadata(
                $tmpFilePath,
                $segmentKeyType,
                $continent,
                $userIdType,
                $operationType
            );

            $this->uploadSegmentUsersFile($fileId, $tmpFilePath);
        } catch (BeesWaxGenericException $exception) {
            $exceptionToThrow = $exception;
        }

        fclose($fh);

        if ($exceptionToThrow !== null) {
            throw $exceptionToThrow;
        }
    }

    public function delete(BeesWaxSegment $segment): bool
    {
        throw new \RuntimeException('Not implemented yet!');
    }

    /**
     * @param BeesWaxResponse $response
     * @param string          $errorFormat with one string parameter
     *
     * @throws BeesWaxResponseException
     */
    private function manageSuccess(BeesWaxResponse $response, string $errorFormat): void
    {
        $responseData = json_decode($response->getPayload());
        $responseErrors = $responseData->errors ?? [];
        $statusCode = $response->getStatusCode();

        if (
            !empty($responseErrors)
            || \is_bool($responseData)
            || (isset($responseData->success) && !$responseData->success)
            || $statusCode !== 200
        ) {
            $message = \count($responseErrors) ? $responseErrors[0] : ($responseData->message ?? (string) $statusCode);
            throw new BeesWaxResponseException(
                $response->getCurlHandler(),
                sprintf($errorFormat, $message)
            );
        }
    }

    /**
     * @param        $filePath
     * @param string $segmentKeyType
     * @param string $continent
     * @param string $userIdType
     * @param string $operationType
     *
     * @return int
     *
     * @throws BeesWaxGenericException
     * @throws BeesWaxResponseException
     */
    private function sendUsersUploadMetadata(
        $filePath,
        string $segmentKeyType,
        string $continent,
        string $userIdType,
        string $operationType
    ): int {
        $fileSize = filesize($filePath);

        if ($fileSize === false) {
            throw new BeesWaxGenericException(
                'Impossible to determine the size of the file to upload',
                BeesWaxGenericException::CODE_ERROR_UPLOADING_SEGMENTS_USERS
            );
        }
        $payload = \json_encode([
            'file_name' => basename($filePath),
            'size_in_bytes' => $fileSize,
            'file_format' => 'DELIMITED',
            'segment_key_type' => $segmentKeyType,
            'continent' => $continent,
            'user_id_type' => $userIdType,
            'operation_type' => $operationType,
        ]);

        $request = $this
            ->session
            ->getRequestBuilder()
            ->build(static::API_UPLOAD_METADATA_PATH, [], BeesWaxRequest::METHOD_POST, $payload);

        $response = $request->doRequest();
        $this->manageSuccess($response, "Error sending segment's users meta data: %s");

        $data = \json_decode($response->getPayload());

        return $data->payload->id;
    }

    /**
     * @param int    $fileId
     * @param string $filePath
     *
     * @throws BeesWaxGenericException
     * @throws BeesWaxResponseException
     */
    private function uploadSegmentUsersFile(int $fileId, string $filePath): void
    {
        $cFile = new \CURLFile($filePath, 'text/csv', 'segment_file');
        $cFile->setPostFilename(basename($filePath));
        $payload = ['segment_file' => $cFile];

        $request = $this->session->getRequestBuilder()->build(
            sprintf(static::API_UPLOAD_FILE_PATH, $fileId),
            [],
            BeesWaxRequest::METHOD_POST,
            $payload
        );

        $response = $request->doRequest();
        $this->manageSuccess($response, "Error uploading segment's users CSV file: %s");
    }
}
