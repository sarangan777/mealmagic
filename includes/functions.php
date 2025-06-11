<?php
// Authentication Functions
function registerUser($name, $email, $password) {
    global $conn;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return false; // Email already exists
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'customer')");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    
    return $stmt->execute();
}

function loginUser($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return false;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($password, $user['password'])) {
        return false;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function logout() {
    session_unset();
    session_destroy();
}

// Cart Functions
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function addToCart($item_id, $name, $price, $quantity = 1) {
    initCart();
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = [
            'id' => $item_id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity
        ];
    }
}

function updateCartItem($item_id, $quantity) {
    if (isset($_SESSION['cart'][$item_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$item_id]['quantity'] = $quantity;
        } else {
            removeFromCart($item_id);
        }
    }
}

function removeFromCart($item_id) {
    if (isset($_SESSION['cart'][$item_id])) {
        unset($_SESSION['cart'][$item_id]);
    }
}

function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

function getCartCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function clearCart() {
    $_SESSION['cart'] = [];
}

// Menu Functions
function getCategories() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFoodItems($category_id = null) {
    global $conn;
    
    if ($category_id) {
        $stmt = $conn->prepare("SELECT f.*, 
                                (SELECT AVG(rating) FROM feedback WHERE food_item_id = f.id) as avg_rating,
                                (SELECT COUNT(*) FROM feedback WHERE food_item_id = f.id) as rating_count
                                FROM food_items f 
                                WHERE f.category_id = :category_id 
                                ORDER BY f.name");
        $stmt->bindParam(':category_id', $category_id);
    } else {
        $stmt = $conn->prepare("SELECT f.*, 
                                (SELECT AVG(rating) FROM feedback WHERE food_item_id = f.id) as avg_rating,
                                (SELECT COUNT(*) FROM feedback WHERE food_item_id = f.id) as rating_count
                                FROM food_items f 
                                ORDER BY f.name");
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFoodItem($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT f.*, 
                            (SELECT AVG(rating) FROM feedback WHERE food_item_id = f.id) as avg_rating,
                            (SELECT COUNT(*) FROM feedback WHERE food_item_id = f.id) as rating_count,
                            c.name as category_name
                            FROM food_items f
                            JOIN categories c ON f.category_id = c.id
                            WHERE f.id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Order Functions
function createOrder($user_id, $total_price, $delivery_address, $scheduled_time) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, delivery_address, order_status, scheduled_time, created_at) 
                               VALUES (:user_id, :total_price, :delivery_address, 'Confirmed', :scheduled_time, NOW())");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':total_price', $total_price);
        $stmt->bindParam(':delivery_address', $delivery_address);
        $stmt->bindParam(':scheduled_time', $scheduled_time);
        $stmt->execute();
        
        $order_id = $conn->lastInsertId();
        
        // Add order items
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, food_item_id, quantity, price) 
                                   VALUES (:order_id, :food_item_id, :quantity, :price)");
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':food_item_id', $item['id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
            
            // Update stock
            $stmt = $conn->prepare("UPDATE food_items SET stock = stock - :quantity WHERE id = :id");
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':id', $item['id']);
            $stmt->execute();
        }
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

function createPayment($order_id, $payment_method) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, paid_at) 
                           VALUES (:order_id, :payment_method, 'Completed', NOW())");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':payment_method', $payment_method);
    
    return $stmt->execute();
}

function getUserOrders($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, p.payment_method, p.payment_status 
                           FROM orders o
                           LEFT JOIN payments p ON o.id = p.order_id
                           WHERE o.user_id = :user_id
                           ORDER BY o.created_at DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderItems($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT oi.*, f.name, f.image 
                           FROM order_items oi
                           JOIN food_items f ON oi.food_item_id = f.id
                           WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, p.payment_method, p.payment_status, u.name as user_name, u.email as user_email
                           FROM orders o
                           LEFT JOIN payments p ON o.id = p.order_id
                           JOIN users u ON o.user_id = u.id
                           WHERE o.id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Feedback Functions
function addFeedback($user_id, $food_item_id, $rating, $comment) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, food_item_id, rating, comment, created_at) 
                           VALUES (:user_id, :food_item_id, :rating, :comment, NOW())");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':food_item_id', $food_item_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':comment', $comment);
    
    return $stmt->execute();
}

function getFeedbackForItem($food_item_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT f.*, u.name as user_name 
                           FROM feedback f
                           JOIN users u ON f.user_id = u.id
                           WHERE f.food_item_id = :food_item_id
                           ORDER BY f.created_at DESC");
    $stmt->bindParam(':food_item_id', $food_item_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper Functions
function displayStars($rating) {
    $output = '';
    $full_stars = floor($rating);
    $half_star = $rating - $full_stars >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
    
    for ($i = 0; $i < $full_stars; $i++) {
        $output .= '★';
    }
    
    if ($half_star) {
        $output .= '☆';
    }
    
    for ($i = 0; $i < $empty_stars; $i++) {
        $output .= '☆';
    }
    
    return $output;
}

function formatCurrency($amount) {
    return 'Rs. ' . number_format($amount, 2);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Admin Functions
function getAllOrders() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email, 
                           p.payment_method, p.payment_status
                           FROM orders o
                           JOIN users u ON o.user_id = u.id
                           LEFT JOIN payments p ON o.id = p.order_id
                           ORDER BY o.created_at DESC");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateOrderStatus($order_id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    
    return $stmt->execute();
}

function getMostOrderedItems($limit = 5) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT f.id, f.name, f.image, f.price, SUM(oi.quantity) as total_ordered
                           FROM order_items oi
                           JOIN food_items f ON oi.food_item_id = f.id
                           GROUP BY f.id
                           ORDER BY total_ordered DESC
                           LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalSales() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT SUM(total_price) as total FROM orders");
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

function getTotalOrders() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

function getTotalCustomers() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

// CRUD for Categories
function addCategory($name, $description) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    
    return $stmt->execute();
}

function updateCategory($id, $name, $description) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

function deleteCategory($id) {
    global $conn;
    
    // Check if category has food items
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM food_items WHERE category_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        return false; // Cannot delete category with food items
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

function getCategory($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// CRUD for Food Items
function addFoodItem($name, $description, $price, $image, $stock, $category_id) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO food_items (name, description, price, image, stock, category_id) 
                           VALUES (:name, :description, :price, :image, :stock, :category_id)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':category_id', $category_id);
    
    return $stmt->execute();
}

function updateFoodItem($id, $name, $description, $price, $image, $stock, $category_id) {
    global $conn;
    
    $image_sql = $image ? ", image = :image" : "";
    
    $stmt = $conn->prepare("UPDATE food_items SET 
                           name = :name, 
                           description = :description, 
                           price = :price, 
                           stock = :stock, 
                           category_id = :category_id" . $image_sql . "
                           WHERE id = :id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':id', $id);
    
    if ($image) {
        $stmt->bindParam(':image', $image);
    }
    
    return $stmt->execute();
}

function deleteFoodItem($id) {
    global $conn;
    
    // Check if food item has orders
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE food_item_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        return false; // Cannot delete food item with orders
    }
    
    $stmt = $conn->prepare("DELETE FROM food_items WHERE id = :id");
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}
?>