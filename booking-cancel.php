<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

// 1. Принимаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

// 2. Проверяем CSRF-токен
$submitToken = $_POST['csrf_token'] ?? null;

if (
    !is_string($submitToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submitToken)
) {
    header('Location: profile.php?booking_error=csrf');
    exit;
}

// 3. Проверяем ID бронирования
$bookingId = filter_input(
    INPUT_POST,
    'booking_id',
    FILTER_VALIDATE_INT
);

if ($bookingId === false || $bookingId === null || $bookingId < 1) {
    header('Location: profile.php?booking_error=invalid_id');
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {

    // 4. Начинаем транзакцию
    $pdo->beginTransaction();

    // 5. Ищем бронь только текущего пользователя
    $stmt = $pdo->prepare(
        "SELECT
            bookings.id,
            events.event_date
        FROM bookings
        INNER JOIN events
            ON bookings.event_id = events.id
        WHERE bookings.id = ?
          AND bookings.user_id = ?
        FOR UPDATE"
    );

    $stmt->execute([$bookingId, $userId]);

    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $pdo->rollBack();
        header('Location: profile.php?booking_error=not_found');
        exit;
    }

    // 6. Не разрешаем отменять бронь прошедшего мероприятия
    if (strtotime($booking['event_date']) <= time()) {
        $pdo->rollBack();
        header('Location: profile.php?booking_error=past_event');
        exit;
    }

    // 7. Удаляем только бронь текущего пользователя
    $stmt = $pdo->prepare(
        "DELETE FROM bookings
        WHERE id = ? AND user_id = ?"
    );

    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() !== 1) {
        $pdo->rollBack();
        header('Location: profile.php?booking_error=not_found');
        exit;
    }

    // 8. Подтверждаем отмену
    $pdo->commit();

    header('Location: profile.php?booking_status=cancelled');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: profile.php?booking_error=failed');
    exit;
}