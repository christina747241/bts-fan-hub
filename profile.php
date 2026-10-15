<?php

require_once 'includes/auth.php';

require_once 'config/database.php';

require_once __DIR__ . '/includes/community-status.php';

// Создаём CSRF-токен, если его ещё нет
require_once __DIR__ . '/includes/csrf.php';

// Получаем сообщения после отмены брони
$bookingStatus = $_GET['booking_status'] ?? '';
$bookingError = $_GET['booking_error'] ?? '';

$bookingErrors = [
    'csrf' => 'Invalid security token. Please refresh the page.',
    'invalid_id' => 'Invalid booking ID.',
    'not_found' => 'Booking not found or you do not have permission to cancel it.',
    'past_event' => 'You cannot cancel a booking for an event that has already started.',
    'failed' => 'Cancellation failed. Please try again.'
];

// Получаем бронирования текущего пользователя
$stmt = $pdo->prepare(
    "SELECT
        bookings.id AS booking_id,
        bookings.created_at AS booking_created_at,
        events.id AS event_id,
        events.title,
        events.image,
        events.description,
        events.event_date,
        events.location
    FROM bookings
    INNER JOIN events
        ON bookings.event_id = events.id
    WHERE bookings.user_id = ?
    ORDER BY events.event_date ASC"
);

$stmt->execute([(int) $_SESSION['user_id']]);

$myBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <header class="header">
        <div class="container">

            <div class="header__content">

                <a href="index.php" class="logo__link">
                    <img src="img/icon/logo.jpg" alt="BTS Logo" class="logo">
                </a>


                <button class="themeButton" aria-label="Toggle Dark/Light Theme">
                    <span class="themeButton-icon">☀</span>
                </button>

                <form action="logout.php" method="POST" class="join__form">
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

    <section class="profile">
        <div class="container">
            <h2>Profile</h2>

            <h3>
                <?= htmlspecialchars($_SESSION['name']) ?>

                <?php if ($isCommunityMember): ?>
                    <span
                        class="community-badge"
                        title="Member of the BTS Fan Community"
                    >
                        💜 ARMY Member
                    </span>
                <?php endif; ?>
            </h3>

            <p>Role: <span class="role"><?php echo htmlspecialchars($_SESSION['role']); ?></span></p>

            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin/index.php">Admin Panel</a>
        <?php endif; ?>

        <h3>My Bookings</h3>

        <?php if ($bookingStatus === 'cancelled'): ?>

            <p class="success-message">
                Your booking has been cancelled successfully.
            </p>

        <?php endif; ?>

        <?php if (isset($bookingErrors[$bookingError])): ?>

            <p class="error-message">
                <?= htmlspecialchars($bookingErrors[$bookingError]) ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($myBookings)): ?>

            <?php foreach ($myBookings as $booking): ?>

                <article class="event__card">

                    <img class="event__img"
                        src="<?= htmlspecialchars($booking['image']) ?>"
                        alt="">

                        <div class="event__data">

                            <h3>
                                <?= htmlspecialchars($booking['title']) ?>
                            </h3>

                            <p>
                                <?= nl2br(htmlspecialchars($booking['description'])) ?>
                            </p>

                            <p>
                                <strong>Date:</strong>
                                <?= htmlspecialchars(
                                    date('F j, Y H:i', strtotime($booking['event_date']))
                                ) ?>
                            </p>

                            <p>
                                <strong>Location:</strong>
                                <?= htmlspecialchars($booking['location']) ?>
                            </p>

                            <p>
                                <strong>Booking date:</strong>
                                <?= htmlspecialchars(
                                    date('F j, Y H:i', strtotime($booking['booking_created_at']))
                                ) ?>
                            </p>

                            <?php if (strtotime($booking['event_date']) > time()): ?>

                                <form
                                    action="booking-cancel.php"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to cancel this booking?');"
                                >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int) $booking['booking_id'] ?>"
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

                                    <button type="submit" class="btn--cancel">
                                        Cancel Booking
                                    </button>

                                </form>

                            <?php else: ?>

                                <p>This event has already started.</p>

                            <?php endif; ?>
                        </div>


                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <p>You have no bookings yet.</p>

            <a href="events.php">Explore Events</a>

        <?php endif; ?>
        </div>


    </section>

    <script src="js/mobile-nav.js"></script>
    <script src="js/switcher-theme.js"></script>
</body>
</html>