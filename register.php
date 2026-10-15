<?php
require_once __DIR__ . '/includes/csrf.php';
require_once 'config/database.php';

$errors = [];
$success = '';

$name = '';
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    if (!csrfTokenIsValid($_POST['csrf_token'] ?? null)) {
    $errors['general'] = 'Invalid security token. Please refresh the page.';
} else {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if($name === ''){
        $errors['name'] = 'Please enter your name.';
    }

    if($email === ''){
        $errors['email'] = 'Please enter your email.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email.';
    }

    if($password === ''){
        $errors['password'] = 'Please enter your password.';
    } elseif(strlen($password) < 6){
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if(empty($errors)){

        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if($user){
            $errors['email'] = 'Email already registered.';
        }else{
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $role = 'user';

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password_hash, role)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $passwordHash,
                $role
            ]);

            $success = 'Registration successful!';

            $name = '';
            $email = '';
            $password = '';

        }
    }
}


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="css/register.css">
</head>
<body>

    <section class="join" id="join">
        <div class="container">

            <h2>Join the Fan Community</h2>

            <form action="register.php" method="POST" class="join__form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <?php if (!empty($errors['general'])): ?>
                    <p class="error-message">
                        <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <div class="form__field">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    <span class="form__error" id="name-error">
                        <?= htmlspecialchars($errors['name'] ?? '') ?>
                    </span>
                </div>

                <div class="form__field">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    <span class="form__error" id="email-error">
                        <?= htmlspecialchars($errors['email'] ?? '') ?>
                    </span>
                </div>

                <div class="form__field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <span class="form__error" id="password-error">
                        <?= htmlspecialchars($errors['password'] ?? '') ?>
                    </span>
                </div>

                <span class="form__success">
                    <?= htmlspecialchars($success) ?>
                </span>
                <button class="form__button" type="submit">
                    Join the community
                </button>

                <a href="login.php" class="link">Log in here</a>
            </form>
        </div>
    </section>
</body>
</html>