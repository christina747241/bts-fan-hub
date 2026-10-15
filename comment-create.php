<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

// Проверяем HTTP-метод
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Проверяем CSRF-токен
$submitToken = $_POST['csrf_token'] ?? null;

if (
    !is_string($submitToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submitToken)
) {
    header('Location: index.php?comment_error=csrf#news');
    exit;
}

// Получаем ID новости
$newsId = filter_input(
    INPUT_POST,
    'news_id',
    FILTER_VALIDATE_INT
);

// Получаем текст комментария
$content = $_POST['content'] ?? null;

// Проверяем ID
if ($newsId === false || $newsId === null || $newsId < 1) {
    header('Location: index.php?comment_error=invalid_id#news');
    exit;
}

// Проверяем тип текста
if (!is_string($content)) {
    header('Location: index.php?comment_error=invalid_content#news');
    exit;
}

$content = trim($content);

// Проверяем, что комментарий не пустой и не слишком длинный
if (
    $content === '' ||
    mb_strlen($content) > 1000
) {
    header('Location: index.php?comment_error=invalid_content#news-' . $newsId);
    exit;
}

// Проверяем, существует ли новость
$stmt = $pdo->prepare(
    "SELECT id FROM news WHERE id = ?"
);

$stmt->execute([$newsId]);

$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    header('Location: index.php?comment_error=news_not_found#news');
    exit;
}

// Сохраняем комментарий
$stmt = $pdo->prepare(
    "INSERT INTO comments (user_id, news_id, content)
    VALUES (?, ?, ?)"
);

$stmt->execute([
    (int) $_SESSION['user_id'],
    $newsId,
    $content
]);

// Возвращаем пользователя к той же новости
$_SESSION['comment_status'] = 'success';

header('Location: index.php#news-' . $newsId);
exit;