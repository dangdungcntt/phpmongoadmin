<?php

namespace App\Encoders;

use MongoDB\BSON\ObjectId;
use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class ObjectIdEncoder implements ObjectMapperEncoder
{
    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var ObjectId $value */
        return "ObjectId(\"{$value->__toString()}\")";
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
