<?php

namespace App\Encoders;

use Nddcoder\ObjectMapper\Contracts\ObjectMapperEncoder;

class StringEncoder implements ObjectMapperEncoder
{
    public function encode(mixed $value, ?string $className = null): string
    {
        /** @var string $value */
        return '"' . $value .'"';
    }

    public function decode(mixed $value, ?string $className = null): mixed
    {
        return null;
    }
}
