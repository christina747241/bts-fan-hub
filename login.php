<?php

session_start();

require_once __DIR__ . '/includes/csrf.php';
require_once 'config/database.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (!csrfTokenIsValid($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid security token. Please refresh the page.';
    } else {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password_hash'])){
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        header("location: profile.php");
        exit;
    }else{
        $error = 'Invalid email or password.';
    }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <section class="login">
        <div class="container">

            <h2>Login</h2>

            <form action="login.php" method="POST" class="join__form">

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
                    <label for="email">Email:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                    >
                </div>

                <div class="form__field">
                    <label for="password">Password:</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <?php if ($error !== ''): ?>
                    <p class="error-message">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <button type="submit" class="form__button">
                    Login
                </button>

                <a href="register.php" class="link">Don't have an account? Register here.</a>


            </form>

        </div>
    </section>
</body>
</html>