<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function sendJson(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(405, [
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

if (!isset($_SESSION['user_id'])) {
    sendJson(401, [
        'success' => false,
        'message' => 'Please log in first.'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Invalid request data.'
    ]);
}

$token = $input['csrf_token'] ?? null;

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    sendJson(403, [
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
}

$allowedAlbums = [
    '2Cool4Skool',
    'ORUL8,2?',
    'The Most Beautiful Moment in Life',
    'WINGS',
    'Love Yourself',
    'Map of the Soul: Persona',
    'Map of the Soul: 7',
    'BE',
    'Proof',
    'ARIRANG'
];

$albumKey = $input['album_key'] ?? null;

if (!is_string($albumKey) || !in_array($albumKey, $allowedAlbums, true)) {
    sendJson(400, [
        'success' => false,
        'message' => 'Unknown album.'
    ]);
}

require_once __DIR__ . '/config/database.php';

$userId = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare(
        'SELECT id FROM album_favorites
         WHERE user_id = ? AND album_key = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $albumKey]);

    $favorite = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($favorite) {
        $stmt = $pdo->prepare(
            'DELETE FROM album_favorites
             WHERE user_id = ? AND album_key = ?'
        );
        $stmt->execute([$userId, $albumKey]);
        $isFavorite = false;
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO album_favorites (user_id, album_key)
             VALUES (?, ?)'
        );
        $stmt->execute([$userId, $albumKey]);
        $isFavorite = true;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM album_favorites WHERE user_id = ?'
    );
    $stmt->execute([$userId]);

    $count = (int) $stmt->fetchColumn();

    sendJson(200, [
        'success' => true,
        'is_favorite' => $isFavorite,
        'count' => $count
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());

    sendJson(500, [
        'success' => false,
        'message' => 'Could not save favorite.'
    ]);
}