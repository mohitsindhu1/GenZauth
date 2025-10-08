<?php
include_once (($_SERVER['DOCUMENT_ROOT'] == "/usr/share/nginx/html/panel" || $_SERVER['DOCUMENT_ROOT'] == "/usr/share/nginx/html/api") ? "/usr/share/nginx/html" : $_SERVER['DOCUMENT_ROOT']) . '/includes/credentials.php'; // reference credentials

if (!class_exists('Redis')) {
        // Redis not available, creating mock Redis class for compatibility
        class Redis {
            public function connect($host, $port) { return true; }
            public function auth($pass) { return true; }
            public function get($key) { return false; }
            public function set($key, $value) { return true; }
            public function expire($key, $ttl) { return true; }
            public function del($key) { return true; }
        }
}

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

global $redisPass;

if(!empty($redisPass)) {
        $redis->auth($redisPass);
}
