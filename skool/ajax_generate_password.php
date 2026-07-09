<?php
/**
 * AJAX Password Generator
 * Returns a JSON response with a secure password
 */

header('Content-Type: application/json');

function generateSecurePassword($length = 10) {
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $numbers = '0123456789';
    $symbols = '!@#$%&*?';
    
    $all = $uppercase . $numbers . $symbols;
    
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    
    return $password;
}

echo json_encode(['password' => generateSecurePassword(10)]);
?>