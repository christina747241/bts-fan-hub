<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

// 1. Разрешаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

// 2. Проверяем CSRF-токен
$submitToken = $_POST['csrf_token'] ?? null;

if (
    !is_string($submitToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submitToken)
) {
    header('Location: events.php?booking_error=csrf#events');
    exit;
}

// 3. Получаем ID мероприятия
$eventId = filter_input(
    INPUT_POST,
    'event_id',
    FILTER_VALIDATE_INT
);

if ($eventId === false || $eventId === null || $eventId < 1) {
    header('Location: events.php?booking_error=invalid_event#events');
    exit;
}

$userId = (int) $_SESSION['user_id'];

try {

    // 4. Начинаем транзакцию
    $pdo->beginTransaction();

    // 5. Получаем мероприятие и блокируем его строку
    $stmt = $pdo->prepare(
        "SELECT id, event_date, capacity
        FROM events
        WHERE id = ?
        FOR UPDATE"
    );

    $stmt->execute([$eventId]);

    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $pdo->rollBack();
        header('Location: events.php?booking_error=not_found#events');
        exit;
    }

    // 6. Проверяем дату мероприятия
    if (strtotime($event['event_date']) <= time()) {
        $pdo->rollBack();
        header(
            'Location: events.php?booking_error=past_event#event-' . $eventId
        );
        exit;
    }

    // 7. Проверяем, нет ли у пользователя такой брони
    $stmt = $pdo->prepare(
        "SELECT id
        FROM bookings
        WHERE user_id = ? AND event_id = ?
        LIMIT 1"
    );

    $stmt->execute([$userId, $eventId]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->rollBack();
        header(
            'Location: events.php?booking_error=already_booked#event-' . $eventId
        );
        exit;
    }

    // 8. Считаем текущие бронирования
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
        FROM bookings
        WHERE event_id = ?"
    );

    $stmt->execute([$eventId]);

    $bookedCount = (int) $stmt->fetchColumn();

    // 9. Проверяем вместимость
    if ($bookedCount >= (int) $event['capacity']) {
        $pdo->rollBack();
        header(
            'Location: events.php?booking_error=full#event-' . $eventId
        );
        exit;
    }

    // 10. Сохраняем бронирование
    $stmt = $pdo->prepare(
        "INSERT INTO bookings (user_id, event_id)
        VALUES (?, ?)"
    );

    $stmt->execute([$userId, $eventId]);

    // 11. Подтверждаем транзакцию
    $pdo->commit();

    header(
        'Location: events.php?booking_status=success#event-' . $eventId
    );
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: events.php?booking_error=failed#event-' . $eventId
    );
    exit;
}
