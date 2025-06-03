-- Create database
CREATE DATABASE IF NOT EXISTS mealmagic;
USE mealmagic;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Food items table
CREATE TABLE food_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    order_status ENUM('Confirmed', 'Preparing', 'Out for Delivery', 'Delivered') DEFAULT 'Confirmed',
    scheduled_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    food_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (food_item_id) REFERENCES food_items(id)
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_method ENUM('Cash on Delivery', 'Demo Payment') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    food_item_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (food_item_id) REFERENCES food_items(id)
);

-- Insert sample data

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@mealmagic.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Categories
INSERT INTO categories (name, description) VALUES
('Rice & Curry', 'Traditional Sri Lankan rice and curry dishes'),
('Kottu', 'Popular street food made with roti and vegetables'),
('Hoppers', 'Traditional Sri Lankan hoppers and string hoppers'),
('Desserts', 'Sweet treats and traditional desserts'),
('Beverages', 'Traditional Sri Lankan drinks');

-- Food items
INSERT INTO food_items (name, description, price, image, stock, category_id) VALUES
('Chicken Rice & Curry', 'Traditional Sri Lankan chicken curry with rice and 3 vegetables', 450.00, 'assets/images/food/chicken_rice_curry.jpg', 50, 1),
('Chicken Kottu', 'Spicy chicken kottu with vegetables and eggs', 400.00, 'assets/images/food/chicken_kottu.jpg', 40, 2),
('String Hoppers', 'Fresh string hoppers (15 pieces) with curry sauce', 250.00, 'assets/images/food/string_hoppers.jpg', 60, 3),
('Egg Hoppers', 'Crispy hoppers with egg (3 pieces)', 200.00, 'assets/images/food/egg_hoppers.jpg', 45, 3),
('Watalappan', 'Traditional Sri Lankan coconut custard pudding', 150.00, 'assets/images/food/watalappan.jpg', 30, 4),
('Wood Apple Juice', 'Fresh wood apple juice with honey', 120.00, 'assets/images/food/woodapple_juice.jpg', 25, 5);

-- Create indexes
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_food_category ON food_items(category_id);
CREATE INDEX idx_order_user ON orders(user_id);
CREATE INDEX idx_order_status ON orders(order_status);
CREATE INDEX idx_payment_order ON payments(order_id);
CREATE INDEX idx_feedback_food ON feedback(food_item_id);