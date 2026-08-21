<?php

use App\Providers\AppServiceProvider;
use App\Providers\SanctumServiceProvider;
use MongoDB\Laravel\MongoDBServiceProvider;

return [
    AppServiceProvider::class,
    MongoDBServiceProvider::class,
    SanctumServiceProvider::class,
];
