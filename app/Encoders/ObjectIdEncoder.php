<?php

namespace App\Encoders;

use MongoDB\BSON\ObjectId;
use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class ObjectIdEncoder implements ObjectMapperEncoder
{
    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var ObjectId $value */
        $id = $value->__toString();
        return "ObjectId(\"{$id}\")";
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
