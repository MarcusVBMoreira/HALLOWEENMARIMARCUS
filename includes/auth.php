<?php

require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (!isAdmin()) {
        header("Location: " . BASE_URL . "pages/dashboard.php");
        exit;
    }
}