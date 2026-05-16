<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'player'")->fetchColumn();
$totalTeams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$totalGames = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();

$votingStatusStmt = $pdo->prepare("
    SELECT setting_value 
    FROM event_settings 
    WHERE setting_key = 'costume_voting_enabled'
    LIMIT 1
");
$votingStatusStmt->execute();
$votingEnabled = $votingStatusStmt->fetchColumn() === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_costume_voting') {
    $newValue = $votingEnabled ? '0' : '1';

    $stmt = $pdo->prepare("
        UPDATE event_settings 
        SET setting_value = ? 
        WHERE setting_key = 'costume_voting_enabled'
    ");
    $stmt->execute([$newValue]);

    header("Location: " . BASE_URL . "admin/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Chefe | HALLOWEEN 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Creepster&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">
</head>
<body>

<section class="admin-page">

    <aside class="admin-sidebar">
        <div class="admin-logo">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Halloween 2026">
        </div>

        <nav class="admin-menu">
            <a href="<?= BASE_URL ?>admin/index.php" class="active">Painel</a>
            <a href="<?= BASE_URL ?>admin/users.php">Jogadores</a>
            <a href="<?= BASE_URL ?>admin/games.php">Jogos</a>
            <a href="<?= BASE_URL ?>admin/results.php">Resultados</a>
            <a href="<?= BASE_URL ?>admin/draw.php">Sorteio</a>
            <a href="<?= BASE_URL ?>admin/costume_votes.php">Fantasias</a>
            <a href="<?= BASE_URL ?>index.php">Ver Site</a>
            <a href="<?= BASE_URL ?>logout.php">Sair</a>
        </nav>
    </aside>

    <main class="admin-content">

        <div class="admin-header">
            <div>
                <h1>Painel do Chefe</h1>
                <p>Controle geral do evento HALLOWEEN 2026.</p>
            </div>

            <span class="admin-user">
                👑 <?= htmlspecialchars($_SESSION['user_name']) ?>
            </span>
        </div>

        <div class="admin-cards">

            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/user-roxo.png" class="icon-img-admin" alt="Jogadores"></span>
                <h3>Jogadores</h3>
                <strong><?= $totalUsers ?></strong>
            </div>

            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/users-laranja.png" class="icon-img-admin" alt="Times"></span>
                <h3>Times</h3>
                <strong><?= $totalTeams ?></strong>
            </div>

            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/dado.png" class="icon-img-admin" alt="Jogos"></span>
                <h3>Jogos</h3>
                <strong><?= $totalGames ?></strong>
            </div>

            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/trofeu-roxo.png" class="icon-img-admin" alt="Partidas"></span>
                <h3>Partidas</h3>
                <strong><?= $totalMatches ?></strong>
            </div>

        </div>

        <div class="admin-actions">
            <a href="<?= BASE_URL ?>admin/users.php" class="btn btn-primary">Gerenciar Jogadores</a>
            <a href="<?= BASE_URL ?>admin/games.php" class="btn btn-secondary">Cadastrar Jogos</a>
            <a href="<?= BASE_URL ?>admin/results.php" class="btn btn-secondary">Lançar Resultado</a>
            <a href="<?= BASE_URL ?>admin/draw.php" class="btn btn-primary"><span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/dado.png" class="icon-img-admin" alt="Jogos"></span> Sortear Times</a>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle_costume_voting">

                <button type="submit" class="btn <?= $votingEnabled ? 'btn-secondary' : 'btn-primary' ?>">
                    <?= $votingEnabled ? '<span class="img-icon-admin"><img src="../assets/images/icons/cadeado-roxo.png" class="icon-img-admin" alt="Jogos"></span> Bloquear Votação' : '<span class="img-icon-admin"><img src="../assets/images/icons/mascara-roxo.png" class="icon-img-admin" alt="Jogos"></span> Liberar Votação' ?>
                </button>
            </form>
        </div>

    </main>

</section>

</body>
</html>