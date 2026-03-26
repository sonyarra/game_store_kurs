<?php
require_once 'config.php';

// Регистрация пользователя
function registerUser($username, $email, $password, $full_name = '') {
    global $pdo;
    
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$username, $email, $hashed_password, $full_name]);
    } catch (PDOException $e) {
        return false;
    }
}

// Авторизация пользователя
function loginUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = true");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

// Получение всех игр
function getGames($category_id = null) {
    global $pdo;
    
    try {
        $sql = "SELECT g.*, c.name as category_name FROM games g 
                LEFT JOIN categories c ON g.category_id = c.id 
                WHERE g.is_active = true";
        
        if ($category_id) {
            $sql .= " AND g.category_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_id]);
        } else {
            $stmt = $pdo->query($sql);
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Получение игры по ID
function getGameById($game_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT g.*, c.name as category_name 
            FROM games g 
            LEFT JOIN categories c ON g.category_id = c.id 
            WHERE g.id = ?
        ");
        $stmt->execute([$game_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

// Получение всех категорий
function getCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Добавление в корзину
function addToCart($user_id, $game_id, $quantity = 1) {
    global $pdo;
    
    try {
        // Проверяем, есть ли уже игра в корзине
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND game_id = ?");
        $stmt->execute([$user_id, $game_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Обновляем количество
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND game_id = ?");
            return $stmt->execute([$quantity, $user_id, $game_id]);
        } else {
            // Добавляем новую запись
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, game_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$user_id, $game_id, $quantity]);
        }
    } catch (PDOException $e) {
        return false;
    }
}

// Получение корзины пользователя
function getCart($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, g.title, g.price, g.image_url, (g.price * c.quantity) as total_price
            FROM cart c 
            JOIN games g ON c.game_id = g.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Удаление из корзины
function removeFromCart($user_id, $game_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND game_id = ?");
        return $stmt->execute([$user_id, $game_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Получение количества товаров в корзине
function getCartCount($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['total'] ?: 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Получение всех пользователей (для админа)
function getAllUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Удаление пользователя
function deleteUser($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        return false;
    }
}


// Удаление игры (для админа)
function deleteGame($game_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
        return $stmt->execute([$game_id]);
    } catch (PDOException $e) {
        return false;
    }
}

// Функция для загрузки изображения
function uploadImage($file) {
    $target_dir = "uploads/";
    
    // Создаем папку uploads если ее нет
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Проверка является ли файл изображением
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Проверка размера файла (максимум 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Разрешенные форматы
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        return false;
    }
    
    // Пытаемся загрузить файл
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $filename;
    }
    
    return false;
}

// Обновленная функция добавления игры с изображением
function addGame($title, $description, $price, $category_id, $developer, $release_date, $stock, $image = null) {
    global $pdo;
    
    try {
        $image_url = $image ?: 'default_game.jpg';
        
        $stmt = $pdo->prepare("
            INSERT INTO games (title, description, price, category_id, developer, release_date, stock, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$title, $description, $price, $category_id, $developer, $release_date, $stock, $image_url]);
    } catch (PDOException $e) {
        return false;
    }
}


// Обновленная функция обновления игры с изображением
function updateGame($game_id, $title, $description, $price, $category_id, $developer, $release_date, $stock, $image = null) {
    global $pdo;
    
    try {
        if ($image) {
            $stmt = $pdo->prepare("
                UPDATE games 
                SET title = ?, description = ?, price = ?, category_id = ?, developer = ?, 
                    release_date = ?, stock = ?, image_url = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$title, $description, $price, $category_id, $developer, $release_date, $stock, $image, $game_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE games 
                SET title = ?, description = ?, price = ?, category_id = ?, developer = ?, 
                    release_date = ?, stock = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$title, $description, $price, $category_id, $developer, $release_date, $stock, $game_id]);
        }
    } catch (PDOException $e) {
        return false;
    }
}

// Функция для получения пути к изображению
function getGameImage($image_url) {
    if (file_exists("uploads/" . $image_url) && !empty($image_url)) {
        return "uploads/" . $image_url;
    }
    return "assets/default_game.jpg"; // Заглушка если изображения нет
}

// Проверки авторизации
function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Аренда игры
function rentGame($user_id, $game_id, $start_date, $end_date) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rentals (user_id, game_id, start_date, end_date, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        return $stmt->execute([$user_id, $game_id, $start_date, $end_date]);
    } catch (PDOException $e) {
        return false;
    }
}

// Получение списка аренд пользователя
function getUserRentals($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, g.title, g.image_url 
            FROM rentals r
            JOIN games g ON r.game_id = g.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Удаление аренды (отмена)
function deleteRental($rental_id, $user_id) {
    global $pdo;
    try {
        // Удаляем только если аренда принадлежит пользователю и ещё активна (можно удалять любую, но лучше активную)
        $stmt = $pdo->prepare("DELETE FROM rentals WHERE id = ? AND user_id = ?");
        return $stmt->execute([$rental_id, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}
?>