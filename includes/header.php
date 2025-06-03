<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check language preference
$languages = ['en' => 'English', 'ta' => 'தமிழ்'];
$current_lang = $_SESSION['lang'] ?? 'en';

// Language translations - simplified version
$translations = [
  'en' => [
    'home' => 'Home',
    'menu' => 'Menu',
    'cart' => 'Cart',
    'orders' => 'My Orders',
    'login' => 'Login',
    'register' => 'Register',
    'logout' => 'Logout',
    'account' => 'My Account',
    'admin' => 'Admin Dashboard',
    'search' => 'Search for food...',
    'items_in_cart' => 'items in cart',
  ],
  'ta' => [
    'home' => 'முகப்பு',
    'menu' => 'உணவு பட்டியல்',
    'cart' => 'கூடை',
    'orders' => 'எனது ஆர்டர்கள்',
    'login' => 'உள்நுழைய',
    'register' => 'பதிவு செய்ய',
    'logout' => 'வெளியேறு',
    'account' => 'எனது கணக்கு',
    'admin' => 'நிர்வாகி டாஷ்போர்டு',
    'search' => 'உணவைத் தேடுங்கள்...',
    'items_in_cart' => 'பொருட்கள் கூடையில்',
  ]
];

// Function to get translated text
function t($key) {
  global $translations, $current_lang;
  return $translations[$current_lang][$key] ?? $key;
}

// Change language if requested
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
  $_SESSION['lang'] = $_GET['lang'];
  // Redirect to the same page without the lang parameter
  $redirect = strtok($_SERVER['REQUEST_URI'], '?');
  header("Location: $redirect");
  exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MealMagic - Sri Lankan Food Delivery</title>
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="/assets/css/styles.css">
  
  <!-- Check for dark mode preference -->
  <script>
    // Check for saved dark mode preference or use system preference
    if (localStorage.getItem('darkMode') === 'enabled' || 
        (localStorage.getItem('darkMode') === null && 
         window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark-mode');
    }
  </script>
</head>
<body>
  <header class="header">
    <div class="container">
      <div class="header-content">
        <div class="logo">
          <a href="index.php">
            <span class="logo-icon"><i class="fas fa-utensils"></i></span>
            <span class="logo-text">MealMagic</span>
          </a>
        </div>
        
        <div class="search-bar">
          <form action="menu.php" method="get">
            <input type="text" name="search" placeholder="<?php echo t('search'); ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
          </form>
        </div>
        
        <nav class="main-nav">
          <button class="mobile-menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
          </button>
          
          <ul class="nav-links">
            <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
              <a href="index.php"><?php echo t('home'); ?></a>
            </li>
            <li class="<?php echo $current_page == 'menu.php' ? 'active' : ''; ?>">
              <a href="menu.php"><?php echo t('menu'); ?></a>
            </li>
            <?php if (isLoggedIn()): ?>
              <li class="<?php echo $current_page == 'cart.php' ? 'active' : ''; ?>">
                <a href="cart.php">
                  <?php echo t('cart'); ?>
                  <?php if (getCartCount() > 0): ?>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                  <?php endif; ?>
                </a>
              </li>
              <li class="<?php echo $current_page == 'order-status.php' ? 'active' : ''; ?>">
                <a href="order-status.php"><?php echo t('orders'); ?></a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
        
        <div class="header-right">
          <div class="theme-toggle">
            <button id="theme-toggle-btn" aria-label="Toggle dark mode">
              <i class="fas fa-moon dark-icon"></i>
              <i class="fas fa-sun light-icon"></i>
            </button>
          </div>
          
          <div class="lang-selector">
            <select id="lang-select" aria-label="Select language">
              <?php foreach ($languages as $code => $name): ?>
                <option value="<?php echo $code; ?>" <?php echo $current_lang == $code ? 'selected' : ''; ?>>
                  <?php echo $name; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="user-menu">
            <?php if (isLoggedIn()): ?>
              <div class="dropdown">
                <button class="dropdown-toggle">
                  <i class="fas fa-user-circle"></i>
                  <span><?php echo $_SESSION['user_name']; ?></span>
                </button>
                <div class="dropdown-menu">
                  <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php"><?php echo t('admin'); ?></a>
                  <?php endif; ?>
                  <a href="logout.php"><?php echo t('logout'); ?></a>
                </div>
              </div>
            <?php else: ?>
              <a href="login.php" class="btn btn-outline"><?php echo t('login'); ?></a>
              <a href="register.php" class="btn btn-primary"><?php echo t('register'); ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </header>
  
  <main class="main-content">
    <div class="container">