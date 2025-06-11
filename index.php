<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get featured items (most ordered)
$featured_items = getMostOrderedItems(4);

// Get all categories
$categories = getCategories();

include 'includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1>Delicious Sri Lankan Cuisine <br>Delivered to Your Door</h1>
        <p>Experience the authentic flavors of Sri Lanka with our carefully prepared dishes</p>
        <div class="hero-buttons">
            <a href="menu.php" class="btn btn-primary">Explore Menu</a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-outline">Sign Up</a>
            <?php endif; ?>
        </div>
        
        <div class="hero-cities">
            <p>Available in: 
                <span>Colombo</span> • 
                <span>Kandy</span> • 
                <span>Galle</span> • 
                <span>Jaffna</span> • 
                <span>Batticaloa</span> • 
                <span>Anuradhapura</span>
            </p>
        </div>
    </div>
</div>

<section class="how-it-works">
    <div class="section-header">
        <h2>How It Works</h2>
        <p>Order your favorite Sri Lankan dishes in just a few simple steps</p>
    </div>
    
    <div class="steps">
        <div class="step">
            <div class="step-icon">🍽️</div>
            <h3>Choose Your Food</h3>
            <p>Browse our menu and select from a variety of authentic Sri Lankan dishes</p>
        </div>
        
        <div class="step">
            <div class="step-icon">🛒</div>
            <h3>Add to Cart</h3>
            <p>Customize your order and add your favorite items to your cart</p>
        </div>
        
        <div class="step">
            <div class="step-icon">⏰</div>
            <h3>Schedule Delivery</h3>
            <p>Choose when you want your food delivered - now or later</p>
        </div>
        
        <div class="step">
            <div class="step-icon">🏍️</div>
            <h3>Enjoy Your Meal</h3>
            <p>We'll deliver your food fresh and hot right to your doorstep</p>
        </div>
    </div>
</section>

<section class="featured-items">
    <div class="section-header">
        <h2>Most Popular Dishes</h2>
        <p>Our customers' favorites, loved for their authentic taste and quality</p>
    </div>
    
    <div class="food-grid">
        <?php foreach ($featured_items as $item): ?>
            <div class="food-card">
                <div class="food-image">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php if (isset($item['stock']) && $item['stock'] <= 5): ?>
                        <span class="stock-warning">Only <?php echo $item['stock']; ?> left!</span>
                    <?php endif; ?>
                </div>
                
                <div class="food-info">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    
                    <div class="rating">
                        <?php echo displayStars($item['avg_rating'] ?? 0); ?>
                        <span>(<?php echo $item['rating_count'] ?? 0; ?>)</span>
                    </div>
                    
                    <div class="food-footer">
                        <span class="price"><?php echo formatCurrency($item['price'] ?? 0); ?></span>
                        <a href="menu.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="view-all">
        <a href="menu.php" class="btn btn-outline">View All Menu</a>
    </div>
</section>

<section class="categories">
    <div class="section-header">
        <h2>Explore By Category</h2>
        <p>Discover our wide range of authentic Sri Lankan dishes</p>
    </div>
    
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a href="menu.php?category=<?php echo $category['id']; ?>" class="category-card">
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>