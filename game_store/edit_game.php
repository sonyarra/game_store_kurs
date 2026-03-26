<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$game_id = (int)$_GET['id'];
$game = getGameById($game_id);
$categories = getCategories();

if (!$game) {
    header('Location: admin.php');
    exit;
}

$success = '';
$error = '';

// Обновление игры
// Обновление игры с изображением
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_game'])) {
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
        if (updateGame($game_id, $title, $description, $price, $category_id, $developer, $release_date, $stock, $image_filename)) {
            $success = 'Игра успешно обновлена';
            $game = getGameById($game_id);
        } else {
            $error = 'Ошибка при обновлении игры';
        }
    }
} 

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование игры - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <nav class="nav-links">
                <a href="admin.php" class="btn btn-outline">Назад в админ-панель</a>
                <a href="logout.php" class="btn btn-danger">Выйти</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="auth-container" style="max-width: 600px;">
                <div class="auth-header">
                    <h1>Редактирование игры</h1>
                    <p><?php echo htmlspecialchars($game['title']); ?></p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                     <div class="form-group">
        <label for="game_image">Изображение игры</label>
        <?php if (!empty($game['image_url']) && $game['image_url'] != 'default_game.jpg'): ?>
            <div style="margin-bottom: 1rem;">
                <img src="<?php echo getGameImage($game['image_url']); ?>" 
                     alt="Текущее изображение" 
                     style="max-width: 200px; border-radius: 8px;">
                <br>
                <small>Текущее изображение</small>
            </div>
        <?php endif; ?>
        <input type="file" id="game_image" name="game_image" class="form-control" 
               accept="image/jpeg,image/png,image/gif">
        <small style="color: var(--gray);">Оставьте пустым, чтобы сохранить текущее изображение</small>
    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="title">Название игры *</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($game['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Цена (₽) *</label>
                            <input type="number" id="price" name="price" class="form-control" 
                                   value="<?php echo $game['price']; ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($game['description']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="category_id">Категория</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category['id'] == $game['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="developer">Разработчик</label>
                            <input type="text" id="developer" name="developer" class="form-control" 
                                   value="<?php echo htmlspecialchars($game['developer']); ?>">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="release_date">Дата выпуска</label>
                            <input type="date" id="release_date" name="release_date" class="form-control" 
                                   value="<?php echo $game['release_date']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="stock">Количество на складе</label>
                            <input type="number" id="stock" name="stock" class="form-control" 
                                   value="<?php echo $game['stock']; ?>" min="0">
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="update_game" class="btn btn-success" style="flex: 1;">
                            Сохранить изменения
                        </button>
                        <a href="admin.php" class="btn btn-outline">Отмена</a>
                    </div>
                </form>
                                </form>
            </div>
        </div>
    </main>
</body>
</html>