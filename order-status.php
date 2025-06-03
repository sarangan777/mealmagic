<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Get user orders
$orders = getUserOrders($user_id);

// Reorder functionality
if (isset($_POST['reorder']) && isset($_POST['order_id'])) {
  $reorder_id = (int)$_POST['order_id'];
  
  // Get order items
  $reorder_items = getOrderItems($reorder_id);
  
  // Clear current cart
  clearCart();
  
  // Add items to cart
  foreach ($reorder_items as $item) {
    addToCart($item['food_item_id'], $item['name'], $item['price'], $item['quantity']);
  }
  
  // Redirect to cart
  header("Location: cart.php?reordered=1");
  exit;
}

// Get specific order details
$selected_order = null;
$selected_order_items = null;

if (isset($_GET['order_id'])) {
  $selected_order_id = (int)$_GET['order_id'];
  
  // Verify that the order belongs to the current user
  foreach ($orders as $order) {
    if ($order['id'] == $selected_order_id) {
      $selected_order = $order;
      $selected_order_items = getOrderItems($selected_order_id);
      break;
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<div class="orders-page">
  <div class="page-header">
    <h1>My Orders</h1>
    <p>Track and manage your orders</p>
  </div>
  
  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-shopping-bag"></i>
      </div>
      <h2>No Orders Yet</h2>
      <p>You haven't placed any orders yet. Start ordering delicious Sri Lankan cuisine!</p>
      <a href="menu.php" class="btn btn-primary">Browse Menu</a>
    </div>
  <?php else: ?>
    <div class="orders-content">
      <?php if ($selected_order): ?>
        <!-- Order details view -->
        <div class="order-details">
          <div class="details-header">
            <h2>Order #<?php echo $selected_order['id']; ?></h2>
            <div class="order-meta">
              <span class="order-date">
                <i class="far fa-calendar-alt"></i> 
                <?php echo date('F j, Y', strtotime($selected_order['created_at'])); ?>
              </span>
              <span class="order-time">
                <i class="far fa-clock"></i>
                <?php echo date('g:i a', strtotime($selected_order['created_at'])); ?>
              </span>
            </div>
          </div>
          
          <div class="status-timeline">
            <?php
            $statuses = ['Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
            $current_status = $selected_order['order_status'];
            $current_index = array_search($current_status, $statuses);
            ?>
            
            <div class="timeline">
              <?php foreach ($statuses as $index => $status): ?>
                <div class="timeline-step <?php echo $index <= $current_index ? 'completed' : ''; ?>">
                  <div class="step-icon">
                    <?php if ($index < $current_index): ?>
                      <i class="fas fa-check"></i>
                    <?php elseif ($index == $current_index): ?>
                      <?php if ($status == 'Confirmed'): ?>
                        <i class="fas fa-check-circle"></i>
                      <?php elseif ($status == 'Preparing'): ?>
                        <i class="fas fa-utensils"></i>
                      <?php elseif ($status == 'Out for Delivery'): ?>
                        <i class="fas fa-motorcycle"></i>
                      <?php elseif ($status == 'Delivered'): ?>
                        <i class="fas fa-home"></i>
                      <?php endif; ?>
                    <?php else: ?>
                      <?php if ($status == 'Confirmed'): ?>
                        <i class="far fa-check-circle"></i>
                      <?php elseif ($status == 'Preparing'): ?>
                        <i class="far fa-utensils"></i>
                      <?php elseif ($status == 'Out for Delivery'): ?>
                        <i class="far fa-motorcycle"></i>
                      <?php elseif ($status == 'Delivered'): ?>
                        <i class="far fa-home"></i>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                  <div class="step-label"><?php echo $status; ?></div>
                </div>
                
                <?php if ($index < count($statuses) - 1): ?>
                  <div class="timeline-connector <?php echo $index < $current_index ? 'completed' : ''; ?>"></div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="order-info-section">
            <div class="order-info">
              <h3>Delivery Information</h3>
              
              <div class="info-group">
                <div class="info-item">
                  <span class="label">Address:</span>
                  <span class="value"><?php echo htmlspecialchars($selected_order['delivery_address']); ?></span>
                </div>
                
                <div class="info-item">
                  <span class="label">Scheduled Delivery:</span>
                  <span class="value">
                    <?php echo date('F j, Y, g:i a', strtotime($selected_order['scheduled_time'])); ?>
                  </span>
                </div>
                
                <div class="info-item">
                  <span class="label">Payment Method:</span>
                  <span class="value"><?php echo htmlspecialchars($selected_order['payment_method']); ?></span>
                </div>
                
                <div class="info-item">
                  <span class="label">Payment Status:</span>
                  <span class="value status-badge <?php echo strtolower($selected_order['payment_status']); ?>">
                    <?php echo htmlspecialchars($selected_order['payment_status']); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="order-items">
            <h3>Order Items</h3>
            
            <div class="items-list">
              <?php foreach ($selected_order_items as $item): ?>
                <div class="order-item">
                  <div class="item-image">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                  </div>
                  
                  <div class="item-details">
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <div class="item-meta">
                      <span class="item-price"><?php echo formatCurrency($item['price']); ?></span>
                      <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                    </div>
                  </div>
                  
                  <div class="item-total">
                    <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
              <div class="summary-line">
                <span>Total</span>
                <span><?php echo formatCurrency($selected_order['total_price']); ?></span>
              </div>
            </div>
          </div>
          
          <div class="order-actions">
            <a href="order-status.php" class="btn btn-outline">
              <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            
            <?php if ($selected_order['order_status'] == 'Delivered'): ?>
              <form action="order-status.php" method="post" class="reorder-form">
                <input type="hidden" name="order_id" value="<?php echo $selected_order['id']; ?>">
                <button type="submit" name="reorder" class="btn btn-primary">
                  <i class="fas fa-redo"></i> Reorder
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <!-- Orders list view -->
        <div class="orders-list">
          <?php foreach ($orders as $order): ?>
            <div class="order-card">
              <div class="order-header">
                <div class="order-id">
                  <h3>Order #<?php echo $order['id']; ?></h3>
                  <span class="order-date"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
                </div>
                
                <div class="order-status">
                  <span class="status-badge <?php echo strtolower($order['order_status']); ?>">
                    <?php echo htmlspecialchars($order['order_status']); ?>
                  </span>
                </div>
              </div>
              
              <div class="order-info">
                <div class="info-item">
                  <span class="label">Total:</span>
                  <span class="value"><?php echo formatCurrency($order['total_price']); ?></span>
                </div>
                
                <div class="info-item">
                  <span class="label">Delivery:</span>
                  <span class="value">
                    <?php echo date('F j, g:i a', strtotime($order['scheduled_time'])); ?>
                  </span>
                </div>
                
                <div class="info-item">
                  <span class="label">Payment:</span>
                  <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                </div>
              </div>
              
              <div class="order-actions">
                <a href="order-status.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline">
                  <i class="fas fa-eye"></i> View Details
                </a>
                
                <?php if ($order['order_status'] == 'Delivered'): ?>
                  <form action="order-status.php" method="post" class="reorder-form">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" name="reorder" class="btn btn-primary">
                      <i class="fas fa-redo"></i> Reorder
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>