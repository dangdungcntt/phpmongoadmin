<?php

namespace App\Helpers;

use MongoDB\Model\BSONArray;
use Nddcoder\ObjectMapper\ObjectMapper;

class BsonHelper
{
    public static function encode(\ArrayObject $value, string $pad = '    '): string
    {
        /** @var ObjectMapper $objectMapper */
        $objectMapper = app()->make('bson-object-mapper');

        $isArray = $value instanceof BSONArray;
        $open    = $isArray ? '[' : '{';
        $close   = $isArray ? ']' : '}';
        $result  = $open.PHP_EOL;

        foreach ($value as $field => $item) {
            $s      = match (true) {
                $item instanceof \ArrayObject => static::encode(
                    $item,
                    $pad.'    '
                ),
                default => $objectMapper->writeValueAsString(
                    $item
                )
            };
            $result .= $pad.($isArray ? '' : "\"$field\": ").$s.','.PHP_EOL;
        }
        return rtrim($result, " ,\n\r").PHP_EOL.substr($pad, 4).$close;
    }
}
