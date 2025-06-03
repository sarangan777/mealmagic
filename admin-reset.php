<?php
require_once 'includes/db.php';

// New admin password: admin123
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = 'admin@mealmagic.lk' AND role = 'admin'");
    $stmt->bindParam(':password', $hashed_password);
    
    if ($stmt->execute()) {
        echo "Admin password updated successfully.\n";
        echo "Email: admin@mealmagic.lk\n";
        echo "Password: admin123\n";
    } else {
        echo "Failed to update admin password.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>