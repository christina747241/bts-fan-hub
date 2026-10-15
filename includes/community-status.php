<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$isCommunityMember = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare(
        "SELECT 1
         FROM community_signups
         WHERE user_id = ?
         LIMIT 1"
    );

    $stmt->execute([(int) $_SESSION['user_id']]);

    $isCommunityMember = (bool) $stmt->fetchColumn();
}