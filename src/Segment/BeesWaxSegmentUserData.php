<?php

namespace Audiens\BeesWax\Segment;

use JsonSerializable;

class BeesWaxSegmentUserData implements JsonSerializable
{
    public const USER_ID_TYPE_BEESWAX_COOKIE = 'BEESWAX';
    public const USER_ID_TYPE_CUSTOMER = 'CUSTOMER';
    public const USER_ID_TYPE_IDFA = 'IDFA';
    public const USER_ID_TYPE_IDFA_MD5 = 'IDFA_MD5';
    public const USER_ID_TYPE_IDFA_SHA = 'IDFA_SHA';
    public const USER_ID_TYPE_AD_ID = 'AD_ID';
    public const USER_ID_TYPE_AD_ID_MD5 = 'AD_ID_MD5';
    public const USER_ID_TYPE_AD_ID_SHA = 'AD_ID_SHA';
    public const USER_ID_TYPE_IP_ADDRESS = 'IP_ADDRESS';

    /** @var string */
    protected $userId;

    /** @var string[] */
    protected $segments;

    /**
     * BeesWaxSegmentUserData constructor.
     *
     * @param string   $userId
     * @param string[] $segments
     *
     * @see BeesWaxSegmentUserData::USER_ID_TYPE_*
     */
    public function __construct(string $userId, array $segments)
    {
        $this->userId = $userId;
        $this->segments = $segments;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'user_id' => $this->userId,
            'segments' => $this->segments,
        ];
    }
}
