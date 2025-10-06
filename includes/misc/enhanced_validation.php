<?php
namespace misc\enhanced;

/**
 * Enhanced validation and security functions for KeyAuth
 * Provides additional security layers and input validation
 */

function validateInput($input, $type = 'general', $maxLength = 255) {
    // Handle null and non-string inputs
    if ($input === null || is_array($input) || is_object($input)) {
        return ['valid' => false, 'error' => 'Invalid input type'];
    }
    
    // Convert to string and sanitize
    $input = trim(strval($input));
    
    // Check length
    if (strlen($input) > $maxLength) {
        return ['valid' => false, 'error' => "Input too long (max: {$maxLength} characters)"];
    }
    
    // Type-specific validation
    switch ($type) {
        case 'username':
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $input)) {
                return ['valid' => false, 'error' => 'Username can only contain letters, numbers, underscores, and hyphens'];
            }
            if (strlen($input) < 3) {
                return ['valid' => false, 'error' => 'Username must be at least 3 characters long'];
            }
            break;
            
        case 'email':
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => false, 'error' => 'Invalid email format'];
            }
            break;
            
        case 'hex':
            if (!ctype_xdigit($input)) {
                return ['valid' => false, 'error' => 'Invalid hexadecimal format'];
            }
            break;
            
        case 'alphanumeric':
            if (!preg_match('/^[a-zA-Z0-9]+$/', $input)) {
                return ['valid' => false, 'error' => 'Only alphanumeric characters allowed'];
            }
            break;
            
        case 'sessionid':
            if (!preg_match('/^[a-zA-Z0-9]{10,64}$/', $input)) {
                return ['valid' => false, 'error' => 'Invalid session ID format'];
            }
            break;
    }
    
    // Only check for obvious XSS attempts - SQL injection is handled by prepared statements
    $xss_patterns = [
        '/(\<script[^>]*\>|\<\/script\>)/i',
        '/(javascript:|vbscript:|data:text\/html)/i',
        '/(onload\s*=|onerror\s*=|onclick\s*=)/i'
    ];
    
    foreach ($xss_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('xss_attempt_detected', ['input' => substr($input, 0, 100)]);
            return ['valid' => false, 'error' => 'Invalid characters detected'];
        }
    }
    
    return ['valid' => true, 'value' => strip_tags($input)];
}

function enhancedSanitize($input, $type = 'general', $maxLength = 255) {
    $result = validateInput($input, $type, $maxLength);
    return $result['valid'] ? $result['value'] : null;
}

function rateLimitCheck($identifier, $limit = 10, $window = 60) {
    // Enhanced rate limiting with Redis/cache
    $key = "rate_limit:$identifier";
    
    try {
        $current = \misc\cache\select($key);
        
        if ($current === false || $current === null) {
            \misc\cache\insert($key, 1, $window);
            return false; // Not rate limited
        }
        
        if ($current >= $limit) {
            logSecurityEvent('rate_limit_exceeded', ['identifier' => $identifier, 'limit' => $limit]);
            return true; // Rate limited
        }
        
        \misc\cache\increment($key);
        return false; // Not rate limited
    } catch (\Exception $e) {
        // Fallback to allowing if cache fails
        error_log("Rate limit check failed: " . $e->getMessage());
        return false;
    }
}

function logSecurityEvent($event, $details = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    error_log("SECURITY_EVENT: " . json_encode($logData));
}

function getClientIP() {
    // Use existing secure IP detection from shared/primary.php
    return \api\shared\primary\getIp();
}

function validateApiRequest($requiredFields = [], $postData = null) {
    $postData = $postData ?: $_POST;
    $errors = [];
    
    // Check for required fields
    foreach ($requiredFields as $field => $rules) {
        if (!isset($postData[$field]) || empty($postData[$field])) {
            $errors[$field] = "Field '$field' is required";
            continue;
        }
        
        $value = $postData[$field];
        
        // Apply validation rules
        if (isset($rules['type'])) {
            $validation = validateInput($value, $rules['type'], $rules['maxLength'] ?? 255);
            if (!$validation['valid']) {
                $errors[$field] = $validation['error'];
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'sanitized' => array_map(function($value) {
            return enhancedSanitize($value);
        }, $postData)
    ];
}

function createApiResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => time(),
        'version' => '1.0'
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    return json_encode($response, JSON_UNESCAPED_SLASHES);
}

function honeypotCheck($fieldName = 'bot_field') {
    // Simple honeypot to catch bots
    return !empty($_POST[$fieldName] ?? '');
}

function checkCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}