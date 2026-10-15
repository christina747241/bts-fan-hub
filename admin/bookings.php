<?php

require_once '../includes/admin-auth.php';
require_once '../config/database.php';

require_once __DIR__ . '/../includes/csrf.php';

// Получаем все бронирования
$stmt = $pdo->prepare(
    "SELECT
        bookings.id AS booking_id,
        bookings.created_at AS booking_created_at,
        users.name AS user_name,
        users.email AS user_email,
        events.title AS event_title,
        events.event_date,
        events.image,
        events.location
    FROM bookings
    INNER JOIN users
        ON bookings.user_id = users.id
    INNER JOIN events
        ON bookings.event_id = events.id
    ORDER BY bookings.created_at DESC"
);

$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings</title>

    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/events.css">
</head>
<body>

    <header class="header">
        <div class="container">

            <div class="header__content">

                <a href="../index.php" class="logo__link">
                    <img
                        src="../img/icon/logo.jpg"
                        alt="BTS Logo"
                        class="logo"
                    >
                </a>

                <button class="themeButton" aria-label="Toggle Dark/Light Theme">
                    <span class="themeButton-icon">☀</span>
                </button>

                <form action="../logout.php" method="POST" class="join__form">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <button type="submit" class="form__button">
                        Log out
                    </button>
                </form>

            </div>

        </div>
    </header>

    <main>
        <section class="admin">
            <div class="container">

                <h2>All Bookings</h2>

                <p>
                    Total bookings:
                    <?= count($bookings) ?>
                </p>

                <?php if (!empty($bookings)): ?>

                    <div class="bookings__list">

                        <?php foreach ($bookings as $booking): ?>

                            <article class="event__card">

                            <img
                                class="event__img"
                                src="../<?= htmlspecialchars(
                                    ltrim($booking['image'], '/'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $booking['event_title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        <div class="event__data">

                            <h3>
                                <?= htmlspecialchars($booking['event_title']) ?>
                            </h3>

                            <p>
                                <strong>Booking ID:</strong>
                                <?= (int) $booking['booking_id'] ?>
                            </p>

                            <p>
                                <strong>User:</strong>
                                <?= htmlspecialchars($booking['user_name']) ?>
                            </p>

                            <p>
                                <strong>Email:</strong>
                                <?= htmlspecialchars($booking['user_email']) ?>
                            </p>

                            <p>
                                <strong>Event date:</strong>
                                <?= htmlspecialchars(
                                    date(
                                        'F j, Y H:i',
                                        strtotime($booking['event_date'])
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($booking['location']) ?>
                            </p>

                            <p>
                                <strong>Booking created:</strong>
                                <?= htmlspecialchars(
                                    date(
                                        'F j, Y H:i',
                                        strtotime($booking['booking_created_at'])
                                    )
                                ) ?>
                            </p>
                        </div>


                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p>No bookings have been made yet.</p>

                <?php endif; ?>

                <a href="index.php" class="form_button">
                    Back to Admin Panel
                </a>

            </div>
        </section>
    </main>

    <script src="../js/mobile-nav.js"></script>
    <script src="../js/switcher-theme.js"></script>
</body>
</html>