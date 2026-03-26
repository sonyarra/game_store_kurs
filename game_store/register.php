<?php
require_once 'config.php';
require_once 'functions.php';

// Если пользователь уже авторизован, перенаправляем
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Все обязательные поля должны быть заполнены';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email адрес';
    } else {
        // Регистрация пользователя
        if (registerUser($username, $email, $password, $full_name)) {
            $success = 'Регистрация прошла успешно! Теперь вы можете войти в систему.';
            // Очищаем форму
            $_POST = array();
        } else {
            $error = 'Ошибка регистрации. Возможно, пользователь с таким именем или email уже существует.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"> <?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="login.php" class="btn btn-outline">Вход</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Регистрация</h1>
                <p>Создайте новый аккаунт</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Имя пользователя *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="full_name">Полное имя</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Пароль *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%;">Зарегистрироваться</button>
            </form>

            <div style="text-align: center; margin-top: 1rem;">
                <p>Уже есть аккаунт? <a href="login.php">Войдите здесь</a></p>
            </div>
        </div>
    </main>
</body>
</html>