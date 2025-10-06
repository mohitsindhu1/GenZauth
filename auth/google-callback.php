<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/google-config.php';
require_once __DIR__ . '/../includes/misc/autoload.phtml';
require_once __DIR__ . '/../includes/dashboard/autoload.phtml';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_GET['code'])) {
        throw new Exception('No authorization code received');
    }
    
    if (!isset($_GET['state']) || !isset($_SESSION['google_oauth_state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {
        throw new Exception('Invalid OAuth state parameter. Possible CSRF attack.');
    }
    
    unset($_SESSION['google_oauth_state']);
    
    $client = getGoogleClient();
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception('OAuth error: ' . $token['error_description']);
    }
    
    $client->setAccessToken($token);
    
    $payload = $client->verifyIdToken();
    if (!$payload) {
        throw new Exception('Invalid ID token');
    }
    
    if (!isset($payload['email_verified']) || !$payload['email_verified']) {
        throw new Exception('Email not verified by Google');
    }
    
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();
    
    $googleId = $userInfo->id;
    $email = $userInfo->email;
    $name = $userInfo->name;
    $picture = $userInfo->picture;
    
    $conn = \misc\mysql\getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM accounts WHERE google_id = ?");
    $stmt->bind_param("s", $googleId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingUser = $result->fetch_assoc();
    
    if (!$existingUser) {
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 1) {
            throw new Exception('Multiple accounts with this email. Please contact support.');
        }
        
        $existingUser = $result->fetch_assoc();
    }
    
    if ($existingUser) {
        if (!empty($existingUser['banned'])) {
            throw new Exception('Account is banned: ' . $existingUser['banned']);
        }
        
        if (!empty($existingUser['locked'])) {
            throw new Exception('Account is locked. Please contact support.');
        }
        
        if (!empty($existingUser['google_id']) && $existingUser['google_id'] !== $googleId) {
            throw new Exception('This email is already linked to a different Google account.');
        }
        
        if (!empty($existingUser['twofactor'])) {
            if (empty($existingUser['google_id'])) {
                throw new Exception('Two-factor authentication is enabled on this account. Please login with username/password first to link your Google account.');
            }
            
            $_SESSION['temp_username'] = $existingUser['username'];
            $_SESSION['temp_google_auth'] = true;
            $_SESSION['temp_istwofamode'] = true;
            
            header("Location: ../login/");
            exit();
        }
        
        if (empty($existingUser['google_id'])) {
            $updateStmt = $conn->prepare("UPDATE accounts SET google_id = ? WHERE username = ?");
            $updateStmt->bind_param("ss", $googleId, $existingUser['username']);
            $updateStmt->execute();
        }
        
        session_regenerate_id(true);
        
        $_SESSION['username'] = $existingUser['username'];
        $_SESSION['email'] = $existingUser['email'];
        
        header("Location: ../app/");
        exit();
    } else {
        $_SESSION['google_id'] = $googleId;
        $_SESSION['google_email'] = $email;
        $_SESSION['google_name'] = $name;
        $_SESSION['google_picture'] = $picture;
        $_SESSION['pending_google_registration'] = true;
        
        header("Location: ../register/?google=1");
        exit();
    }
    
} catch (Exception $e) {
    error_log("Google OAuth callback error: " . $e->getMessage());
    $_SESSION['oauth_error'] = $e->getMessage();
    header("Location: ../login/?error=oauth_failed");
    exit();
}
