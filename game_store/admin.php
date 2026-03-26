<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requireAdmin();

$success = '';
$error = '';

// Добавление игры с изображением
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $developer = trim($_POST['developer']);
    $release_date = $_POST['release_date'];
    $stock = (int)$_POST['stock'];
    
    // Обработка изображения
    $image_filename = null;
    if (isset($_FILES['game_image']) && $_FILES['game_image']['error'] === UPLOAD_ERR_OK) {
        $image_filename = uploadImage($_FILES['game_image']);
        if (!$image_filename) {
            $error = 'Ошибка при загрузке изображения';
        }
    }
    
    if (empty($title) || $price <= 0) {
        $error = 'Заполните обязательные поля корректно';
    } elseif (!$error) {
        if (addGame($title, $description, $price, $category_id, $developer, $release_date, $stock, $image_filename)) {
            $success = 'Игра успешно добавлена';
        } else {
            $error = 'Ошибка при добавлении игры';
        }
    }
}

// Удаление пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    if (deleteUser($user_id)) {
        $success = 'Пользователь успешно удален';
    } else {
        $error = 'Ошибка при удалении пользователя';
    }
}

// Удаление игры
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game'])) {
    $game_id = (int)$_POST['game_id'];
    
    if (deleteGame($game_id)) {
        $success = 'Игра успешно удалена';
    } else {
        $error = 'Ошибка при удалении игры';
    }
}

// Получение данных
$users = getAllUsers();
$games = getGames();
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="index.php" class="btn btn-outline">Вернуться на сайт</a>
                <span style="background: var(--warning); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
                    Администратор
                </span>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="admin-panel">
                <h1 style="margin-bottom: 2rem;">Панель администратора</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Управление пользователями -->
                <div class="admin-section">
                    <h3>Управление пользователями</h3>
                    <div class="user-list">
                        <?php foreach ($users as $user): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <h4>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span style="background: var(--warning); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.8rem; margin-left: 0.5rem;">
                                                ADMIN
                                            </span>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <small>Зарегистрирован: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></small>
                                </div>
                                <div>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger" 
                                                    onclick="return confirm('Вы уверены, что хотите удалить пользователя <?php echo htmlspecialchars($user['username']); ?>?')">
                                                Удалить
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--gray);">Системный администратор</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Добавление новой игры -->
                <div class="admin-section">
                    <h3> Добавить новую игру</h3>
                    <form method="POST" action="" enctype="multipart/form-data" style="display: grid; gap: 1rem; max-width: 600px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="game_image">Изображение игры</label>
                                <input type="file" id="game_image" name="game_image" class="form-control" 
                                    accept="image/jpeg,image/png,image/gif">
                                <small style="color: var(--gray);">Форматы: JPG, PNG, GIF (макс. 5MB)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Название игры *</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="price">Цена (₽) *</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Категория</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Описание</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="developer">Разработчик</label>
                                <input type="text" id="developer" name="developer" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="release_date">Дата выпуска</label>
                                <input type="date" id="release_date" name="release_date" class="form-control">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="stock">Количество на складе</label>
                                <input type="number" id="stock" name="stock" class="form-control" min="0" value="0">
                            </div>
                        </div>

                        <button type="submit" name="add_game" class="btn btn-success">Добавить игру</button>
                    </form>
                </div>

                <!-- Управление играми -->
                <div class="admin-section">
                    <h3>Управление играми</h3>
                    <div class="game-list">
                        <?php foreach ($games as $game): ?>
                            <div class="game-card-admin">
                                <div class="game-info-admin">
                                    <h4><?php echo htmlspecialchars($game['title']); ?></h4>
                                    <div class="game-price-admin">
                                        <?php echo number_format($game['price'], 0, ',', ' '); ?> ₽ • 
                                        <?php echo htmlspecialchars($game['category_name']); ?> • 
                                        В наличии: <?php echo $game['stock']; ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="edit_game.php?id=<?php echo $game['id']; ?>" class="btn btn-warning">
                                        Редактировать
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                                        <button type="submit" name="delete_game" class="btn btn-danger"
                                                onclick="return confirm('Вы уверены, что хотите удалить игру <?php echo htmlspecialchars($game['title']); ?>?')">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
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