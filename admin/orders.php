<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
  header("Location: ../login.php");
  exit;
}

// Get order details if viewing a specific order
$selected_order = null;
$selected_order_items = null;

if (isset($_GET['order_id'])) {
  $order_id = (int)$_GET['order_id'];
  $selected_order = getOrderDetails($order_id);
  $selected_order_items = getOrderItems($order_id);
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $order_id = (int)$_POST['order_id'];
  $status = sanitizeInput($_POST['status']);
  
  if (updateOrderStatus($order_id, $status)) {
    header("Location: orders.php?order_id=$order_id&success=status_updated");
    exit;
  }
}

// Get all orders
$orders = getAllOrders();

// Filter orders by status if requested
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

if (!empty($status_filter)) {
  $filtered_orders = [];
  foreach ($orders as $order) {
    if ($order['order_status'] === $status_filter) {
      $filtered_orders[] = $order;
    }
  }
  $orders = $filtered_orders;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders - MealMagic Admin</title>
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="admin.css">
  
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
<body class="admin-body">
  <div class="admin-container">
    <aside class="admin-sidebar">
      <div class="sidebar-header">
        <a href="../index.php" class="logo">
          <span class="logo-icon"><i class="fas fa-utensils"></i></span>
          <span class="logo-text">MealMagic</span>
        </a>
        <button class="close-sidebar" aria-label="Close sidebar">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <nav class="admin-nav">
        <ul>
          <li>
            <a href="dashboard.php">
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="manage-menu.php">
              <i class="fas fa-utensils"></i>
              <span>Manage Menu</span>
            </a>
          </li>
          <li class="active">
            <a href="orders.php">
              <i class="fas fa-shopping-bag"></i>
              <span>Orders</span>
            </a>
          </li>
          <li>
            <a href="../index.php">
              <i class="fas fa-home"></i>
              <span>Back to Site</span>
            </a>
          </li>
          <li>
            <a href="../logout.php">
              <i class="fas fa-sign-out-alt"></i>
              <span>Logout</span>
            </a>
          </li>
        </ul>
      </nav>
    </aside>
    
    <main class="admin-main">
      <header class="admin-header">
        <button class="menu-toggle" aria-label="Toggle menu">
          <i class="fas fa-bars"></i>
        </button>
        
        <div class="admin-title">
          <h1>
            <?php if ($selected_order): ?>
              Order #<?php echo $selected_order['id']; ?>
            <?php else: ?>
              Manage Orders
            <?php endif; ?>
          </h1>
        </div>
        
        <div class="admin-user">
          <div class="theme-toggle">
            <button id="theme-toggle-btn" aria-label="Toggle dark mode">
              <i class="fas fa-moon dark-icon"></i>
              <i class="fas fa-sun light-icon"></i>
            </button>
          </div>
          
          <div class="user-info">
            <span><?php echo $_SESSION['user_name']; ?></span>
            <span class="role">Administrator</span>
          </div>
        </div>
      </header>
      
      <div class="admin-content">
        <?php if (isset($_GET['success'])): ?>
          <div class="alert success">
            <?php if ($_GET['success'] === 'status_updated'): ?>
              <p>Order status updated successfully.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        
        <?php if ($selected_order): ?>
          <!-- Order details view -->
          <div class="admin-section">
            <div class="section-header">
              <div>
                <a href="orders.php" class="btn btn-outline">
                  <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
              </div>
              
              <div class="order-status-update">
                <form action="orders.php" method="post" class="status-form">
                  <input type="hidden" name="order_id" value="<?php echo $selected_order['id']; ?>">
                  <div class="form-inline">
                    <select name="status" required>
                      <option value="Confirmed" <?php echo ($selected_order['order_status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                      <option value="Preparing" <?php echo ($selected_order['order_status'] === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                      <option value="Out for Delivery" <?php echo ($selected_order['order_status'] === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                      <option value="Delivered" <?php echo ($selected_order['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                  </div>
                </form>
              </div>
            </div>
            
            <div class="admin-panels">
              <div class="admin-panel">
                <h3>Order Information</h3>
                <div class="info-grid">
                  <div class="info-item">
                    <span class="label">Order ID</span>
                    <span class="value">#<?php echo $selected_order['id']; ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Date</span>
                    <span class="value"><?php echo date('F j, Y, g:i a', strtotime($selected_order['created_at'])); ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Status</span>
                    <span class="value">
                      <span class="status-badge <?php echo strtolower($selected_order['order_status']); ?>">
                        <?php echo htmlspecialchars($selected_order['order_status']); ?>
                      </span>
                    </span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Payment Method</span>
                    <span class="value"><?php echo htmlspecialchars($selected_order['payment_method']); ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Payment Status</span>
                    <span class="value">
                      <span class="status-badge <?php echo strtolower($selected_order['payment_status']); ?>">
                        <?php echo htmlspecialchars($selected_order['payment_status']); ?>
                      </span>
                    </span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Total Amount</span>
                    <span class="value"><?php echo formatCurrency($selected_order['total_price']); ?></span>
                  </div>
                </div>
              </div>
              
              <div class="admin-panel">
                <h3>Customer Information</h3>
                <div class="info-grid">
                  <div class="info-item">
                    <span class="label">Name</span>
                    <span class="value"><?php echo htmlspecialchars($selected_order['user_name']); ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($selected_order['user_email']); ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Delivery Address</span>
                    <span class="value"><?php echo htmlspecialchars($selected_order['delivery_address']); ?></span>
                  </div>
                  
                  <div class="info-item">
                    <span class="label">Scheduled Delivery</span>
                    <span class="value"><?php echo date('F j, Y, g:i a', strtotime($selected_order['scheduled_time'])); ?></span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="admin-panel">
              <h3>Order Items</h3>
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($selected_order_items as $item): ?>
                    <tr>
                      <td>
                        <div class="item-info">
                          <div class="item-image">
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                          </div>
                          <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        </div>
                      </td>
                      <td><?php echo formatCurrency($item['price']); ?></td>
                      <td><?php echo $item['quantity']; ?></td>
                      <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-right"><strong>Total</strong></td>
                    <td><strong><?php echo formatCurrency($selected_order['total_price']); ?></strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        <?php else: ?>
          <!-- Orders list view -->
          <div class="admin-section">
            <div class="filters">
              <div class="filter-group">
                <label for="status-filter">Filter by Status:</label>
                <select id="status-filter" onchange="filterOrders(this.value)">
                  <option value="">All Orders</option>
                  <option value="Confirmed" <?php echo ($status_filter === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                  <option value="Preparing" <?php echo ($status_filter === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                  <option value="Out for Delivery" <?php echo ($status_filter === 'Out for Delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                  <option value="Delivered" <?php echo ($status_filter === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                </select>
              </div>
              
              <?php if (!empty($status_filter)): ?>
                <a href="orders.php" class="btn btn-outline btn-sm">Clear Filter</a>
              <?php endif; ?>
            </div>
            
            <div class="table-container">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($orders as $order): ?>
                    <tr>
                      <td>#<?php echo $order['id']; ?></td>
                      <td>
                        <div class="customer-info">
                          <span class="customer-name"><?php echo htmlspecialchars($order['user_name']); ?></span>
                          <span class="customer-email"><?php echo htmlspecialchars($order['user_email']); ?></span>
                        </div>
                      </td>
                      <td><?php echo date('M d, Y, g:i a', strtotime($order['created_at'])); ?></td>
                      <td><?php echo formatCurrency($order['total_price']); ?></td>
                      <td>
                        <span class="status-badge <?php echo strtolower($order['order_status']); ?>">
                          <?php echo htmlspecialchars($order['order_status']); ?>
                        </span>
                      </td>
                      <td>
                        <span class="status-badge <?php echo strtolower($order['payment_status']); ?>">
                          <?php echo htmlspecialchars($order['payment_status']); ?>
                        </span>
                      </td>
                      <td>
                        <div class="action-buttons">
                          <a href="orders.php?order_id=<?php echo $order['id']; ?>" class="btn-icon view" title="View Order Details">
                            <i class="fas fa-eye"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  
                  <?php if (empty($orders)): ?>
                    <tr>
                      <td colspan="7" class="text-center">No orders found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  
  <script>
    // Filter orders by status
    function filterOrders(status) {
      if (status) {
        window.location.href = 'orders.php?status=' + encodeURIComponent(status);
      } else {
        window.location.href = 'orders.php';
      }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      // Mobile sidebar toggle
      const menuToggle = document.querySelector('.menu-toggle');
      const closeSidebar = document.querySelector('.close-sidebar');
      const adminContainer = document.querySelector('.admin-container');
      
      if (menuToggle && closeSidebar && adminContainer) {
        menuToggle.addEventListener('click', function() {
          adminContainer.classList.toggle('sidebar-open');
        });
        
        closeSidebar.addEventListener('click', function() {
          adminContainer.classList.remove('sidebar-open');
        });
      }
      
      // Theme toggle
      const themeToggleBtn = document.getElementById('theme-toggle-btn');
      if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
          document.documentElement.classList.toggle('dark-mode');
          
          // Save preference to localStorage
          if (document.documentElement.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
          } else {
            localStorage.setItem('darkMode', 'disabled');
          }
        });
      }
    });
  </script>
  
  <style>
    .filters {
      display: flex;
      align-items: center;
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-lg);
    }
    
    .filter-group {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
    }
    
    .filter-group label {
      font-weight: 600;
    }
    
    .filter-group select {
      padding: 0.5rem 1rem;
      border: 1px solid var(--color-background-dark);
      border-radius: var(--border-radius-md);
      background-color: var(--color-background);
    }
    
    .btn-sm {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
    }
    
    .customer-info {
      display: flex;
      flex-direction: column;
    }
    
    .customer-email {
      font-size: 0.75rem;
      color: var(--color-text-light);
    }
    
    .admin-panels {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--spacing-md);
      margin-bottom: var(--spacing-lg);
    }
    
    @media (min-width: 768px) {
      .admin-panels {
        grid-template-columns: 1fr 1fr;
      }
    }
    
    .admin-panel {
      background-color: var(--color-background);
      border-radius: var(--border-radius-md);
      padding: var(--spacing-md);
      box-shadow: var(--shadow-sm);
      margin-bottom: var(--spacing-md);
    }
    
    .admin-panel h3 {
      margin-top: 0;
      margin-bottom: var(--spacing-md);
      font-size: 1.25rem;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--spacing-md);
    }
    
    @media (min-width: 576px) {
      .info-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-item .label {
      font-size: 0.875rem;
      color: var(--color-text-light);
      margin-bottom: var(--spacing-xs);
    }
    
    .info-item .value {
      font-weight: 600;
    }
    
    .form-inline {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
    }
    
    .text-right {
      text-align: right;
    }
    
    .order-status-update select {
      padding: 0.5rem 1rem;
      border: 1px solid var(--color-background-dark);
      border-radius: var(--border-radius-md);
      background-color: var(--color-background);
    }
  </style>
</body>
</html>