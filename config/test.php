<?php
// config/test.php
$config = [
    'id' => 'app-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
//        'db' => [
//            'dsn' => 'mysql:host=localhost;dbname=yii_app_test',
//        ]
    ],
    'params' => [
        'appEmail' => 'tdv@example.com'
    ]
];
return $config;