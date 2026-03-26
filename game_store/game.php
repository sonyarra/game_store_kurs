<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$game_id = (int)$_GET['id'];
$game = getGameById($game_id);

if (!$game) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Добавление в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    $user_id = $_SESSION['user']['id'];
    $game_id = (int)$_POST['game_id'];
    
    if (addToCart($user_id, $game_id)) {
        $success = 'Игра добавлена в корзину!';
    } else {
        $error = 'Ошибка при добавлении в корзину';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"> <?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="btn btn-warning">Админ-панель</a>
                    <?php else: ?>
                        <a href="cart.php" class="btn btn-outline">
                            Корзина 
                            <?php if (getCartCount($_SESSION['user']['id']) > 0): ?>
                                <span style="background: var(--danger); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.8rem; margin-left: 5px;">
                                    <?php echo getCartCount($_SESSION['user']['id']); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <span>Привет, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
                    <a href="logout.php" class="btn btn-danger">Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Вход</a>
                    <a href="register.php" class="btn">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="game-detail-container">
                <a href="index.php" class="btn btn-outline" style="margin-bottom: 2rem;">← Назад к играм</a>
                
                <div class="game-detail-card">
                    <div class="game-detail-image">
                        <img src="<?php echo getGameImage($game['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($game['title']); ?>"
                             onerror="this.src='assets/default_game.jpg'">
                    </div>
                    
                    <div class="game-detail-info">
                        <h1 class="game-detail-title"><?php echo htmlspecialchars($game['title']); ?></h1>
                        
                        <div class="game-detail-meta">
                            <div class="game-detail-category">
                                Категория: <?php echo htmlspecialchars($game['category_name']); ?>
                            </div>
                            <div class="game-detail-developer">
                                Разработчик: <?php echo htmlspecialchars($game['developer'] ?: 'Не указан'); ?>
                            </div>
                            <div class="game-detail-release">
                                Дата выхода: <?php echo $game['release_date'] ? date('d.m.Y', strtotime($game['release_date'])) : 'Не указана'; ?>
                            </div>
                            <div class="game-detail-stock">
                                В наличии: <?php echo $game['stock']; ?> шт.
                            </div>
                        </div>
                        
                        <div class="game-detail-price-section">
                            <div class="game-detail-price"><?php echo number_format($game['price'], 0, ',', ' '); ?> ₽</div>
                            <div class="game-detail-rating">★ <?php echo $game['rating']; ?></div>
                        </div>
                        
                        <div class="game-detail-description">
                            <h3>Описание</h3>
                            <p><?php echo nl2br(htmlspecialchars($game['description'] ?: 'Описание отсутствует')); ?></p>
                        </div>
                        
                        <div class="game-detail-actions">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
    <!-- Кнопки добавления в корзину и аренды для обычных пользователей -->
    <div style="display: flex; gap: 1rem; width: 100%;">
        <form method="POST" action="" style="flex: 1;">
            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
            <button type="submit" name="add_to_cart" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                Добавить в корзину
            </button>
        </form>
        <a href="rent.php?id=<?php echo $game['id']; ?>" class="btn btn-warning" style="flex: 1; padding: 1rem; font-size: 1.1rem; text-align: center;">
            Арендовать
        </a>
    </div>
<?php elseif (isAdmin()): ?>
                            <?php elseif (isAdmin()): ?>
                                <!-- Кнопки управления для администратора -->
                                <div style="display: flex; gap: 1rem; width: 100%;">
                                    <a href="edit_game.php?id=<?php echo $game['id']; ?>" class="btn btn-warning" style="flex: 1; padding: 1rem;">
                                        Редактировать
                                    </a>
                                    <form method="POST" action="admin.php" style="flex: 1;">
                                        <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                                        <button type="submit" name="delete_game" class="btn btn-danger" style="width: 100%; padding: 1rem;"
                                                onclick="return confirm('Вы уверены, что хотите удалить игру <?php echo htmlspecialchars($game['title']); ?>?')">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                                    Войдите, чтобы добавить в корзину
                                </a>
                            <?php endif; ?>
                            
                            <a href="index.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem; padding: 1rem; text-align: center;">
                                ← Вернуться на главную
                            </a>
                        </div>
                    </div>
                </div>
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