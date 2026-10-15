<?php

if(session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

require_once 'config/database.php';

// Создаём CSRF-токен для будущей формы бронирования
require_once __DIR__ . '/includes/csrf.php';

$currentUserId = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : 0;

// Получаем мероприятия и количество бронирований
$stmt = $pdo->prepare(
    "SELECT
        events.id,
        events.title,
        events.image,
        events.description,
        events.event_date,
        events.location,
        events.capacity,
        COUNT(bookings.id) AS booked_count,
        MAX(
            CASE
                WHEN bookings.user_id = ? THEN 1
                ELSE 0
            END
        ) AS user_booked
    FROM events
    LEFT JOIN bookings
        ON events.id = bookings.event_id
    GROUP BY
        events.id,
        events.title,
        events.image,
        events.description,
        events.event_date,
        events.location,
        events.capacity
    ORDER BY events.event_date ASC"
);

$stmt->execute([$currentUserId]);

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$bookingStatus = $_GET['booking_status'] ?? '';
$bookingError = $_GET['booking_error'] ?? '';

$bookingErrors = [
    'csrf' => 'Invalid security token. Please refresh the page.',
    'invalid_event' => 'Invalid event ID.',
    'not_found' => 'Event not found.',
    'past_event' => 'This event has already taken place.',
    'already_booked' => 'You have already booked this event.',
    'full' => 'Sorry, this event is fully booked.',
    'failed' => 'Booking failed. Please try again.'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <header class="header">
        <div class="container">

            <div class="header__content">

                <a href="index.php" class="logo__link">
                    <img src="img/icon/logo.jpg" alt="BTS Logo" class="logo">
                </a>

                <button class="nav-button">
                    <span class="nav-button-icon"></span>
                </button>

                <div class="mobile-nav">
                    <div class="nav">
                        <ul>
                            <li><a href="#hero">Home</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#members">Members</a></li>
                            <li><a href="#albums">Albums</a></li>
                            <li><a href="#news">News</a></li>
                            <li><a href="#join">Join</a></li>
                        </ul>
                    </div>
                </div>

                <button class="themeButton" aria-label="Toggle Dark/Light Theme">
                    <span class="themeButton-icon">☀</span>
                </button>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <a href="profile.php" class="btn">Profile</a>

                <?php else: ?>

                    <a href="login.php" class="btn">Log in</a>
                    <a href="register.php" class="btn">Register</a>

                <?php endif; ?>
            </div>


        </div>
    </header>

    <main>
        <section class="events">

            <div class="container">

                <h2 class="title">Events</h2>

                <?php if ($bookingStatus === 'success'): ?>
                    <p class="success-message">
                        Your booking was successful!
                    </p>
                <?php endif; ?>

                <?php if (isset($bookingErrors[$bookingError])): ?>
                    <p class="error-message">
                        <?= htmlspecialchars($bookingErrors[$bookingError]) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <article class="event__card" id="event-<?= (int) $event['id'] ?>">

                        <img class="event__img"
                        src="<?= htmlspecialchars($event['image']) ?>"
                        alt="">

                        <div class="event__data">

                            <h3>
                                <?= htmlspecialchars($event['title']) ?>
                            </h3>
                            <p>
                                <?= nl2br(htmlspecialchars($event['description'])) ?>
                            </p>
                            <p>
                                <strong>Date:</strong>
                                <?= htmlspecialchars(
                                    date('F j, Y H:i', strtotime($event['event_date']))
                                ) ?>
                            </p>
                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($event['location']) ?>
                            </p>
                            <p>
                                <strong>Capacity:</strong>
                                <?= (int) $event['booked_count'] ?>
                                /
                                <?= (int) $event['capacity'] ?>
                            </p>
                            <p>
                                <strong>Available places:</strong>
                                <?= max(
                                    0,
                                    (int) $event['capacity'] - (int) $event['booked_count']
                                ) ?>
                            </p>

                            <?php
                                $availablePlaces = max(
                                    0,
                                    (int) $event['capacity'] - (int) $event['booked_count']
                                );

                                $isUpcoming = strtotime($event['event_date']) > time();
                            ?>

                            <?php if ($isUpcoming && $availablePlaces > 0): ?>

                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <p>
                                    <a href="login.php">Log in</a>
                                    to book a place.
                                </p>
                            <?php elseif ((int) $event['user_booked'] === 1): ?>

                            <p class="already--booked">You have already booked this event.</p>

                            <?php else: ?>

                            <form action="booking-create.php" method="POST">
                                <input
                                    type="hidden"
                                    name="event_id"
                                    value="<?= (int) $event['id'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                        $_SESSION['csrf_token'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                <button type="submit" class="btn--book">
                                    Book a Place
                                </button>
                            </form>

                            <?php endif; ?>

                            <?php elseif (!$isUpcoming): ?>

                                <p>This event has already taken place.</p>

                            <?php else: ?>

                            <p>This event is fully booked.</p>

                            <?php endif; ?>
                        </div>


                    </article>

                <?php endforeach; ?>

                <?php else: ?>
                    <p>No events available yet.</p>
                <?php endif; ?>
            </div>

        </section>
    </main>

    <script src="js/mobile-nav.js"></script>
    <script src="js/switcher-theme.js"></script>
</body>
</html>