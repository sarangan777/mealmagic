<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
  header("Location: login.php");
  exit;
}

// Initialize cart
initCart();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Update quantity
  if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $item_id => $quantity) {
      updateCartItem($item_id, (int)$quantity);
    }
    // Redirect to avoid form resubmission
    header("Location: cart.php?updated=1");
    exit;
  }
  
  // Remove item
  if (isset($_POST['remove_item'])) {
    $item_id = (int)$_POST['item_id'];
    removeFromCart($item_id);
    // Redirect to avoid form resubmission
    header("Location: cart.php?removed=1");
    exit;
  }
  
  // Clear cart
  if (isset($_POST['clear_cart'])) {
    clearCart();
    // Redirect to avoid form resubmission
    header("Location: cart.php?cleared=1");
    exit;
  }
}

// Get cart items
$cart_items = getCartItems();
$cart_total = getCartTotal();
?>

<?php include 'includes/header.php'; ?>

<div class="cart-page">
  <div class="page-header">
    <h1>Your Cart</h1>
    <p>Review your items before checkout</p>
  </div>
  
  <?php if (isset($_GET['updated'])): ?>
    <div class="alert success">
      <p>Cart updated successfully.</p>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['removed'])): ?>
    <div class="alert success">
      <p>Item removed from cart.</p>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['cleared'])): ?>
    <div class="alert success">
      <p>Cart cleared successfully.</p>
    </div>
  <?php endif; ?>
  
  <?php if (empty($cart_items)): ?>
    <div class="empty-cart">
      <div class="empty-cart-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <h2>Your cart is empty</h2>
      <p>Looks like you haven't added any items to your cart yet.</p>
      <a href="menu.php" class="btn btn-primary">Browse Menu</a>
    </div>
  <?php else: ?>
    <div class="cart-content">
      <div class="cart-items">
        <form action="cart.php" method="post" id="cart-form">
          <div class="cart-header">
            <div class="cart-item-header item-info">Item</div>
            <div class="cart-item-header item-price">Price</div>
            <div class="cart-item-header item-quantity">Quantity</div>
            <div class="cart-item-header item-total">Total</div>
            <div class="cart-item-header item-actions">Actions</div>
          </div>
          
          <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
              <div class="item-info">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
              </div>
              
              <div class="item-price">
                <?php echo formatCurrency($item['price']); ?>
              </div>
              
              <div class="item-quantity">
                <div class="quantity-control">
                  <button type="button" class="quantity-btn minus" aria-label="Decrease quantity">-</button>
                  <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                         value="<?php echo $item['quantity']; ?>" min="1" max="10">
                  <button type="button" class="quantity-btn plus" aria-label="Increase quantity">+</button>
                </div>
              </div>
              
              <div class="item-total">
                <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
              </div>
              
              <div class="item-actions">
                <button type="submit" form="remove-form-<?php echo $item['id']; ?>" class="btn-icon" aria-label="Remove item">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
            
            <form id="remove-form-<?php echo $item['id']; ?>" action="cart.php" method="post">
              <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
              <input type="hidden" name="remove_item" value="1">
            </form>
          <?php endforeach; ?>
          
          <div class="cart-actions">
            <button type="submit" name="update_cart" class="btn btn-outline">
              <i class="fas fa-sync-alt"></i> Update Cart
            </button>
            <button type="submit" name="clear_cart" class="btn btn-outline">
              <i class="fas fa-trash"></i> Clear Cart
            </button>
          </div>
        </form>
      </div>
      
      <div class="cart-summary">
        <h2>Order Summary</h2>
        
        <div class="summary-item">
          <span>Subtotal</span>
          <span><?php echo formatCurrency($cart_total); ?></span>
        </div>
        
        <div class="summary-item">
          <span>Delivery Fee</span>
          <span><?php echo formatCurrency(150); ?></span>
        </div>
        
        <?php if ($cart_total >= 2000): ?>
          <div class="summary-item discount">
            <span>Delivery Fee Discount</span>
            <span>-<?php echo formatCurrency(150); ?></span>
          </div>
        <?php endif; ?>
        
        <div class="summary-total">
          <span>Total</span>
          <span><?php echo formatCurrency($cart_total + ($cart_total >= 2000 ? 0 : 150)); ?></span>
        </div>
        
        <div class="checkout-button">
          <a href="checkout.php" class="btn btn-primary btn-block">
            Proceed to Checkout
          </a>
        </div>
        
        <div class="continue-shopping">
          <a href="menu.php" class="btn-text">
            <i class="fas fa-arrow-left"></i> Continue Shopping
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>