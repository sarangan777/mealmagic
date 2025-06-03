<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
  header("Location: ../login.php");
  exit;
}

// Get all categories
$categories = getCategories();

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add category
  if (isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (addCategory($name, $description)) {
      header("Location: manage-menu.php?success=category_added");
      exit;
    }
  }
  
  // Update category
  if (isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (updateCategory($id, $name, $description)) {
      header("Location: manage-menu.php?success=category_updated");
      exit;
    }
  }
  
  // Delete category
  if (isset($_POST['delete_category'])) {
    $id = (int)$_POST['id'];
    
    if (deleteCategory($id)) {
      header("Location: manage-menu.php?success=category_deleted");
      exit;
    } else {
      header("Location: manage-menu.php?error=category_has_items");
      exit;
    }
  }
  
  // Add food item
  if (isset($_POST['add_food'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = '../assets/images/food/';
      
      // Create directory if it doesn't exist
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }
      
      // Generate unique filename
      $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      $file_name = uniqid('food_') . '.' . $file_ext;
      $target_file = $upload_dir . $file_name;
      
      // Move uploaded file
      if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image = 'assets/images/food/' . $file_name;
      }
    }
    
    if (addFoodItem($name, $description, $price, $image, $stock, $category_id)) {
      header("Location: manage-menu.php?success=food_added");
      exit;
    }
  }
  
  // Update food item
  if (isset($_POST['update_food'])) {
    $id = (int)$_POST['id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = '../assets/images/food/';
      
      // Create directory if it doesn't exist
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }
      
      // Generate unique filename
      $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      $file_name = uniqid('food_') . '.' . $file_ext;
      $target_file = $upload_dir . $file_name;
      
      // Move uploaded file
      if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image = 'assets/images/food/' . $file_name;
      }
    }
    
    if (updateFoodItem($id, $name, $description, $price, $image, $stock, $category_id)) {
      header("Location: manage-menu.php?success=food_updated");
      exit;
    }
  }
  
  // Delete food item
  if (isset($_POST['delete_food'])) {
    $id = (int)$_POST['id'];
    
    if (deleteFoodItem($id)) {
      header("Location: manage-menu.php?success=food_deleted");
      exit;
    } else {
      header("Location: manage-menu.php?error=food_has_orders");
      exit;
    }
  }
}

// Get food items
$food_items = [];
if ($category_id) {
  $food_items = getFoodItems($category_id);
} else {
  $food_items = getFoodItems();
}

// Get specific category or food item for editing
$edit_category = null;
$edit_food_item = null;

if ($action === 'edit_category' && $category_id) {
  $edit_category = getCategory($category_id);
}

if ($action === 'edit_food' && $item_id) {
  $edit_food_item = getFoodItem($item_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Menu - MealMagic Admin</title>
  
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
          <li class="active">
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
          <h1>Manage Menu</h1>
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
            <?php if ($_GET['success'] === 'category_added'): ?>
              <p>Category added successfully.</p>
            <?php elseif ($_GET['success'] === 'category_updated'): ?>
              <p>Category updated successfully.</p>
            <?php elseif ($_GET['success'] === 'category_deleted'): ?>
              <p>Category deleted successfully.</p>
            <?php elseif ($_GET['success'] === 'food_added'): ?>
              <p>Food item added successfully.</p>
            <?php elseif ($_GET['success'] === 'food_updated'): ?>
              <p>Food item updated successfully.</p>
            <?php elseif ($_GET['success'] === 'food_deleted'): ?>
              <p>Food item deleted successfully.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
          <div class="alert error">
            <?php if ($_GET['error'] === 'category_has_items'): ?>
              <p>Cannot delete category with food items.</p>
            <?php elseif ($_GET['error'] === 'food_has_orders'): ?>
              <p>Cannot delete food item with existing orders.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        
        <div class="admin-section">
          <div class="section-header">
            <h2>Categories</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="add-category-modal">
              <i class="fas fa-plus"></i> Add Category
            </button>
          </div>
          
          <div class="table-container">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Description</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $category): ?>
                  <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td>
                      <div class="action-buttons">
                        <a href="manage-menu.php?action=edit_category&category_id=<?php echo $category['id']; ?>" class="btn-icon edit" title="Edit Category">
                          <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn-icon delete" data-toggle="modal" data-target="delete-category-modal-<?php echo $category['id']; ?>" title="Delete Category">
                          <i class="fas fa-trash"></i>
                        </button>
                        <a href="manage-menu.php?category_id=<?php echo $category['id']; ?>" class="btn-icon view" title="View Food Items">
                          <i class="fas fa-eye"></i>
                        </a>
                      </div>
                      
                      <!-- Delete Category Modal -->
                      <div class="modal" id="delete-category-modal-<?php echo $category['id']; ?>">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h3>Confirm Deletion</h3>
                            <button class="modal-close" data-dismiss="modal">×</button>
                          </div>
                          <div class="modal-body">
                            <p>Are you sure you want to delete the category "<?php echo htmlspecialchars($category['name']); ?>"?</p>
                            <p class="text-danger">This action cannot be undone.</p>
                          </div>
                          <div class="modal-footer">
                            <form action="manage-menu.php" method="post">
                              <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                              <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                              <button type="submit" name="delete_category" class="btn btn-danger">Delete</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                
                <?php if (empty($categories)): ?>
                  <tr>
                    <td colspan="4" class="text-center">No categories found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="admin-section">
          <div class="section-header">
            <h2>
              Food Items
              <?php if ($category_id): ?>
                <span class="subtitle">
                  in "<?php echo htmlspecialchars(getCategory($category_id)['name']); ?>"
                  <a href="manage-menu.php" class="btn-text">View All</a>
                </span>
              <?php endif; ?>
            </h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="add-food-modal">
              <i class="fas fa-plus"></i> Add Food Item
            </button>
          </div>
          
          <div class="table-container">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($food_items as $item): ?>
                  <tr>
                    <td>
                      <div class="item-image">
                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                      <?php 
                        $item_category = getCategory($item['category_id']);
                        echo htmlspecialchars($item_category['name']); 
                      ?>
                    </td>
                    <td><?php echo formatCurrency($item['price']); ?></td>
                    <td>
                      <?php if ($item['stock'] <= 5): ?>
                        <span class="status-badge preparing"><?php echo $item['stock']; ?></span>
                      <?php else: ?>
                        <?php echo $item['stock']; ?>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <a href="manage-menu.php?action=edit_food&item_id=<?php echo $item['id']; ?>" class="btn-icon edit" title="Edit Food Item">
                          <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn-icon delete" data-toggle="modal" data-target="delete-food-modal-<?php echo $item['id']; ?>" title="Delete Food Item">
                          <i class="fas fa-trash"></i>
                        </button>
                        <a href="../menu.php?id=<?php echo $item['id']; ?>" target="_blank" class="btn-icon view" title="View Food Item">
                          <i class="fas fa-eye"></i>
                        </a>
                      </div>
                      
                      <!-- Delete Food Modal -->
                      <div class="modal" id="delete-food-modal-<?php echo $item['id']; ?>">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h3>Confirm Deletion</h3>
                            <button class="modal-close" data-dismiss="modal">×</button>
                          </div>
                          <div class="modal-body">
                            <p>Are you sure you want to delete the food item "<?php echo htmlspecialchars($item['name']); ?>"?</p>
                            <p class="text-danger">This action cannot be undone.</p>
                          </div>
                          <div class="modal-footer">
                            <form action="manage-menu.php" method="post">
                              <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                              <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                              <button type="submit" name="delete_food" class="btn btn-danger">Delete</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                
                <?php if (empty($food_items)): ?>
                  <tr>
                    <td colspan="6" class="text-center">No food items found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Add Category Modal -->
        <div class="modal" id="add-category-modal">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Add Category</h3>
              <button class="modal-close" data-dismiss="modal">×</button>
            </div>
            <form action="manage-menu.php" method="post" class="admin-form">
              <div class="modal-body">
                <div class="form-group">
                  <label for="category-name">Category Name</label>
                  <input type="text" id="category-name" name="name" required>
                </div>
                <div class="form-group">
                  <label for="category-description">Description</label>
                  <textarea id="category-description" name="description" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Edit Category Modal -->
        <?php if ($edit_category): ?>
          <div class="modal active" id="edit-category-modal">
            <div class="modal-content">
              <div class="modal-header">
                <h3>Edit Category</h3>
                <a href="manage-menu.php" class="modal-close">×</a>
              </div>
              <form action="manage-menu.php" method="post" class="admin-form">
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                <div class="modal-body">
                  <div class="form-group">
                    <label for="edit-category-name">Category Name</label>
                    <input type="text" id="edit-category-name" name="name" value="<?php echo htmlspecialchars($edit_category['name']); ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="edit-category-description">Description</label>
                    <textarea id="edit-category-description" name="description" rows="3"><?php echo htmlspecialchars($edit_category['description']); ?></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <a href="manage-menu.php" class="btn btn-outline">Cancel</a>
                  <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Add Food Modal -->
        <div class="modal" id="add-food-modal">
          <div class="modal-content">
            <div class="modal-header">
              <h3>Add Food Item</h3>
              <button class="modal-close" data-dismiss="modal">×</button>
            </div>
            <form action="manage-menu.php" method="post" class="admin-form" enctype="multipart/form-data">
              <div class="modal-body">
                <div class="form-row">
                  <div class="form-group">
                    <label for="food-name">Food Name</label>
                    <input type="text" id="food-name" name="name" required>
                  </div>
                  <div class="form-group">
                    <label for="food-category">Category</label>
                    <select id="food-category" name="category_id" required>
                      <option value="">Select Category</option>
                      <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="food-description">Description</label>
                  <textarea id="food-description" name="description" rows="3" required></textarea>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="food-price">Price (Rs.)</label>
                    <input type="number" id="food-price" name="price" step="0.01" min="0" required>
                  </div>
                  <div class="form-group">
                    <label for="food-stock">Stock</label>
                    <input type="number" id="food-stock" name="stock" min="0" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="food-image">Image</label>
                  <input type="file" id="food-image" name="image" accept="image/*" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
                <button type="submit" name="add_food" class="btn btn-primary">Add Food Item</button>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Edit Food Modal -->
        <?php if ($edit_food_item): ?>
          <div class="modal active" id="edit-food-modal">
            <div class="modal-content">
              <div class="modal-header">
                <h3>Edit Food Item</h3>
                <a href="manage-menu.php" class="modal-close">×</a>
              </div>
              <form action="manage-menu.php" method="post" class="admin-form" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $edit_food_item['id']; ?>">
                <div class="modal-body">
                  <div class="form-row">
                    <div class="form-group">
                      <label for="edit-food-name">Food Name</label>
                      <input type="text" id="edit-food-name" name="name" value="<?php echo htmlspecialchars($edit_food_item['name']); ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="edit-food-category">Category</label>
                      <select id="edit-food-category" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                          <option value="<?php echo $category['id']; ?>" <?php echo ($edit_food_item['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="edit-food-description">Description</label>
                    <textarea id="edit-food-description" name="description" rows="3" required><?php echo htmlspecialchars($edit_food_item['description']); ?></textarea>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label for="edit-food-price">Price (Rs.)</label>
                      <input type="number" id="edit-food-price" name="price" step="0.01" min="0" value="<?php echo $edit_food_item['price']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="edit-food-stock">Stock</label>
                      <input type="number" id="edit-food-stock" name="stock" min="0" value="<?php echo $edit_food_item['stock']; ?>" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="edit-food-image">Image</label>
                    <?php if (!empty($edit_food_item['image'])): ?>
                      <div class="current-image">
                        <img src="../<?php echo htmlspecialchars($edit_food_item['image']); ?>" alt="<?php echo htmlspecialchars($edit_food_item['name']); ?>" width="100">
                        <p>Current image. Upload a new one to replace it.</p>
                      </div>
                    <?php endif; ?>
                    <input type="file" id="edit-food-image" name="image" accept="image/*">
                  </div>
                </div>
                <div class="modal-footer">
                  <a href="manage-menu.php" class="btn btn-outline">Cancel</a>
                  <button type="submit" name="update_food" class="btn btn-primary">Update Food Item</button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Modal functionality
      const modalToggles = document.querySelectorAll('[data-toggle="modal"]');
      const modalCloses = document.querySelectorAll('[data-dismiss="modal"]');
      
      modalToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
          const modalId = this.getAttribute('data-target');
          const modal = document.getElementById(modalId);
          
          if (modal) {
            modal.classList.add('active');
          }
        });
      });
      
      modalCloses.forEach(close => {
        close.addEventListener('click', function() {
          const modal = this.closest('.modal');
          
          if (modal) {
            modal.classList.remove('active');
          }
        });
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
  
  <style>
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1050;
      overflow-y: auto;
      padding: var(--spacing-md);
    }
    
    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .modal-content {
      background-color: var(--color-background);
      border-radius: var(--border-radius-lg);
      max-width: 600px;
      width: 100%;
      box-shadow: var(--shadow-lg);
      margin: auto;
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: var(--spacing-md);
      border-bottom: 1px solid var(--color-background-dark);
    }
    
    .modal-header h3 {
      margin: 0;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--color-text-light);
    }
    
    .modal-body {
      padding: var(--spacing-md);
    }
    
    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: var(--spacing-md);
      padding: var(--spacing-md);
      border-top: 1px solid var(--color-background-dark);
    }
    
    .admin-section {
      margin-bottom: var(--spacing-xl);
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--spacing-md);
    }
    
    .section-header h2 {
      margin: 0;
    }
    
    .section-header .subtitle {
      font-size: 1rem;
      font-weight: normal;
      color: var(--color-text-light);
      margin-left: var(--spacing-sm);
    }
    
    .btn-danger {
      background-color: var(--color-error);
      border-color: var(--color-error);
      color: var(--color-text-inverse);
    }
    
    .btn-danger:hover {
      background-color: #bd2130;
      border-color: #bd2130;
    }
    
    .text-danger {
      color: var(--color-error);
    }
    
    .text-center {
      text-align: center;
    }
    
    .current-image {
      margin-bottom: var(--spacing-sm);
    }
    
    .current-image p {
      font-size: 0.875rem;
      color: var(--color-text-light);
      margin-top: var(--spacing-xs);
    }
  </style>
</body>
</html>