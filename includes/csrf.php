<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (
    empty($_SESSION['csrf_token']) ||
    !is_string($_SESSION['csrf_token'])
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfTokenIsValid(mixed $token): bool
{
    return (
        is_string($token) &&
        isset($_SESSION['csrf_token']) &&
        is_string($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token)
    );
}