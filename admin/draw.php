<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$message = '';
$error = '';

function shuffleSecure(array $items): array {
    for ($i = count($items) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
    }

    return $items;
}

$teams = $pdo->query("SELECT * FROM teams ORDER BY id ASC")->fetchAll();

if (count($teams) < 2) {
    $pdo->prepare("INSERT INTO teams (name, color) VALUES (?, ?)")->execute(['Caveiras Caóticas', 'purple']);
    $pdo->prepare("INSERT INTO teams (name, color) VALUES (?, ?)")->execute(['Abóboras Assassinas', 'orange']);

    $teams = $pdo->query("SELECT * FROM teams ORDER BY id ASC")->fetchAll();
}

$teamOne = $teams[0];
$teamTwo = $teams[1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'draw_teams') {
        $stmt = $pdo->query("
            SELECT id, name, team_id
            FROM users
            WHERE role = 'player'
            AND is_active = 1
            ORDER BY id ASC
        ");

        $players = $stmt->fetchAll();

        if (count($players) < 2) {
            $error = 'É necessário ter pelo menos 2 jogadores ativos para sortear.';
        } else {
            $fixedTeamOne = array_filter($players, fn($p) => (int)$p['team_id'] === (int)$teamOne['id']);
            $fixedTeamTwo = array_filter($players, fn($p) => (int)$p['team_id'] === (int)$teamTwo['id']);
            $playersToDraw = array_filter($players, fn($p) => empty($p['team_id']));

            $playersToDraw = shuffleSecure(array_values($playersToDraw));

            $teamOneCount = count($fixedTeamOne);
            $teamTwoCount = count($fixedTeamTwo);

            try {
                $pdo->beginTransaction();

                foreach ($playersToDraw as $player) {
                    if ($teamOneCount <= $teamTwoCount) {
                        $teamId = $teamOne['id'];
                        $teamOneCount++;
                    } else {
                        $teamId = $teamTwo['id'];
                        $teamTwoCount++;
                    }

                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET team_id = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([$teamId, $player['id']]);
                }

                $pdo->commit();
                $message = 'Times sorteados com sucesso. Jogadores já definidos foram mantidos.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Erro ao sortear os times.';
            }
        }
    }

    if ($action === 'clear_draw') {
        $stmt = $pdo->prepare("
            UPDATE users
            SET team_id = NULL
            WHERE role = 'player'
            AND is_active = 1
        ");

        $stmt->execute();

        $message = 'Sorteio removido. Os jogadores ativos ficaram sem time.';
    }

    if ($action === 'update_team_names') {
        $team_one_name = trim($_POST['team_one_name'] ?? '');
        $team_two_name = trim($_POST['team_two_name'] ?? '');

        if ($team_one_name === '' || $team_two_name === '') {
            $error = 'Preencha o nome dos dois times.';
        } else {
            $stmt = $pdo->prepare("UPDATE teams SET name = ? WHERE id = ?");
            $stmt->execute([$team_one_name, $teamOne['id']]);

            $stmt = $pdo->prepare("UPDATE teams SET name = ? WHERE id = ?");
            $stmt->execute([$team_two_name, $teamTwo['id']]);

            $message = 'Nomes dos times atualizados com sucesso.';

            $teams = $pdo->query("SELECT * FROM teams ORDER BY id ASC")->fetchAll();
            $teamOne = $teams[0];
            $teamTwo = $teams[1];
        }
    }
    if ($action === 'set_fixed_players') {
        $fixedPlayers = $_POST['fixed_players'] ?? [];

        try {
            $pdo->beginTransaction();

            $pdo->query("
                UPDATE users
                SET team_id = NULL
                WHERE role = 'player'
                AND is_active = 1
            ");

            foreach ($fixedPlayers as $userId => $teamId) {
                $userId = (int)$userId;
                $teamId = (int)$teamId;

                if ($userId > 0 && in_array($teamId, [(int)$teamOne['id'], (int)$teamTwo['id']], true)) {
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET team_id = ?
                        WHERE id = ?
                        AND role = 'player'
                        AND is_active = 1
                    ");

                    $stmt->execute([$teamId, $userId]);
                }
            }

            $pdo->commit();
            $message = 'Líderes/fixos definidos com sucesso.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erro ao definir líderes/fixos.';
        }
    }
}

$activePlayers = $pdo->query("
    SELECT users.id, users.name, users.team_id, teams.name AS team_name
    FROM users
    LEFT JOIN teams ON users.team_id = teams.id
    WHERE users.role = 'player'
    AND users.is_active = 1
    ORDER BY users.name ASC
")->fetchAll();

$inactivePlayers = $pdo->query("
    SELECT id, name
    FROM users
    WHERE role = 'player'
    AND is_active = 0
    ORDER BY name ASC
")->fetchAll();

$teamOnePlayers = array_filter($activePlayers, fn($player) => (int)$player['team_id'] === (int)$teamOne['id']);
$teamTwoPlayers = array_filter($activePlayers, fn($player) => (int)$player['team_id'] === (int)$teamTwo['id']);
$withoutTeamPlayers = array_filter($activePlayers, fn($player) => empty($player['team_id']));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sorteio dos Times | HALLOWEEN 2026</title>
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
            <a href="<?= BASE_URL ?>admin/draw.php" class="active">Sorteio</a>
            <a href="<?= BASE_URL ?>admin/costume_votes.php">Fantasias</a>
            <a href="<?= BASE_URL ?>index.php">Ver Site</a>
            <a href="<?= BASE_URL ?>logout.php">Sair</a>
        </nav>
    </aside>

    <main class="admin-content">

        <div class="admin-header">
            <div>
                <h1>Sorteio dos Times</h1>
                <p>Somente jogadores ativos entram no sorteio.</p>
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

        <div class="admin-panel draw-panel">
            
            <h2>Definir Líderes / Jogadores Fixos</h2>

            <p class="admin-note">
                Escolha jogadores que devem ficar obrigatoriamente em um time. Eles não serão sorteados.
            </p>

            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="set_fixed_players">

                <div class="fixed-players-list">
                    <?php foreach ($activePlayers as $player): ?>
                        <div class="fixed-player-item">
                            <span><?= htmlspecialchars($player['name']) ?></span>

                            <select name="fixed_players[<?= (int)$player['id'] ?>]">
                                <option value="">Sortear normalmente</option>
                                <option 
                                    value="<?= (int)$teamOne['id'] ?>"
                                    <?= (int)$player['team_id'] === (int)$teamOne['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($teamOne['name']) ?>
                                </option>
                                <option 
                                    value="<?= (int)$teamTwo['id'] ?>"
                                    <?= (int)$player['team_id'] === (int)$teamTwo['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($teamTwo['name']) ?>
                                </option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-secondary full">
                    Salvar Jogadores Fixos
                </button>
            </form>

            <div class="draw-controls">
                <form method="POST" id="drawForm">
                    <input type="hidden" name="action" value="draw_teams">
                    <button type="button" class="btn btn-primary" id="drawButton">
                        <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/dado.png" class="icon-img-admin" alt="Partidas"></span> Sortear Times
                    </button>
                </form>

                <form method="POST" onsubmit="return confirm('Tem certeza que deseja apagar o sorteio atual?');">
                    <input type="hidden" name="action" value="clear_draw">
                    <button type="submit" class="btn btn-secondary">
                        <span class="img-icon-admin"><img src="<?= BASE_URL ?>assets/images/icons/lixo.png" class="icon-img-admin" alt="Excluir"></span> Excluir Sorteio
                    </button>
                </form>
            </div>

            <div class="draw-animation" id="drawAnimation">
                <div class="draw-dice">🎲</div>
                <p>Sorteando jogadores...</p>
            </div>

        </div>

        <div class="admin-panel">
            <h2>Alterar Nome dos Times</h2>

            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="update_team_names">

                <div class="admin-form-row">
                    <div class="form-group">
                        <label>Time Roxo</label>
                        <input 
                            type="text" 
                            name="team_one_name" 
                            value="<?= htmlspecialchars($teamOne['name']) ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Time Laranja</label>
                        <input 
                            type="text" 
                            name="team_two_name" 
                            value="<?= htmlspecialchars($teamTwo['name']) ?>" 
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary full">
                    Salvar Nome dos Times
                </button>
            </form>
        </div>

        <div class="draw-grid">

            <div class="admin-panel draw-team-card team-purple">
                <div class="team-header">
                    <div class="team-icon">💀</div>
                    <h2><?= htmlspecialchars($teamOne['name']) ?></h2>
                </div>

                <?php if (count($teamOnePlayers) === 0): ?>
                    <p class="admin-note">Nenhum jogador neste time ainda.</p>
                <?php endif; ?>

                <ul class="draw-player-list">
                    <?php foreach ($teamOnePlayers as $player): ?>
                        <li><?= htmlspecialchars($player['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="admin-panel draw-team-card team-orange">
                <div class="team-header">
                    <div class="team-icon">🎃</div>
                    <h2><?= htmlspecialchars($teamTwo['name']) ?></h2>
                </div>

                <?php if (count($teamTwoPlayers) === 0): ?>
                    <p class="admin-note">Nenhum jogador neste time ainda.</p>
                <?php endif; ?>

                <ul class="draw-player-list">
                    <?php foreach ($teamTwoPlayers as $player): ?>
                        <li><?= htmlspecialchars($player['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>

        <div class="admin-grid-two">

            <div class="admin-panel">
                <h2>Jogadores ativos sem time</h2>

                <?php if (count($withoutTeamPlayers) === 0): ?>
                    <p class="admin-note">Nenhum jogador ativo sem time.</p>
                <?php endif; ?>

                <ul class="admin-simple-list">
                    <?php foreach ($withoutTeamPlayers as $player): ?>
                        <li><?= htmlspecialchars($player['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="admin-panel">
                <h2>Jogadores inativos</h2>

                <?php if (count($inactivePlayers) === 0): ?>
                    <p class="admin-note">Nenhum jogador inativo.</p>
                <?php endif; ?>

                <ul class="admin-simple-list muted">
                    <?php foreach ($inactivePlayers as $player): ?>
                        <li><?= htmlspecialchars($player['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>

    </main>

</section>

<script>
const drawButton = document.getElementById('drawButton');
const drawForm = document.getElementById('drawForm');
const drawAnimation = document.getElementById('drawAnimation');

if (drawButton) {
    drawButton.addEventListener('click', () => {
        drawAnimation.classList.add('active');
        drawButton.disabled = true;

        setTimeout(() => {
            drawForm.submit();
        }, 1800);
    });
}
</script>

</body>
</html>