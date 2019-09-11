<?php

$view = [
    "template_path" => TEMPLATES_URI,
];

$logger = [
    "name" => "appfinans-system",
    "path" => isset($_ENV["docker"]) ? "php://stdout" : __DIR__ . "/../logs/app.log",
    "level" => \Monolog\Logger::DEBUG,
];

if ( IN_DEVELOPMENT ) {
    $displayErrorDetails = true;
    $addContentLengthHeader = false;
    if ( IN_LOCAL ) {
        $database = [
            "host" => "HOST DE TESTES",
            "name" => "DB DE TESTES",
            "charset" => "utf8",
            "user" => "USUARIO",
            "password" => "SENHA",
            "prefix" => "PRE_",
        ];
    } else {
        $database = [
            "host" => "localhost",
            "name" => "rlbene36_appfinans",
            "charset" => "utf8",
            "user" => "rlbene36_admin",
            "password" => "admin",
            "prefix" => "tb_",
        ];
    }
} else {
    $displayErrorDetails = false;
    $addContentLengthHeader = true;
    $database = [
        "host" => "HOST PRODUCAO",
        "name" => "DB PRODUCAO",
        "charset" => "utf8",
        "user" => "USUARIO",
        "password" => "SENHA",
        "prefix" => "PRE_",
    ];
}

return [
    "settings" => [
        "displayErrorDetails" => $displayErrorDetails,
        "addContentLengthHeader" => $addContentLengthHeader,
        "view" => $view,
        "logger" => $logger,
        "mail" => $mail,
        "database" => $database,
    ]
];
