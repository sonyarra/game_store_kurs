<?php
require_once 'config.php';
require_once 'functions.php';

$games = getGames();
$categories = getCategories();
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : null;

if ($selected_category) {
    $games = getGames($selected_category);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <header>
    <div class="container header-content">
        <a href="index.php" class="logo"> <?php echo SITE_NAME; ?></a>
       <nav class="nav-links">
    <?php if (isLoggedIn()): ?>
        <?php if (isAdmin()): ?>
            <!-- Только для админа -->
            <a href="admin.php" class="btn btn-warning">Админ-панель</a>
            <span>Привет, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
        <?php else: ?>
            <!-- Только для обычных пользователей -->
            <span>Привет, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
            <a href="cart.php" class="btn btn-outline">
                Корзина 
                <?php if (getCartCount($_SESSION['user']['id']) > 0): ?>
                    <span style="background: var(--danger); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.8rem; margin-left: 5px;">
                        <?php echo getCartCount($_SESSION['user']['id']); ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="my_rentals.php" class="btn btn-outline">Мои аренды</a>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php" class="btn btn-outline">Вход</a>
        <a href="register.php" class="btn">Регистрация</a>
    <?php endif; ?>
</nav>
    </div>
</header>

    <main class="main-content">
        <div class="container">
            <?php if (!isLoggedIn()): ?>
                <div style="text-align: center; margin-bottom: 3rem;">
                    <h1 style="font-size: 3rem; margin-bottom: 1rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        Добро пожаловать в GameStore!
                    </h1>
                    <p style="font-size: 1.2rem; color: var(--gray); margin-bottom: 2rem;">
                        Лучшие игры по лучшим ценам. Войдите или зарегистрируйтесь для покупок.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="register.php" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
                            Начать покупки
                        </a>
                        <a href="login.php" class="btn btn-outline" style="font-size: 1.1rem; padding: 1rem 2rem;">
                            Войти в аккаунт
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <h1 style="margin-bottom: 2rem;">Каталог игр</h1>
                
                <!-- Фильтр по категориям -->
                <div style="margin-bottom: 2rem;">
                    <h3>Категории:</h3>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                        <a href="index.php" class="btn <?php echo !$selected_category ? '' : 'btn-outline'; ?>">
                            Все игры
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="index.php?category=<?php echo $category['id']; ?>" 
                               class="btn <?php echo $selected_category == $category['id'] ? '' : 'btn-outline'; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Сетка игр -->
            <?php if (isLoggedIn()): ?>
                <div class="game-grid">
                    <?php foreach ($games as $game): ?>
                        <!-- В index.php замените блок карточки игры на этот код: -->
<div class="game-card" onclick="window.location.href='game.php?id=<?php echo $game['id']; ?>'" style="cursor: pointer;">
    <div class="game-image">
        <img src="<?php echo getGameImage($game['image_url']); ?>" 
             alt="<?php echo htmlspecialchars($game['title']); ?>"
             onerror="this.src='assets/default_game.jpg'">
    </div>
    <div class="game-info">
        <h3 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h3>
        <p class="game-description"><?php echo htmlspecialchars($game['description']); ?></p>
        <div class="game-meta">
            <div class="game-price"><?php echo number_format($game['price'], 0, ',', ' '); ?> ₽</div>
            <div class="game-rating">★ <?php echo $game['rating']; ?></div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <small style="color: var(--gray);"><?php echo htmlspecialchars($game['category_name']); ?></small>
            <small style="color: var(--success);">В наличии: <?php echo $game['stock']; ?></small>
        </div>
        
       <?php if (!isAdmin()): ?>
    <!-- Кнопки добавления в корзину и аренды для обычных пользователей -->
    <div onclick="event.stopPropagation();">
        <div style="display: flex; gap: 0.5rem;">
            <form method="POST" action="cart.php" style="flex: 1;">
                <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                <button type="submit" name="add_to_cart" class="btn btn-success" style="width: 100%;">
                    В корзину
                </button>
            </form>
            <a href="rent.php?id=<?php echo $game['id']; ?>" class="btn btn-warning" style="flex: 1; text-align: center;">
                Арендовать
            </a>
        </div>
    </div>
<?php else: ?>
            <!-- Кнопки управления для администратора -->
            <div onclick="event.stopPropagation();">
                <div style="display: flex; gap: 0.5rem;">
                    <a href="edit_game.php?id=<?php echo $game['id']; ?>" class="btn btn-warning" style="flex: 1;">
                         Редактировать
                    </a>
                    <form method="POST" action="admin.php" style="flex: 1;">
                        <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                        <button type="submit" name="delete_game" class="btn btn-danger" style="width: 100%;"
                                onclick="return confirm('Вы уверены, что хотите удалить игру <?php echo htmlspecialchars($game['title']); ?>?')">
                             Удалить
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
                        
                    <?php endforeach; ?>
                </div>

                <?php if (empty($games)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray);">
                        <h3>Игры не найдены</h3>
                        <p>Попробуйте выбрать другую категорию</p>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Все права защищены.</p>
            <p style="margin-top: 0.5rem; opacity: 0.8;">Лучший магазин игр для настоящих геймеров</p>
        </div>
    </footer>
</body>
</html>