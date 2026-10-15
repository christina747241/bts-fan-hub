<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = '';

$title = '';
$description = '';
$image = '';
$publishedAt = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    if (!csrfTokenIsValid($_POST['csrf_token'] ?? null)) {
        header('Location: index.php?error=csrf');
        exit;
    }

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $publishedAt = isset($_POST['published_at']) ? trim($_POST['published_at']) : '';

    if($title === ''){
        $errors['title'] = 'Please enter the title.';
    }

    if($description === ''){
        $errors['description'] = 'Please enter the description.';
    }

    if($image === ''){
        $errors['image'] = 'Please enter the image URL.';
    }

    if ($publishedAt === '') {
    $errors['published_at'] =
        'Please enter the published date and time.';
    } else {
        $date = DateTime::createFromFormat(
            'Y-m-d\TH:i',
            $publishedAt
        );

        if (
            !$date ||
            $date->format('Y-m-d\TH:i') !== $publishedAt
        ) {
            $errors['published_at'] =
                'Please enter a valid date and time.';
        }
    }

    if(empty($errors)){

        try {
            $dbPublishedAt = $date->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "INSERT INTO news (title, description, image, published_at)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $title,
                $description,
                $image,
                $dbPublishedAt,
            ]);

            $success = 'News created successfully.';
        } catch (PDOException $e) {
            error_log($e->getMessage());

            $errors['general'] = 'Could not create news. Please try again.';
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New News</title>

    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/login.css">
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
            <h2>Create New News</h2>

            <form action="news-create.php" method="POST" class="join__form">
                <?php if (!empty($errors['general'])): ?>
                    <p class="error-message">
                        <?= htmlspecialchars(
                            $errors['general'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                <?php endif; ?>
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Title">
                <span>
                    <?php echo htmlspecialchars($errors['title'] ?? ''); ?>
                </span>

                <textarea name="description" placeholder="Description"><?php echo htmlspecialchars($description); ?></textarea>
                <span>
                    <?php echo htmlspecialchars($errors['description'] ?? ''); ?>
                </span>

                <input type="text" name="image" value="<?php echo htmlspecialchars($image); ?>" placeholder="Image URL">
                <span>
                    <?php echo htmlspecialchars($errors['image'] ?? ''); ?>
                </span>

                <input type="datetime-local" name="published_at" value="<?php echo htmlspecialchars($publishedAt); ?>">
                <span>
                    <?php echo htmlspecialchars($errors['published_at'] ?? ''); ?>
                </span>

                <button type="submit">Create news</button>
                <span>
                    <?= htmlspecialchars($success) ?>
                </span>

            </form>
            <a href="index.php">Back to News List</a>

        </div>
    </section>

    <script src="../js/mobile-nav.js"></script>
    <script src="../js/switcher-theme.js"></script>
</body>
</html>