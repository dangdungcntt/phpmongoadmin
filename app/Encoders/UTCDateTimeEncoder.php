<?php

namespace App\Encoders;

use MongoDB\BSON\UTCDateTime;
use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class UTCDateTimeEncoder implements ObjectMapperEncoder
{
    public const STUDIO_3T_DATE_FORMAT = 'Y-m-d\TH:i:s.vO';

    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var UTCDateTime $value */
        return 'ISODate("' . $value->toDateTime()->setTimezone(new \DateTimeZone(config('app.timezone')))->format(self::STUDIO_3T_DATE_FORMAT) . '")';
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
