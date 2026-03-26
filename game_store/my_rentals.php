<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

if (isAdmin()) {
    header('Location: admin.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$success = '';
$error = '';

// Обработка отмены аренды
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_rental'])) {
    $rental_id = (int)$_POST['rental_id'];
    if (deleteRental($rental_id, $user_id)) {
        $success = 'Аренда успешно отменена';
    } else {
        $error = 'Ошибка при отмене аренды';
    }
}

$rentals = getUserRentals($user_id);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои аренды - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="index.php" class="btn btn-outline">Назад к играм</a>
                <span>Привет, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="cart-container">
                <h1 style="margin-bottom: 2rem;">Мои арендованные игры</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (empty($rentals)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray);">
                        <h3>У вас нет арендованных игр</h3>
                        <p>Перейдите в каталог, чтобы арендовать игру</p>
                        <a href="index.php" class="btn" style="margin-top: 1rem;">Перейти к играм</a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach ($rentals as $rental): 
                            $today = date('Y-m-d');
                            $is_active = ($rental['status'] === 'active' && $rental['end_date'] >= $today);
                        ?>
                            <div class="user-card" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                <!-- Миниатюра игры -->
                                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden;">
                                    <img src="<?php echo getGameImage($rental['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($rental['title']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <!-- Информация об аренде -->
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($rental['title']); ?></h4>
                                    <div style="color: var(--gray); font-size: 0.9rem;">
                                        Срок аренды: <?php echo date('d.m.Y', strtotime($rental['start_date'])); ?> 
                                        – <?php echo date('d.m.Y', strtotime($rental['end_date'])); ?>
                                    </div>
                                    <div style="margin-top: 0.5rem;">
                                        <?php if ($is_active): ?>
                                            <span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">Активна</span>
                                        <?php else: ?>
                                            <span style="background: var(--gray); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem;">Завершена</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Дата бронирования и кнопка отмены -->
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                    <small style="color: var(--gray);">Забронировано: <?php echo date('d.m.Y', strtotime($rental['created_at'])); ?></small>
                                    
                                    <?php if ($is_active): ?>
                                        <form method="POST" onsubmit="return confirm('Вы уверены, что хотите отменить аренду?');">
                                            <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                            <button type="submit" name="cancel_rental" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                                Отменить
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>