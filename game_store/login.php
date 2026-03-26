<?php
require_once 'config.php';
require_once 'functions.php';

// Если пользователь уже авторизован, перенаправляем
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $user = loginUser($username, $password);
        
        if ($user) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверное имя пользователя или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"> <?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="register.php" class="btn">Регистрация</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Вход в систему</h1>
                <p>Введите свои учетные данные</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Войти</button>
            </form>

            <div style="text-align: center; margin-top: 1rem;">
                <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь здесь</a></p>
            </div>
            </div>
        </div>
    </main>
</body>
</html>