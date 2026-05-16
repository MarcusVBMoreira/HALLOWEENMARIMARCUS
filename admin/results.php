<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$message = '';
$error = '';

$games = $pdo->query("
    SELECT * FROM games 
    WHERE is_active = 1 
    ORDER BY game_order ASC, id ASC
")->fetchAll();

$players = $pdo->query("
    SELECT users.id, users.name, teams.name AS team_name
    FROM users
    LEFT JOIN teams ON users.team_id = teams.id
    WHERE users.role = 'player'
    ORDER BY users.name ASC
")->fetchAll();

$teams = $pdo->query("
    SELECT * FROM teams
    ORDER BY name ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_result') {
        $game_id = (int) ($_POST['game_id'] ?? 0);
        $winner_user_id = !empty($_POST['winner_user_id']) ? (int) $_POST['winner_user_id'] : null;
        $winner_team_id = !empty($_POST['winner_team_id']) ? (int) $_POST['winner_team_id'] : null;

        if ($game_id <= 0) {
            $error = 'Selecione um jogo.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ? LIMIT 1");
            $stmt->execute([$game_id]);
            $game = $stmt->fetch();

            if (!$game) {
                $error = 'Jogo não encontrado.';
            } elseif ((int)$game['player_points'] > 0 && !$winner_user_id) {
                $error = 'Este jogo pontua jogador. Selecione o jogador vencedor.';
            } elseif ((int)$game['team_points'] > 0 && !$winner_team_id) {
                $error = 'Este jogo pontua equipe. Selecione o time vencedor.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("
                        INSERT INTO matches (game_id, winner_user_id, winner_team_id, created_by)
                        VALUES (?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $game_id,
                        $winner_user_id,
                        $winner_team_id,
                        $_SESSION['user_id']
                    ]);

                    $match_id = $pdo->lastInsertId();

                    if ((int)$game['player_points'] > 0 && $winner_user_id) {
                        $points = (int)$game['player_points'];

                        $stmt = $pdo->prepare("
                            INSERT INTO user_score_history (user_id, match_id, points)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$winner_user_id, $match_id, $points]);

                        $stmt = $pdo->prepare("
                            UPDATE users 
                            SET total_score = total_score + ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$points, $winner_user_id]);
                    }

                    if ((int)$game['team_points'] > 0 && $winner_team_id) {
                        $points = (int)$game['team_points'];

                        $stmt = $pdo->prepare("
                            INSERT INTO team_score_history (team_id, match_id, points)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$winner_team_id, $match_id, $points]);

                        $stmt = $pdo->prepare("
                            UPDATE teams 
                            SET total_score = total_score + ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$points, $winner_team_id]);
                    }

                    $pdo->commit();

                    $message = 'Resultado lançado com sucesso.';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Erro ao lançar resultado.';
                }
            }
        }
    }

    if ($action === 'delete_result') {
        $match_id = (int) ($_POST['match_id'] ?? 0);

        if ($match_id > 0) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT * FROM user_score_history WHERE match_id = ?");
                $stmt->execute([$match_id]);
                $userScores = $stmt->fetchAll();

                foreach ($userScores as $score) {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET total_score = total_score - ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$score['points'], (int)$score['user_id']]);
                }

                $stmt = $pdo->prepare("SELECT * FROM team_score_history WHERE match_id = ?");
                $stmt->execute([$match_id]);
                $teamScores = $stmt->fetchAll();

                foreach ($teamScores as $score) {
                    $stmt = $pdo->prepare("
                        UPDATE teams 
                        SET total_score = total_score - ?
                        WHERE id = ?
                    ");
                    $stmt->execute([(int)$score['points'], (int)$score['team_id']]);
                }

                $stmt = $pdo->prepare("DELETE FROM user_score_history WHERE match_id = ?");
                $stmt->execute([$match_id]);

                $stmt = $pdo->prepare("DELETE FROM team_score_history WHERE match_id = ?");
                $stmt->execute([$match_id]);

                $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
                $stmt->execute([$match_id]);

                $pdo->commit();

                $message = 'Resultado removido e pontuação revertida.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Erro ao remover resultado.';
            }
        }
    }
}

$matches = $pdo->query("
    SELECT 
        matches.id,
        matches.created_at,
        games.name AS game_name,
        games.player_points,
        games.team_points,
        winner_user.name AS winner_user_name,
        winner_team.name AS winner_team_name,
        creator.name AS created_by_name
    FROM matches
    INNER JOIN games ON matches.game_id = games.id
    LEFT JOIN users AS winner_user ON matches.winner_user_id = winner_user.id
    LEFT JOIN teams AS winner_team ON matches.winner_team_id = winner_team.id
    LEFT JOIN users AS creator ON matches.created_by = creator.id
    ORDER BY matches.created_at DESC
")->fetchAll();

$rankingPlayers = $pdo->query("
    SELECT name, total_score
    FROM users
    WHERE role = 'player'
    ORDER BY total_score DESC, name ASC
    LIMIT 5
")->fetchAll();

$rankingTeams = $pdo->query("
    SELECT name, total_score
    FROM teams
    ORDER BY total_score DESC, name ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resultados | HALLOWEEN 2026</title>
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
            <a href="<?= BASE_URL ?>admin/results.php" class="active">Resultados</a>
            <a href="<?= BASE_URL ?>admin/draw.php">Sorteio</a>
            <a href="<?= BASE_URL ?>admin/costume_votes.php">Fantasias</a>
            <a href="<?= BASE_URL ?>index.php">Ver Site</a>
            <a href="<?= BASE_URL ?>logout.php">Sair</a>
        </nav>
    </aside>

    <main class="admin-content">

        <div class="admin-header">
            <div>
                <h1>Lançar Resultados</h1>
                <p>Registre os vencedores e atualize a pontuação automaticamente.</p>
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

        <div class="admin-grid-two">

            <div class="admin-panel">
                <h2>Novo Resultado</h2>

                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="create_result">

                    <div class="form-group">
                        <label>Jogo</label>
                        <select name="game_id" required>
                            <option value="">Selecione o jogo</option>

                            <?php foreach ($games as $game): ?>
                                <option value="<?= $game['id'] ?>">
                                    <?= htmlspecialchars($game['name']) ?>
                                    — Jogador: <?= (int)$game['player_points'] ?> pts
                                    | Time: <?= (int)$game['team_points'] ?> pts
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jogador vencedor</label>
                        <select name="winner_user_id">
                            <option value="">Não pontua jogador / sem vencedor individual</option>

                            <?php foreach ($players as $player): ?>
                                <option value="<?= $player['id'] ?>">
                                    <?= htmlspecialchars($player['name']) ?>
                                    <?= $player['team_name'] ? ' — ' . htmlspecialchars($player['team_name']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Time vencedor</label>
                        <select name="winner_team_id">
                            <option value="">Não pontua time / sem vencedor de equipe</option>

                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['id'] ?>">
                                    <?= htmlspecialchars($team['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary full">
                        Lançar Resultado
                    </button>
                </form>
            </div>

            <div class="admin-panel">
                <h2>Ranking Atual</h2>

                <div class="admin-ranking-block">
                    <h3>Jogadores</h3>

                    <?php if (count($rankingPlayers) === 0): ?>
                        <p class="admin-note">Nenhum jogador cadastrado.</p>
                    <?php endif; ?>

                    <?php foreach ($rankingPlayers as $index => $player): ?>
                        <div class="admin-ranking-item">
                            <span><?= $index + 1 ?>º <?= htmlspecialchars($player['name']) ?></span>
                            <strong><?= (int)$player['total_score'] ?> pts</strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="admin-ranking-block">
                    <h3>Times</h3>

                    <?php if (count($rankingTeams) === 0): ?>
                        <p class="admin-note">Nenhum time cadastrado.</p>
                    <?php endif; ?>

                    <?php foreach ($rankingTeams as $index => $team): ?>
                        <div class="admin-ranking-item">
                            <span><?= $index + 1 ?>º <?= htmlspecialchars($team['name']) ?></span>
                            <strong><?= (int)$team['total_score'] ?> pts</strong>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

        </div>

        <div class="admin-panel">
            <h2>Histórico de Resultados</h2>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Jogo</th>
                            <th>Jogador vencedor</th>
                            <th>Time vencedor</th>
                            <th>Pontos</th>
                            <th>Criado por</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($matches) === 0): ?>
                            <tr>
                                <td colspan="7">Nenhum resultado lançado ainda.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($matches as $match): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($match['created_at'])) ?></td>
                                <td><?= htmlspecialchars($match['game_name']) ?></td>
                                <td><?= $match['winner_user_name'] ? htmlspecialchars($match['winner_user_name']) : '-' ?></td>
                                <td><?= $match['winner_team_name'] ? htmlspecialchars($match['winner_team_name']) : '-' ?></td>
                                <td>
                                    Jogador: <?= (int)$match['player_points'] ?> |
                                    Time: <?= (int)$match['team_points'] ?>
                                </td>
                                <td><?= $match['created_by_name'] ? htmlspecialchars($match['created_by_name']) : '-' ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Remover este resultado? A pontuação será revertida.');">
                                        <input type="hidden" name="action" value="delete_result">
                                        <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                                        <button type="submit" class="admin-danger-btn">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </main>

</section>

</body>
</html>