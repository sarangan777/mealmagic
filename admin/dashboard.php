<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
  header("Location: ../login.php");
  exit;
}

// Get dashboard data
$total_sales = getTotalSales();
$total_orders = getTotalOrders();
$total_customers = getTotalCustomers();
$most_ordered_items = getMostOrderedItems(5);
$recent_orders = getAllOrders();
$recent_orders = array_slice($recent_orders, 0, 10); // Get only the 10 most recent orders
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - MealMagic</title>
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="admin.css">
  
  <!-- Chart.js (for admin charts) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
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
          <li class="active">
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
          <li>
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
          <h1>Dashboard</h1>
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
        <div class="dashboard-overview">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
              <h3>Total Sales</h3>
              <p class="stat-value"><?php echo formatCurrency($total_sales); ?></p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
              <h3>Total Orders</h3>
              <p class="stat-value"><?php echo $total_orders; ?></p>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
              <h3>Total Customers</h3>
              <p class="stat-value"><?php echo $total_customers; ?></p>
            </div>
          </div>
        </div>
        
        <div class="dashboard-charts">
          <div class="chart-container">
            <h2>Popular Items</h2>
            <canvas id="popularItemsChart"></canvas>
          </div>
          
          <div class="chart-container">
            <h2>Orders Status</h2>
            <canvas id="orderStatusChart"></canvas>
          </div>
        </div>
        
        <div class="dashboard-tables">
          <div class="table-container">
            <h2>Most Ordered Items</h2>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Total Ordered</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($most_ordered_items as $item): ?>
                  <tr>
                    <td>
                      <div class="item-info">
                        <div class="item-image">
                          <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                      </div>
                    </td>
                    <td><?php echo $item['total_ordered']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          
          <div class="table-container">
            <h2>Recent Orders</h2>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_orders as $order): ?>
                  <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                    <td><?php echo formatCurrency($order['total_price']); ?></td>
                    <td>
                      <span class="status-badge <?php echo strtolower($order['order_status']); ?>">
                        <?php echo htmlspecialchars($order['order_status']); ?>
                      </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            
            <div class="table-footer">
              <a href="orders.php" class="btn btn-outline">View All Orders</a>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <script>
    // Sample data for charts
    document.addEventListener('DOMContentLoaded', function() {
      // Popular Items Chart
      const popularItemsCtx = document.getElementById('popularItemsChart').getContext('2d');
      const popularItemsChart = new Chart(popularItemsCtx, {
        type: 'bar',
        data: {
          labels: [
            <?php foreach ($most_ordered_items as $item): ?>
              "<?php echo addslashes($item['name']); ?>",
            <?php endforeach; ?>
          ],
          datasets: [{
            label: 'Orders',
            data: [
              <?php foreach ($most_ordered_items as $item): ?>
                <?php echo $item['total_ordered']; ?>,
              <?php endforeach; ?>
            ],
            backgroundColor: '#FF7A00',
            borderColor: '#E05A00',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
      
      // Order Status Chart
      const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
      
      // Count orders by status
      <?php
      $status_counts = [
        'Confirmed' => 0,
        'Preparing' => 0,
        'Out for Delivery' => 0,
        'Delivered' => 0
      ];
      
      foreach ($recent_orders as $order) {
        if (isset($status_counts[$order['order_status']])) {
          $status_counts[$order['order_status']]++;
        }
      }
      ?>
      
      const orderStatusChart = new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
          labels: [
            'Confirmed',
            'Preparing',
            'Out for Delivery',
            'Delivered'
          ],
          datasets: [{
            data: [
              <?php echo $status_counts['Confirmed']; ?>,
              <?php echo $status_counts['Preparing']; ?>,
              <?php echo $status_counts['Out for Delivery']; ?>,
              <?php echo $status_counts['Delivered']; ?>
            ],
            backgroundColor: [
              '#17A2B8',
              '#FFC107',
              '#FF7A00',
              '#28A745'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
      
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
</body>
</html>