<?php

require_once __DIR__ . '/config.php';

function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}