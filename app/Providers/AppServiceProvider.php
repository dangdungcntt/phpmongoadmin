<?php

namespace App\Providers;

use App\Encoders\BoolEncoder;
use App\Encoders\ObjectIdEncoder;
use App\Encoders\StringEncoder;
use App\Encoders\UTCDateTimeEncoder;
use App\Models\Connection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Nddcoder\ObjectMapper\ObjectMapper;
use Nddcoder\SqlToMongodbQuery\SqlToMongodbQuery;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.sidebar', fn($view) => $view->with('connections', Connection::all()));
        View::composer('layouts.app', fn($view) => $view->with('currentConnection', new Connection()));

        $this->app
            ->bind(
                'bson-object-mapper',
                function () {
                    $objectMapper = new ObjectMapper();
                    $objectMapper->addEncoder(UTCDateTime::class, UTCDateTimeEncoder::class);
                    $objectMapper->addEncoder(ObjectId::class, ObjectIdEncoder::class);
                    $objectMapper->addEncoder('string', StringEncoder::class);
                    $objectMapper->addEncoder('boolean', BoolEncoder::class);
                    $objectMapper->setJsonEncodeFlags(JSON_UNESCAPED_UNICODE);
                    return $objectMapper;
                }
            );

        $objectIdBuilder = function ($str) {
            if (preg_match('/^[0-9a-fA-F]{24}$/', $str) || strtotime($str) == false) {
                return new ObjectId($str);
            }

            return new ObjectId(dechex(strtotime($str)).'0000000000000000');
        };

        $dateBuilder = fn($str) => new UTCDateTime(date_create($str));

        SqlToMongodbQuery::addInlineFunctionBuilder('date', $dateBuilder);
        SqlToMongodbQuery::addInlineFunctionBuilder('ISODate', $dateBuilder);
        SqlToMongodbQuery::addInlineFunctionBuilder('ObjectId', $objectIdBuilder);
        SqlToMongodbQuery::addInlineFunctionBuilder('Id', $objectIdBuilder);
        SqlToMongodbQuery::addInlineFunctionBuilder('lower', fn($str) => strtolower($str));
        SqlToMongodbQuery::addInlineFunctionBuilder('upper', fn($str) => strtoupper($str));
    }
}
