<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

// Пользователи не могут заходить в корзину
if (isAdmin()) {
    header('Location: admin.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$success = '';
$error = '';

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $game_id = (int)$_POST['game_id'];
    
    if (addToCart($user_id, $game_id)) {
        $success = 'Игра добавлена в корзину!';
    } else {
        $error = 'Ошибка при добавлении в корзину';
    }
}

// Удаление из корзины
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $game_id = (int)$_POST['game_id'];
    
    if (removeFromCart($user_id, $game_id)) {
        $success = 'Игра удалена из корзины';
    } else {
        $error = 'Ошибка при удалении из корзины';
    }
}

// Получение корзины
$cart_items = getCart($user_id);
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['total_price'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - <?php echo SITE_NAME; ?></title>
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
                <h1 style="margin-bottom: 2rem;">Ваша корзина</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (empty($cart_items)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray);">
                        <h3>Ваша корзина пуста</h3>
                        <p>Добавьте игры из каталога, чтобы они появились здесь</p>
                        <a href="index.php" class="btn" style="margin-top: 1rem;">Перейти к играм</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
    <img src="<?php echo getGameImage($item['image_url']); ?>" 
         alt="<?php echo htmlspecialchars($item['title']); ?>"
         onerror="this.src='assets/default_game.jpg'">
</div>
                            <div class="cart-item-info">
                                <h4 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <div class="cart-item-price">
                                    <?php echo number_format($item['price'], 0, ',', ' '); ?> ₽ × <?php echo $item['quantity']; ?> шт.
                                </div>
                                <div style="color: var(--primary); font-weight: 500; margin-top: 0.5rem;">
                                    Итого: <?php echo number_format($item['total_price'], 0, ',', ' '); ?> ₽
                                </div>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="game_id" value="<?php echo $item['game_id']; ?>">
                                <button type="submit" name="remove_from_cart" class="btn btn-danger">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-total">
                        Общая сумма: <?php echo number_format($total_amount, 0, ',', ' '); ?> ₽
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                        <a href="index.php" class="btn btn-outline">Продолжить покупки</a>
                        <button class="btn btn-success" onclick="alert('Функция оплаты в разработке 🚧')">
                            Перейти к оплате
                        </button>
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