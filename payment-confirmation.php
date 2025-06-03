<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
  header("Location: login.php");
  exit;
}

// Check if order ID is set in session
if (!isset($_SESSION['order_id'])) {
  header("Location: menu.php");
  exit;
}

$order_id = $_SESSION['order_id'];

// Get order details
$order = getOrderDetails($order_id);
$order_items = getOrderItems($order_id);

// Clear order ID from session
unset($_SESSION['order_id']);
?>

<?php include 'includes/header.php'; ?>

<div class="confirmation-page">
  <div class="confirmation-content">
    <div class="confirmation-header">
      <div class="confirmation-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1>Thank You for Your Order!</h1>
      <p>Your order has been placed successfully.</p>
    </div>
    
    <div class="order-details">
      <div class="order-info">
        <h2>Order Information</h2>
        
        <div class="info-group">
          <div class="info-item">
            <span class="label">Order Number:</span>
            <span class="value">#<?php echo $order['id']; ?></span>
          </div>
          
          <div class="info-item">
            <span class="label">Order Date:</span>
            <span class="value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
          </div>
          
          <div class="info-item">
            <span class="label">Delivery Address:</span>
            <span class="value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
          </div>
          
          <div class="info-item">
            <span class="label">Scheduled Delivery:</span>
            <span class="value"><?php echo date('F j, Y, g:i a', strtotime($order['scheduled_time'])); ?></span>
          </div>
          
          <div class="info-item">
            <span class="label">Payment Method:</span>
            <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
          </div>
          
          <div class="info-item">
            <span class="label">Payment Status:</span>
            <span class="value status-badge <?php echo strtolower($order['payment_status']); ?>">
              <?php echo htmlspecialchars($order['payment_status']); ?>
            </span>
          </div>
          
          <div class="info-item">
            <span class="label">Order Status:</span>
            <span class="value status-badge <?php echo strtolower($order['order_status']); ?>">
              <?php echo htmlspecialchars($order['order_status']); ?>
            </span>
          </div>
        </div>
      </div>
      
      <div class="order-summary">
        <h2>Order Summary</h2>
        
        <div class="ordered-items">
          <?php foreach ($order_items as $item): ?>
            <div class="ordered-item">
              <div class="item-info">
                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
              </div>
              <span class="item-price"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="order-total">
          <span>Total</span>
          <span><?php echo formatCurrency($order['total_price']); ?></span>
        </div>
      </div>
    </div>
    
    <div class="confirmation-steps">
      <h2>What Happens Next?</h2>
      
      <div class="steps">
        <div class="step completed">
          <div class="step-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="step-content">
            <h3>Order Confirmed</h3>
            <p>Your order has been received and confirmed.</p>
          </div>
        </div>
        
        <div class="step">
          <div class="step-icon">
            <i class="fas fa-utensils"></i>
          </div>
          <div class="step-content">
            <h3>Preparing</h3>
            <p>Our chefs are preparing your delicious meal.</p>
          </div>
        </div>
        
        <div class="step">
          <div class="step-icon">
            <i class="fas fa-motorcycle"></i>
          </div>
          <div class="step-content">
            <h3>Out for Delivery</h3>
            <p>Your food is on the way to your location.</p>
          </div>
        </div>
        
        <div class="step">
          <div class="step-icon">
            <i class="fas fa-home"></i>
          </div>
          <div class="step-content">
            <h3>Delivered</h3>
            <p>Your food has been delivered. Enjoy your meal!</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="confirmation-actions">
      <a href="order-status.php" class="btn btn-primary">
        <i class="fas fa-truck"></i> Track Order
      </a>
      <a href="menu.php" class="btn btn-outline">
        <i class="fas fa-utensils"></i> Continue Shopping
      </a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>