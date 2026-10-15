<?php

$host = getenv('DB_HOST') ?: 'database';
$dbname = getenv('DB_NAME') ?: 'bts_fan_hub';
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (
    $username === false ||
    $username === '' ||
    $password === false ||
    $password === ''
) {
    throw new RuntimeException(
        'Database credentials are not configured.'
    );
}

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);