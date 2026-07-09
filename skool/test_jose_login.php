<?php
require_once('../config.php');

echo "<h2>Testing STEPPING STONE SCHOOL Login</h2>";

// Get the school data
$school = db_get_row("SELECT * FROM school_register WHERE id = 33");

if ($school) {
    echo "<p><strong>School Name:</strong> " . $school['name'] . "</p>";
    echo "<p><strong>Username:</strong> " . $school['username'] . "</p>";
    echo "<p><strong>Email:</strong> " . $school['email'] . "</p>";
    echo "<p><strong>Stored Password Hash:</strong> " . $school['password'] . "</p>";
    
    // Test with password123
    $testPassword = 'password123';
    echo "<p><strong>Testing password:</strong> '" . $testPassword . "'</p>";
    
    if (password_verify($testPassword, $school['password'])) {
        echo "<p style='color: green; font-weight: bold;'>✓ PASSWORD MATCHES! Login will work with: " . $testPassword . "</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ PASSWORD DOES NOT MATCH!</p>";
        
        // Try to find what password works by checking if it's maybe a different hash
        echo "<h3>Try resetting password:</h3>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='school_id' value='33'>";
        echo "<label>New Password: </label>";
        echo "<input type='text' name='new_password' value='password123' required>";
        echo "<button type='submit' name='reset'>Reset Password</button>";
        echo "</form>";
    }
}

// Process password reset
if (isset($_POST['reset'])) {
    $schoolId = $_POST['school_id'];
    $newPassword = $_POST['new_password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE school_register SET password = ? WHERE id = ?");
    $result = $stmt->execute([$hashedPassword, $schoolId]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Password reset to: <strong>{$newPassword}</strong></p>";
        echo "<p>New hash: " . $hashedPassword . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to reset password</p>";
    }
}
?>