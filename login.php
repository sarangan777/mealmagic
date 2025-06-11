<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$email = $password = "";
$email_err = $password_err = "";
$login_err = "";

// Process form data when form is submitted
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
            // Redirect admin to admin dashboard
            if (isAdmin()) {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $login_err = "Invalid email or password.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Welcome Back</h1>
        
        <p class="auth-intro">Login to your MealMagic account to order your favorite Sri Lankan dishes.</p>
        
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
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p>Are you an admin? <a href="admin/dashboard.php">Admin Login</a></p>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>