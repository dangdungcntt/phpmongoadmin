<?php

namespace App\Encoders;

use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class BoolEncoder implements ObjectMapperEncoder
{
    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var string $value */
        return $value === true ? 'true' : 'false';
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
