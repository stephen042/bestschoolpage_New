<?php
require_once('../config.php');

echo "<h2>Login Test</h2>";

if (isset($_POST['test'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Try to find user
    $user = db_get_row("SELECT * FROM school_register WHERE username = ? OR email = ?", [$username, $username]);
    
    if ($user) {
        echo "<p>User found: " . $user['name'] . "</p>";
        echo "<p>Stored password hash: " . substr($user['password'], 0, 30) . "...</p>";
        
        if (password_verify($password, $user['password'])) {
            echo "<p style='color: green; font-weight: bold;'>✓ PASSWORD MATCHES! Login would work.</p>";
            echo "<p>You can login at: <a href='../login.php' target='_blank'>../login.php</a></p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ PASSWORD DOES NOT MATCH!</p>";
            echo "<p>Try resetting the password again.</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found with username: " . htmlspecialchars($username) . "</p>";
    }
}
?>

<form method="post">
    <label>Username or Email:</label>
    <input type="text" name="username" required style="width: 200px; padding: 5px;">
    <br><br>
    <label>Password:</label>
    <input type="text" name="password" required style="width: 150px; padding: 5px;">
    <br><br>
    <button type="submit" name="test">Test Login</button>
</form>