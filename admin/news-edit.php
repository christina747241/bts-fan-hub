<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = '';

$id = '';
$title = '';
$description = '';
$image = '';
$publishedAt = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id < 1) {
        header('Location: index.php?error=invalid_id');
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM news WHERE id = ?"
    );

    $stmt->execute([$id]);

    $news = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$news) {
        header('Location: index.php');
        exit;
    }

    $title = $news['title'];
    $description = $news['description'];
    $image = $news['image'];
    $publishedAt = date(
        'Y-m-d\TH:i',
        strtotime($news['published_at'])
    );
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfTokenIsValid($_POST['csrf_token'] ?? null)) {
        header('Location: index.php?error=csrf');
        exit;
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id < 1) {
        header('Location: index.php?error=invalid_id');
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

    $date = null;

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

        $dbPublishedAt = $date->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            "UPDATE news
            SET
                title = ?,
                description = ?,
                image = ?,
                published_at = ?
            WHERE id = ?"
        );

        $stmt->execute([
            $title,
            $description,
            $image,
            $dbPublishedAt,
            $id
        ]);

        header('Location: index.php?updated=1');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News</title>

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
            <h2>Edit News</h2>

            <form action="news-edit.php" method="POST" class="join__form">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                 <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <div class="form__field">

                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>
                    <span>
                        <?php echo htmlspecialchars($errors['title'] ?? ''); ?>
                    </span>
                </div>

                <div class="form__field">

                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required><?= htmlspecialchars($description) ?></textarea>
                    <span>
                        <?php echo htmlspecialchars($errors['description'] ?? ''); ?>
                    </span>
                </div>

                <div class="form__field">

                    <label for="image">Image URL:</label>
                    <input type="text" name="image" id="image" value="<?= htmlspecialchars($image) ?>" required>
                    <span>
                        <?php echo htmlspecialchars($errors['image'] ?? ''); ?>
                    </span>
                </div>

                <div class="form__field">

                    <label for="published_at">Published At:</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="<?= htmlspecialchars($publishedAt) ?>" required>
                    <span>
                        <?php echo htmlspecialchars($errors['published_at'] ?? ''); ?>
                    </span>
                </div>

                <button type="submit" class="form__button">Save Changes</button>

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