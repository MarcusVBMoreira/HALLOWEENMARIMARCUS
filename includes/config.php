<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ENVIRONMENT', 'local');
//define('ENVIRONMENT', 'production');

define('APP_NAME', 'HALLOWEEN 2026');

if (ENVIRONMENT === 'local') {
    define('BASE_URL', 'http://localhost/HALLOWEEN_2026/');

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'halloween_2026');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('BASE_URL', 'https://halloweenmarimarcus.com.br/');

    define('DB_HOST', '');
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASS', '');
}

date_default_timezone_set('America/Sao_Paulo');