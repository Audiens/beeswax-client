<?php
declare(strict_types=1);

namespace Audiens\BeesWax\Exception;

use Exception;

class BeesWaxGenericException extends Exception
{
    public const CODE_WAX_RESPONSE_EXCEPTION  = 3000;
    public const CODE_INVALID_LOGIN           = 3001;
    public const CODE_SEGMENT_ALREADY_CREATED = 3002;
    public const CODE_SEGMENT_NOT_FOUND       = 3003;
    public const CODE_NON_EXISTING_SEGMENT    = 3004;

    public const CODE_CANT_CREATE_COOKIE_FILE        = 4000;
    public const CODE_ERROR_UPLOADING_SEGMENTS_USERS = 4001;
    public const CODE_CANT_CREATE_TEMP_FILE          = 4002;
}
