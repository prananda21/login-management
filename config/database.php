<?php

function getDatabaseConfig(): array
{
    // parse env in env.db.test ==> Test Database
    $env_test = parse_ini_file(".env.db.test");
    $host_test = $env_test["DB_HOST"];
    $port_test = $env_test["DB_PORT"];
    $db_test = $env_test["DB_NAME"];
    $username_test = $env_test["DB_USERNAME"];
    $password_test = $env_test["DB_PASSWORD"];

    // parse env in env.db.prod ==> Production Database
    $env_prod = parse_ini_file(".env.db.prod");
    $host_prod = $env_prod["DB_HOST"];
    $port_prod = $env_prod["DB_PORT"];
    $db_prod = $env_prod["DB_NAME"];
    $username_prod = $env_prod["DB_USERNAME"];
    $password_prod = $env_prod["DB_PASSWORD"];

    return [
        "database" => [
            "test" => [
                "url" => "mysql:host=$host_test:$port_test;dbname=$db_test",
                "username" => $username_test,
                "password" => $password_test,
            ],
            "prod" => [
                "url" => "mysql:host=$host_prod:$port_prod;dbname=$db_prod",
                "username" => $username_prod,
                "password" => $password_prod,
            ],
        ],
    ];
}
