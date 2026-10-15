<?php

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

$successMessage = '';
$errorMessage = '';

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'News updated successfully.';
}elseif(isset($_GET['deleted']) && $_GET['deleted'] === '1'){
    $successMessage = 'News deleted successfully.';
}

if(isset($_GET['error'])){
    if($_GET['error'] === 'csrf'){
        $errorMessage = 'Invalid CSRF token. Please try again.';
    }elseif($_GET['error'] === 'invalid_id'){
        $errorMessage = 'Invalid news ID. Please try again.';
    }elseif($_GET['error'] === 'not_found'){
        $errorMessage = 'News item not found. Please try again.';
    }
}

$stmt = $pdo->prepare(
    "SELECT * FROM news ORDER BY published_at DESC"
);

$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <header class="header">
        <div class="container">

            <div class="header__content">

                <a href="../index.php" class="logo__link">
                    <img src="../img/icon/logo.jpg" alt="BTS Logo" class="logo">
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

    <section class="admin">
        <div class="container">

            <h2>Admin Panel</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</p>

            <a href="news-create.php" class="btn--delete">Create New News</a>
            <a href="bookings.php" class="btn--delete">
                View Bookings
            </a>

            <h3>Latest News</h3>

            <?php if ($successMessage !== ''): ?>
                <p class="success-message">
                    <?= htmlspecialchars($successMessage) ?>
                </p>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <p class="error-message">
                    <?= htmlspecialchars($errorMessage) ?>
                </p>
            <?php endif; ?>

            <?php foreach($news as $item): ?>

                <article class="news__card">

                    <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">

                    <div class="news__data">

                        <h3><?= htmlspecialchars($item['title']) ?></h3>

                        <p><?= htmlspecialchars($item['description']) ?></p>


                        <a href="news-edit.php?id=<?= (int) $item['id'] ?>" class="btn--delete">Edit</a>

                        <form action="news-delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this news item?');">

                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <button type="submit" class="btn--delete">Delete</button>
                        </form>
                    </div>

                </article>

            <?php endforeach; ?>

        </div>
    </section>

    <script src="../js/mobile-nav.js"></script>
    <script src="../js/switcher-theme.js"></script>
</body>
</html>