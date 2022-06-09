<?php

require_once __DIR__ . '/../../vendor/getkirby/cms/bootstrap.php';

new Kirby([
    'roots' => [
        'content' => __DIR__ . '/content',
        'site'    => __DIR__ . '/site',
    ]
]);
