<?php

namespace App\Encoders;

use MongoDB\BSON\UTCDateTime;
use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class UTCDateTimeEncoder implements ObjectMapperEncoder
{
    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var UTCDateTime $value */
        return 'ISODate("' . $value->toDateTime()->format(DATE_RFC3339_EXTENDED) . '")';
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
