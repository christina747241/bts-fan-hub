<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#join');
    exit;
}

// Проверяем CSRF-токен
$token = $_POST['csrf_token'] ?? null;

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    header('Location: index.php?join_error=csrf#join');
    exit;
}

// Получаем данные формы
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$favoriteMember = $_POST['member'] ?? '';
$favoriteSong = trim($_POST['song'] ?? '');
$message = trim($_POST['message'] ?? '');
$wantsUpdates = isset($_POST['updates']) ? 1 : 0;

// Разрешённые значения для любимого участника
$allowedMembers = [
    '',
    'rm',
    'jk',
    'v',
    'jimin',
    'suga',
    'j-hope',
    'jin'
];

// Проверяем имя
if (
    !is_string($name) ||
    $name === '' ||
    mb_strlen($name) > 100
) {
    header('Location: index.php?join_error=invalid#join');
    exit;
}

// Проверяем email
if (
    !is_string($email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150
) {
    header('Location: index.php?join_error=invalid#join');
    exit;
}

// Проверяем любимого участника
if (
    !is_string($favoriteMember) ||
    !in_array($favoriteMember, $allowedMembers, true)
) {
    header('Location: index.php?join_error=invalid#join');
    exit;
}

// Проверяем длину остальных полей
if (
    !is_string($favoriteSong) ||
    mb_strlen($favoriteSong) > 150 ||
    !is_string($message) ||
    mb_strlen($message) > 2000
) {
    header('Location: index.php?join_error=invalid#join');
    exit;
}

// Проверяем, не отправлял ли этот email заявку раньше
$stmt = $pdo->prepare(
    "SELECT id FROM community_signups WHERE email = ? LIMIT 1"
);

$stmt->execute([$email]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    header('Location: index.php?join_error=duplicate#join');
    exit;
}

// Связываем заявку с аккаунтом, если пользователь вошёл
$userId = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : null;

try {

    $stmt = $pdo->prepare(
    "INSERT INTO community_signups
        (name, email, favorite_member, favorite_song,
        message, wants_updates, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $name,
        $email,
        $favoriteMember !== '' ? $favoriteMember : null,
        $favoriteSong !== '' ? $favoriteSong : null,
        $message !== '' ? $message : null,
        $wantsUpdates,
        $userId,
    ]);

    header('Location: index.php?join_status=success#join');
    exit;

} catch (PDOException $e) {

    // Подробности остаются в серверном логе
    error_log($e->getMessage());

    header('Location: index.php?join_error=failed#join');
    exit;
}