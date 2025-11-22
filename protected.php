<?php
/**
 * Protection for pages that require authentication
 * Include this at the top of any page that requires the user to be logged in
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'includes/auth.php';
require_once 'classes/Logger.php';

// Initialize logger
$logger = Logger::getInstance();

// Check if user is authenticated
if (!isAuthenticated()) {
    // Store the current URL for redirecting back after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Log the unauthorized access attempt
    $logger->warning('Unauthorized access attempt', [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'request_uri' => $_SERVER['REQUEST_URI']
    ]);
    
    // Redirect to login page
    header('Location: /login.php');
    exit();
}

// Optional: Check for specific role requirements
if (isset($requiredRole) && !hasRole($requiredRole)) {
    // Log the unauthorized role access
    $logger->warning('Unauthorized role access attempt', [
        'user_id' => $_SESSION['user_id'],
        'required_role' => $requiredRole,
        'user_role' => $_SESSION['user_role'],
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    // Redirect to unauthorized page
    header('Location: /unauthorized.php');
    exit();
}

// Update last activity time for session timeout
$_SESSION['last_activity'] = time();

// Check for session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    // Log the session timeout
    $logger->info('Session timed out', [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['user']['username'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    // Log out the user
    require_once 'logout.php';
    exit();
}

// Check for remember me token if session is about to expire
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    if (isset($_COOKIE['remember_token'])) {
        try {
            require_once 'classes/Database.php';
            $db = Database::getInstance();
            
            // Find the token in the database
            $stmt = $db->prepare("SELECT u.* FROM users u 
                                 JOIN user_tokens ut ON u.id = ut.user_id 
                                 WHERE ut.token = ? AND ut.expires_at > NOW()");
            $hashedToken = password_hash($_COOKIE['remember_token'], PASSWORD_DEFAULT);
            $stmt->execute([$hashedToken]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Refresh the user session
                loginUser($user);
                
                // Update the token expiration
                $newExpiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                $stmt = $db->prepare("UPDATE user_tokens SET expires_at = ? WHERE token = ?");
                $stmt->execute([$newExpiry, $hashedToken]);
                
                // Refresh the cookie
                setcookie('remember_token', $_COOKIE['remember_token'], [
                    'expires' => strtotime('+30 days'),
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
                
                // Log the session refresh
                $logger->debug('Session refreshed with remember token', [
                    'user_id' => $user['id']
                ]);
            }
        } catch (Exception $e) {
            // Log error but don't interrupt the user
            $logger->error('Error refreshing session with remember token', [
                'error' => $e->getMessage(),
                'user_id' => $_SESSION['user_id'] ?? null
            ]);
        }
    }
}
