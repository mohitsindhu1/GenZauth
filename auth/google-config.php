<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getGoogleClient() {
    $client = new Google_Client();
    
    $clientId = getenv('GOOGLE_CLIENT_ID');
    $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
    $redirectUri = getenv('REPLIT_DOMAINS') 
        ? 'https://' . explode(',', getenv('REPLIT_DOMAINS'))[0] . '/auth/google-callback.php'
        : 'http://localhost:5000/auth/google-callback.php';
    
    if (!$clientId || !$clientSecret) {
        throw new Exception('Google OAuth credentials not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET environment variables.');
    }
    
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope('openid');
    $client->addScope('email');
    $client->addScope('profile');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    
    return $client;
}

function getGoogleAuthUrl() {
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $client = getGoogleClient();
        
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        $client->setState($state);
        
        return $client->createAuthUrl();
    } catch (Exception $e) {
        error_log("Google OAuth error: " . $e->getMessage());
        return null;
    }
}
