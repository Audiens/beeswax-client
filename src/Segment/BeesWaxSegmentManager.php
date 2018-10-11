<?php

namespace Audiens\BeesWax\Segment;

use Audiens\BeesWax\BeesWaxResponse;
use Audiens\BeesWax\BeesWaxSession;
use Audiens\BeesWax\Exception\BeesWaxGenericException;
use Audiens\BeesWax\Exception\BeesWaxResponseException;

class BeesWaxSegmentManager
{
    protected const CREATE_PATH = '/rest/segment';
    protected const READ_PATH   = '/rest/segment';

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
     * @throws \Audiens\BeesWax\Exception\BeesWaxGenericException
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

        $request = $this->session->getRequestBuilder()->build(static::CREATE_PATH, [], 'POST', $payload);

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

    public function update(BeesWaxSegment $segment): BeesWaxSegment
    {
        throw new \RuntimeException('Not implemented yet!');
    }

    public function delete(BeesWaxSegment $segment): bool
    {
        throw new \RuntimeException('Not implemented yet!');
    }

    /**
     * @param string $id
     *
     * @return BeesWaxSegment
     * @throws BeesWaxGenericException
     * @throws BeesWaxResponseException
     */
    public function read(string $id): BeesWaxSegment
    {
        $request = $this->session->getRequestBuilder()->build(static::READ_PATH, ['segment_id' => $id], 'GET', null);

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
}
