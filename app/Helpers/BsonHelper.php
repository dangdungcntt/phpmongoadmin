<?php

namespace App\Helpers;

use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
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

    public static function decode(array|string $input): mixed
    {
        $array = is_string($input) ? json_decode($input, true) : $input;

        if (is_null($array)) {
            return null;
        }

        $result = [];

        foreach ($array as $key => $value) {
            switch ($key) {
                case '$date':
                    return new UTCDateTime(is_array($value) ? $value['$numberLong'] ?? null : $value);
                case '$numberDecimal':
                    return new Decimal128($value);
                case '$oid':
                    return new ObjectId($value);
                default:
                    $result[$key] = is_array($value) ? static::decode($value) : $value;
            }
        }

        return $result;
    }
}
