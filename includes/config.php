<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'HALLOWEEN 2026');

/*
 * Ambiente detectado automaticamente:
 * - localhost / 127.0.0.1 / linha de comando => local (XAMPP)
 * - qualquer outro domínio                     => production (Hostinger)
 */
$currentHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]);
$isLocal = PHP_SAPI === 'cli' || in_array($currentHost, ['localhost', '127.0.0.1', '::1'], true);

define('ENVIRONMENT', $isLocal ? 'local' : 'production');

if (ENVIRONMENT === 'local') {
    define('BASE_URL', 'http://localhost/HALLOWEEN_2026/');

    define('DB_HOST', 'localhost');
    define('DB_NAME', 'halloween_2026');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('BASE_URL', 'https://halloweenmarimarcus.com.br/');

    /*
     * As credenciais de produção NÃO ficam no GitHub.
     * Crie o arquivo "halloween_db.php" (modelo: includes/config.production.example.php)
     * em um destes locais no servidor:
     *   1) uma pasta ACIMA do public_html (recomendado, fora do alcance da web)
     *   2) includes/config.production.php (ignorado pelo git)
     */
    $credentialFiles = [
        dirname(__DIR__, 2) . '/halloween_db.php',
        __DIR__ . '/config.production.php',
    ];

    $dbConfig = null;
    foreach ($credentialFiles as $file) {
        if (is_file($file)) {
            $dbConfig = require $file;
            break;
        }
    }

    if (!is_array($dbConfig)) {
        http_response_code(500);
        die('Configuração do banco de dados não encontrada no servidor.');
    }

    define('DB_HOST', $dbConfig['host'] ?? 'localhost');
    define('DB_NAME', $dbConfig['name'] ?? '');
    define('DB_USER', $dbConfig['user'] ?? '');
    define('DB_PASS', $dbConfig['pass'] ?? '');

    unset($dbConfig, $credentialFiles, $file);
}

unset($currentHost, $isLocal);

date_default_timezone_set('America/Sao_Paulo');
