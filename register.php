<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
  header("Location: index.php");
  exit;
}

$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  // Validate name
  if (empty(trim($_POST["name"]))) {
    $name_err = "Please enter your name.";
  } else {
    $name = sanitizeInput($_POST["name"]);
  }
  
  // Validate email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter your email.";
  } else {
    $email = sanitizeInput($_POST["email"]);
    
    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email_err = "Please enter a valid email address.";
    }
  }
  
  // Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password.";
  } elseif (strlen(trim($_POST["password"])) < 6) {
    $password_err = "Password must have at least 6 characters.";
  } else {
    $password = trim($_POST["password"]);
  }
  
  // Validate confirm password
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm password.";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if ($password != $confirm_password) {
      $confirm_password_err = "Passwords did not match.";
    }
  }
  
  // Check input errors before inserting in database
  if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
    
    // Register the user
    if (registerUser($name, $email, $password)) {
      $registration_success = true;
    } else {
      $email_err = "This email is already taken.";
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
  <div class="auth-card">
    <h1>Create an Account</h1>
    
    <?php if ($registration_success): ?>
      <div class="alert success">
        <p>Registration successful! <a href="login.php">Click here to login</a>.</p>
      </div>
    <?php else: ?>
      <p class="auth-intro">Join MealMagic to order delicious Sri Lankan cuisine right to your doorstep.</p>
      
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
          <span class="error-text"><?php echo $name_err; ?></span>
        </div>
        
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
        
        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
          <span class="error-text"><?php echo $confirm_password_err; ?></span>
        </div>
        
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </div>
        
        <div class="auth-footer">
          <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>