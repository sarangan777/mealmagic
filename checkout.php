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

// Redirect if cart is empty
if (count(getCartItems()) === 0) {
  header("Location: cart.php");
  exit;
}

// Get cart details
$cart_items = getCartItems();
$cart_total = getCartTotal();
$delivery_fee = $cart_total >= 2000 ? 0 : 150;
$final_total = $cart_total + $delivery_fee;

// Get current user info
$user_id = $_SESSION['user_id'];

// Define available cities
$cities = ['Colombo', 'Kandy', 'Jaffna', 'Galle', 'Batticaloa', 'Anuradhapura'];

// Define payment methods
$payment_methods = ['Cash on Delivery', 'Demo Payment'];

// Process checkout
$address_err = $city_err = $payment_method_err = $scheduled_time_err = '';
$checkout_successful = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate delivery address
  if (empty(trim($_POST["address"]))) {
    $address_err = "Please enter your delivery address.";
  } else {
    $delivery_address = sanitizeInput($_POST["address"]);
  }
  
  // Validate city
  if (empty($_POST["city"]) || !in_array($_POST["city"], $cities)) {
    $city_err = "Please select a valid delivery city.";
  } else {
    $delivery_city = sanitizeInput($_POST["city"]);
  }
  
  // Validate payment method
  if (empty($_POST["payment_method"]) || !in_array($_POST["payment_method"], $payment_methods)) {
    $payment_method_err = "Please select a valid payment method.";
  } else {
    $payment_method = sanitizeInput($_POST["payment_method"]);
  }
  
  // Validate scheduled time
  if (empty($_POST["scheduled_time"])) {
    $scheduled_time_err = "Please select a delivery time.";
  } else {
    $scheduled_time = sanitizeInput($_POST["scheduled_time"]);
    
    // Check if scheduled time is in the future
    $current_time = time();
    $scheduled_timestamp = strtotime($scheduled_time);
    
    if ($scheduled_timestamp <= $current_time) {
      $scheduled_time_err = "Scheduled time must be in the future.";
    }
  }
  
  // Complete order if no errors
  if (empty($address_err) && empty($city_err) && empty($payment_method_err) && empty($scheduled_time_err)) {
    // Combine address and city
    $full_address = $delivery_address . ', ' . $delivery_city;
    
    // Create order
    $order_id = createOrder($user_id, $final_total, $full_address, $scheduled_time);
    
    if ($order_id) {
      // Create payment record
      if (createPayment($order_id, $payment_method)) {
        // Clear cart after successful order
        clearCart();
        
        // Redirect to payment confirmation page
        $_SESSION['order_id'] = $order_id;
        header("Location: payment-confirmation.php");
        exit;
      }
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<div class="checkout-page">
  <div class="page-header">
    <h1>Checkout</h1>
    <p>Complete your order</p>
  </div>
  
  <div class="checkout-content">
    <div class="checkout-form">
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-section">
          <h2>Delivery Details</h2>
          
          <div class="form-group <?php echo (!empty($address_err)) ? 'has-error' : ''; ?>">
            <label for="address">Delivery Address</label>
            <input type="text" id="address" name="address" value="<?php echo $delivery_address ?? ''; ?>" required>
            <span class="error-text"><?php echo $address_err; ?></span>
          </div>
          
          <div class="form-group <?php echo (!empty($city_err)) ? 'has-error' : ''; ?>">
            <label for="city">City</label>
            <select id="city" name="city" required>
              <option value="">Select city</option>
              <?php foreach ($cities as $city): ?>
                <option value="<?php echo $city; ?>" <?php echo (isset($delivery_city) && $delivery_city == $city) ? 'selected' : ''; ?>>
                  <?php echo $city; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="error-text"><?php echo $city_err; ?></span>
          </div>
          
          <div class="form-group <?php echo (!empty($scheduled_time_err)) ? 'has-error' : ''; ?>">
            <label for="scheduled_time">Scheduled Delivery Time</label>
            <input type="datetime-local" id="scheduled_time" name="scheduled_time" 
                   value="<?php echo $scheduled_time ?? ''; ?>" required
                   min="<?php echo date('Y-m-d\TH:i', time() + 1800); ?>">
            <span class="error-text"><?php echo $scheduled_time_err; ?></span>
            <small class="form-hint">Delivery available from 30 minutes from now</small>
          </div>
        </div>
        
        <div class="form-section">
          <h2>Payment Method</h2>
          
          <div class="form-group payment-options <?php echo (!empty($payment_method_err)) ? 'has-error' : ''; ?>">
            <?php foreach ($payment_methods as $method): ?>
              <div class="payment-option">
                <input type="radio" id="payment_<?php echo strtolower(str_replace(' ', '_', $method)); ?>" 
                       name="payment_method" value="<?php echo $method; ?>" 
                       <?php echo (isset($payment_method) && $payment_method == $method) ? 'checked' : ''; ?> required>
                <label for="payment_<?php echo strtolower(str_replace(' ', '_', $method)); ?>">
                  <?php if ($method == 'Cash on Delivery'): ?>
                    <i class="fas fa-money-bill-wave"></i>
                  <?php else: ?>
                    <i class="fas fa-credit-card"></i>
                  <?php endif; ?>
                  <span><?php echo $method; ?></span>
                </label>
              </div>
            <?php endforeach; ?>
            <span class="error-text"><?php echo $payment_method_err; ?></span>
            <small class="form-hint">Note: No real payment will be processed for this demo.</small>
          </div>
        </div>
        
        <div class="checkout-actions">
          <a href="cart.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Cart
          </a>
          <button type="submit" class="btn btn-primary">
            Complete Order <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>
    
    <div class="order-summary">
      <h2>Order Summary</h2>
      
      <div class="summary-items">
        <?php foreach ($cart_items as $item): ?>
          <div class="summary-item">
            <div class="item-info">
              <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
              <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
            </div>
            <span class="item-price"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      
      <div class="summary-totals">
        <div class="summary-line">
          <span>Subtotal</span>
          <span><?php echo formatCurrency($cart_total); ?></span>
        </div>
        
        <div class="summary-line">
          <span>Delivery Fee</span>
          <span><?php echo formatCurrency($delivery_fee); ?></span>
        </div>
        
        <?php if ($cart_total >= 2000): ?>
          <div class="summary-line discount">
            <span>Free Delivery</span>
            <span>-<?php echo formatCurrency(150); ?></span>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="summary-total">
        <span>Total</span>
        <span><?php echo formatCurrency($final_total); ?></span>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>