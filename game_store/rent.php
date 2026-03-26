<?php
require_once 'config.php';
require_once 'functions.php';

// Проверка авторизации
requireLogin();

// Только обычные пользователи могут арендовать
if (isAdmin()) {
    header('Location: admin.php');
    exit;
}

$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$game = getGameById($game_id);

if (!$game) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rent_game'])) {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($start_date) || empty($end_date)) {
        $error = 'Укажите даты аренды';
    } elseif ($end_date < $start_date) {
        $error = 'Дата окончания должна быть позже даты начала';
    } else {
        if (rentGame($user_id, $game_id, $start_date, $end_date)) {
            $success = 'Игра успешно арендована!';
        } else {
            $error = 'Ошибка при аренде игры';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аренда игры - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="index.php" class="btn btn-outline">Вернуться к играм</a>
                <span>Привет, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="auth-container" style="max-width: 500px;">
                <div class="auth-header">
                    <h1>Аренда игры</h1>
                    <p><?php echo htmlspecialchars($game['title']); ?></p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br>
                        <a href="index.php" class="btn" style="margin-top: 1rem;">Вернуться на главную</a>
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="start_date">Дата начала аренды *</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">Дата окончания аренды *</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" 
                                   value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="rent_game" class="btn btn-success" style="flex: 1;">
                                Подтвердить аренду
                            </button>
                            <a href="index.php" class="btn btn-outline">Отмена</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>