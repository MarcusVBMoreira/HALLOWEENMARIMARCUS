<?php require_once __DIR__ . '/config.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HALLOWEEN 2026</title>

    <link href="https://fonts.googleapis.com/css2?family=Creepster&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">
</head>

<body>

<header class="navbar">
    <div class="logo">
        <a href="<?= BASE_URL ?>index.php" class="logo-link">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Halloween 2026">
        </a>
    </div>

    <nav class="nav-links">
        <a href="<?= BASE_URL ?>index.php">Início</a>
        <a href="<?= BASE_URL ?>pages/games.php">Jogos</a>
        <a href="<?= BASE_URL ?>pages/rules.php">Regras</a>
        <a href="<?= BASE_URL ?>pages/scoreboard.php">Placar</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>logout.php" class="btn-login mobile-only">
                <span class="door door-closed"></span>
                <span class="door door-open"></span>
                Sair
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>login.php" class="btn-login mobile-only">
                <span class="lock lock-closed"></span>
                <span class="lock lock-open"></span>
                Login
            </a>
        <?php endif; ?>
    </nav>

    <div class="header-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= BASE_URL ?>logout.php" class="btn-login">
                <span class="door door-closed"></span>
                <span class="door door-open"></span>
                Sair
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>login.php" class="btn-login">
                <span class="lock lock-closed"></span>
                <span class="lock lock-open"></span>
                Login
            </a>
        <?php endif; ?>
    </div>

    <div class="menu-toggle" id="menu-toggle"></div>
    <div class="menu-overlay"></div>
</header>