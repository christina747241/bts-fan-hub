<?php

require_once __DIR__ . '/auth.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: /index.php');
    exit;
}