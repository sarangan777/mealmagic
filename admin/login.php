<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
  header("Location: dashboard.php");
  exit;
}

$email = $password = "";
$email_err = $password_err = "";
$login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter your email.";
  } else {
    $email = sanitizeInput($_POST["email"]);
  }
  
  // Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter your password.";
  } else {
    $password = trim($_POST["password"]);
  }
  
  // Check input errors before authenticating
  if (empty($email_err) && empty($password_err)) {
    // Attempt to log in
    if (loginUser($email, $password)) {
      // Check if user is admin
      if (isAdmin()) {
        header("Location: dashboard.php");
        exit;
      } else {
        $login_err = "Access denied. Admin privileges required.";
        // Logout non-admin user
        logout();
      }
    } else {
      $login_err = "Invalid email or password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - MealMagic</title>
  
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
  <div class="auth-container">
    <div class="auth-card">
      <h1>Admin Login</h1>
      
      <p class="auth-intro">Login to access the MealMagic admin dashboard.</p>
      
      <?php if (!empty($login_err)): ?>
        <div class="alert error">
          <p><?php echo $login_err; ?></p>
        </div>
      <?php endif; ?>
      
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
        <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
          <span class="error-text"><?php echo $email_err; ?></span>
        </div>
        
        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
          <span class="error-text"><?php echo $password_err; ?></span>
        </div>
        
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
        
        <div class="auth-footer">
          <p><a href="../index.php">Back to Website</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>