<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SnsResponse::class, function (Faker $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
        'type' => $faker->word(),
        'source_email' => $faker->unique()->safeEmail,
        'source_arn' => $faker->unique()->url,
        'unsubscribe_url' => $faker->unique()->url,
        'data_payload' => json_encode([]),
        'datetime_payload' => $faker->dateTime(),
    ];
});
