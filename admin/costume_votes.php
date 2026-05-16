<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset_votes') {
        try {
            $pdo->beginTransaction();

            $pdo->query("DELETE FROM costume_votes");

            $pdo->commit();

            $message = 'Votação reiniciada com sucesso.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erro ao reiniciar votação.';
        }
    }
}

$ranking = $pdo->query("
    SELECT 
        users.id,
        users.name,
        users.costume_photo,
        COUNT(costume_votes.id) AS total_votes
    FROM users
    LEFT JOIN costume_votes ON costume_votes.voted_user_id = users.id
    WHERE users.role = 'player'
    AND users.is_active = 1
    AND users.costume_photo IS NOT NULL
    GROUP BY users.id, users.name, users.costume_photo
    ORDER BY total_votes DESC, users.name ASC
")->fetchAll();

$totalVotes = $pdo->query("SELECT COUNT(*) FROM costume_votes")->fetchColumn();

$totalActive = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'player' 
    AND is_active = 1
")->fetchColumn();

$totalWithPhoto = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'player' 
    AND is_active = 1
    AND costume_photo IS NOT NULL
")->fetchColumn();

$voters = $pdo->query("
    SELECT 
        voter.name AS voter_name,
        voted.name AS voted_name,
        costume_votes.created_at
    FROM costume_votes
    INNER JOIN users AS voter ON costume_votes.voter_user_id = voter.id
    INNER JOIN users AS voted ON costume_votes.voted_user_id = voted.id
    ORDER BY costume_votes.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Votação de Fantasia | Admin</title>
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
            <a href="<?= BASE_URL ?>admin/index.php">Painel</a>
            <a href="<?= BASE_URL ?>admin/users.php">Jogadores</a>
            <a href="<?= BASE_URL ?>admin/games.php">Jogos</a>
            <a href="<?= BASE_URL ?>admin/results.php">Resultados</a>
            <a href="<?= BASE_URL ?>admin/draw.php">Sorteio</a>
            <a href="<?= BASE_URL ?>admin/costume_votes.php" class="active">Fantasias</a>
            <a href="<?= BASE_URL ?>index.php">Ver Site</a>
            <a href="<?= BASE_URL ?>logout.php">Sair</a>
        </nav>
    </aside>

    <main class="admin-content">

        <div class="admin-header">
            <div>
                <h1>Votação de Fantasia</h1>
                <p>Acompanhe o ranking e reinicie a votação se necessário.</p>
            </div>

            <span class="admin-user">
                👑 <?= htmlspecialchars($_SESSION['user_name']) ?>
            </span>
        </div>

        <?php if ($message): ?>
            <div class="admin-alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-cards">
            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/mascara-roxo.png" class="icon-img-admin" alt="Partidas"></span>
                <h3>Fotos enviadas</h3>
                <strong><?= (int)$totalWithPhoto ?>/<?= (int)$totalActive ?></strong>
            </div>

            <div class="admin-card">
                <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/salvar.png" class="icon-img-admin" alt="Partidas"></span>
                <h3>Votos registrados</h3>
                <strong><?= (int)$totalVotes ?></strong>
            </div>
        </div>

        <div class="admin-panel">
            <h2>Ranking da Melhor Fantasia</h2>

            <?php if (count($ranking) === 0): ?>
                <p class="admin-note">Nenhuma fantasia enviada ainda.</p>
            <?php else: ?>
                <div class="costume-ranking-grid">
                    <?php foreach ($ranking as $index => $player): ?>
                        <div class="costume-ranking-card">
                            <div class="costume-position"><?= $index + 1 ?>º</div>

                            <img 
                                src="<?= BASE_URL . htmlspecialchars($player['costume_photo']) ?>" 
                                alt="Fantasia de <?= htmlspecialchars($player['name']) ?>"
                            >

                            <h3><?= htmlspecialchars($player['name']) ?></h3>
                            <strong><?= (int)$player['total_votes'] ?> voto(s)</strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-grid-two">

            <div class="admin-panel">
                <h2>Histórico de votos</h2>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Quem votou</th>
                                <th>Votou em</th>
                                <th>Data</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($voters) === 0): ?>
                                <tr>
                                    <td colspan="3">Nenhum voto registrado ainda.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($voters as $vote): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vote['voter_name']) ?></td>
                                    <td><?= htmlspecialchars($vote['voted_name']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($vote['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-panel">
                <h2>Reiniciar votação</h2>

                <p class="admin-note">
                    Essa ação apaga todos os votos registrados, mas mantém as fotos enviadas.
                </p>

                <form method="POST" onsubmit="return confirm('Tem certeza que deseja reiniciar a votação? Todos os votos serão apagados.');">
                    <input type="hidden" name="action" value="reset_votes">

                    <button type="submit" class="admin-danger-btn full">
                        Reiniciar Votação
                    </button>
                </form>
            </div>

        </div>

    </main>

</section>

</body>
</html>