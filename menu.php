<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get category ID from URL if provided
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Get item ID from URL if provided
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get search query if provided
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get categories
$categories = getCategories();

// Get food items based on filters
if ($item_id) {
  // Single item view
  $item = getFoodItem($item_id);
  $category_id = $item ? $item['category_id'] : null;
  $food_items = null;
} else {
  // List view
  $food_items = getFoodItems($category_id);
  $item = null;
  
  // Apply search filter if provided
  if (!empty($search_query)) {
    $filtered_items = [];
    foreach ($food_items as $food) {
      if (stripos($food['name'], $search_query) !== false || 
          stripos($food['description'], $search_query) !== false) {
        $filtered_items[] = $food;
      }
    }
    $food_items = $filtered_items;
  }
}

// Process add to cart action
if (isset($_POST['add_to_cart']) && isLoggedIn()) {
  $add_item_id = (int)$_POST['item_id'];
  $add_item_name = $_POST['item_name'];
  $add_item_price = (float)$_POST['item_price'];
  $add_quantity = (int)$_POST['quantity'];
  
  if ($add_quantity > 0) {
    addToCart($add_item_id, $add_item_name, $add_item_price, $add_quantity);
    
    // Redirect to avoid form resubmission on page refresh
    header("Location: menu.php" . ($category_id ? "?category=$category_id" : ""));
    exit;
  }
}

// Process add feedback
if (isset($_POST['add_feedback']) && isLoggedIn()) {
  $feedback_item_id = (int)$_POST['item_id'];
  $rating = (int)$_POST['rating'];
  $comment = sanitizeInput($_POST['comment']);
  
  if ($rating >= 1 && $rating <= 5) {
    addFeedback($_SESSION['user_id'], $feedback_item_id, $rating, $comment);
    
    // Redirect to avoid form resubmission
    header("Location: menu.php?id=$feedback_item_id&feedback=added");
    exit;
  }
}

// Get item feedback if viewing a single item
$item_feedback = $item ? getFeedbackForItem($item['id']) : null;
?>

<?php include 'includes/header.php'; ?>

<div class="menu-page">
  <div class="menu-header">
    <h1><?php echo $item ? htmlspecialchars($item['name']) : 'Our Menu'; ?></h1>
    
    <?php if (!$item): ?>
      <p>Explore our authentic Sri Lankan cuisine</p>
      
      <div class="category-filters">
        <a href="menu.php" class="category-filter <?php echo !$category_id ? 'active' : ''; ?>">All</a>
        <?php foreach ($categories as $category): ?>
          <a href="menu.php?category=<?php echo $category['id']; ?>" 
             class="category-filter <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($category['name']); ?>
          </a>
        <?php endforeach; ?>
      </div>
      
      <?php if (!empty($search_query)): ?>
        <div class="search-results">
          <p>Search results for: <strong><?php echo htmlspecialchars($search_query); ?></strong></p>
          <a href="menu.php<?php echo $category_id ? "?category=$category_id" : ""; ?>" class="clear-search">
            <i class="fas fa-times"></i> Clear search
          </a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  
  <?php if ($item): ?>
    <!-- Single item view -->
    <div class="item-detail">
      <div class="item-image">
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
        
        <?php if ($item['stock'] <= 5): ?>
          <span class="stock-warning">Only <?php echo $item['stock']; ?> left!</span>
        <?php endif; ?>
      </div>
      
      <div class="item-info">
        <div class="item-category">
          <a href="menu.php?category=<?php echo $item['category_id']; ?>">
            <?php echo htmlspecialchars($item['category_name']); ?>
          </a>
        </div>
        
        <h2><?php echo htmlspecialchars($item['name']); ?></h2>
        
        <div class="item-rating">
          <?php echo displayStars($item['avg_rating'] ?? 0); ?>
          <span>(<?php echo $item['rating_count'] ?? 0; ?> reviews)</span>
        </div>
        
        <div class="item-price">
          <span><?php echo formatCurrency($item['price']); ?></span>
        </div>
        
        <div class="item-description">
          <p><?php echo htmlspecialchars($item['description']); ?></p>
        </div>
        
        <?php if ($item['stock'] > 0): ?>
          <form action="menu.php?id=<?php echo $item['id']; ?>" method="post" class="add-to-cart-form">
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
            <input type="hidden" name="item_price" value="<?php echo $item['price']; ?>">
            
            <div class="quantity-control">
              <button type="button" class="quantity-btn minus" aria-label="Decrease quantity">-</button>
              <input type="number" name="quantity" value="1" min="1" max="<?php echo $item['stock']; ?>">
              <button type="button" class="quantity-btn plus" aria-label="Increase quantity">+</button>
            </div>
            
            <button type="submit" name="add_to_cart" class="btn btn-primary">
              <i class="fas fa-cart-plus"></i> Add to Cart
            </button>
          </form>
        <?php else: ?>
          <div class="out-of-stock">
            <p>Currently out of stock</p>
          </div>
        <?php endif; ?>
        
        <div class="item-actions">
          <a href="menu.php<?php echo $category_id ? "?category=$category_id" : ""; ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Menu
          </a>
        </div>
      </div>
    </div>
    
    <div class="item-feedback">
      <h3>Customer Reviews</h3>
      
      <?php if (isLoggedIn()): ?>
        <div class="feedback-form">
          <h4>Leave a Review</h4>
          <form action="menu.php?id=<?php echo $item['id']; ?>" method="post">
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            
            <div class="form-group">
              <label for="rating">Rating</label>
              <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required>
                <label for="star5" title="5 stars"></label>
                <input type="radio" id="star4" name="rating" value="4">
                <label for="star4" title="4 stars"></label>
                <input type="radio" id="star3" name="rating" value="3">
                <label for="star3" title="3 stars"></label>
                <input type="radio" id="star2" name="rating" value="2">
                <label for="star2" title="2 stars"></label>
                <input type="radio" id="star1" name="rating" value="1">
                <label for="star1" title="1 star"></label>
              </div>
            </div>
            
            <div class="form-group">
              <label for="comment">Comment</label>
              <textarea id="comment" name="comment" rows="3" required></textarea>
            </div>
            
            <button type="submit" name="add_feedback" class="btn btn-primary">Submit Review</button>
          </form>
        </div>
      <?php endif; ?>
      
      <div class="reviews-list">
        <?php if ($item_feedback && count($item_feedback) > 0): ?>
          <?php foreach ($item_feedback as $feedback): ?>
            <div class="review">
              <div class="review-header">
                <span class="reviewer-name"><?php echo htmlspecialchars($feedback['user_name']); ?></span>
                <span class="review-date"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></span>
              </div>
              
              <div class="review-rating">
                <?php echo displayStars($feedback['rating']); ?>
              </div>
              
              <div class="review-comment">
                <p><?php echo htmlspecialchars($feedback['comment']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="no-reviews">No reviews yet. Be the first to leave a review!</p>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <!-- Menu list view -->
    <?php if (empty($food_items)): ?>
      <div class="empty-state">
        <i class="fas fa-utensils"></i>
        <h3>No items found</h3>
        <p>Try another category or search term</p>
        <a href="menu.php" class="btn btn-primary">View All Menu</a>
      </div>
    <?php else: ?>
      <div class="food-grid">
        <?php foreach ($food_items as $food): ?>
          <div class="food-card">
            <div class="food-image">
              <img src="<?php echo htmlspecialchars($food['image']); ?>" alt="<?php echo htmlspecialchars($food['name']); ?>">
              
              <?php if ($food['stock'] <= 5 && $food['stock'] > 0): ?>
                <span class="stock-warning">Only <?php echo $food['stock']; ?> left!</span>
              <?php elseif ($food['stock'] <= 0): ?>
                <span class="out-of-stock-label">Out of Stock</span>
              <?php endif; ?>
            </div>
            
            <div class="food-info">
              <h3><?php echo htmlspecialchars($food['name']); ?></h3>
              
              <div class="rating">
                <?php echo displayStars($food['avg_rating'] ?? 0); ?>
                <span>(<?php echo $food['rating_count'] ?? 0; ?>)</span>
              </div>
              
              <p class="description"><?php echo htmlspecialchars(substr($food['description'], 0, 80)) . (strlen($food['description']) > 80 ? '...' : ''); ?></p>
              
              <div class="food-footer">
                <span class="price"><?php echo formatCurrency($food['price']); ?></span>
                
                <div class="food-actions">
                  <a href="menu.php?id=<?php echo $food['id']; ?>" class="btn btn-sm btn-outline">Details</a>
                  
                  <?php if ($food['stock'] > 0 && isLoggedIn()): ?>
                    <form action="menu.php<?php echo $category_id ? "?category=$category_id" : ""; ?>" method="post" class="quick-add">
                      <input type="hidden" name="item_id" value="<?php echo $food['id']; ?>">
                      <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($food['name']); ?>">
                      <input type="hidden" name="item_price" value="<?php echo $food['price']; ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" name="add_to_cart" class="btn btn-sm btn-primary">
                        <i class="fas fa-cart-plus"></i> Add
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>