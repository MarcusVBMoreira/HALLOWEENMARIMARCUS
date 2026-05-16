<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$message = '';
$error = '';

function uploadGameImage($file, $currentImage = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return $currentImage;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Formato de imagem inválido. Use JPG, PNG ou WEBP.');
    }

    $extension = match ($fileType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    };

    $uploadDir = __DIR__ . '/../assets/uploads/games/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = 'game_' . time() . '_' . random_int(1000, 9999) . '.' . $extension;
    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Erro ao salvar imagem do jogo.');
    }

    if ($currentImage) {
        $oldFile = __DIR__ . '/../' . $currentImage;

        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    return 'assets/uploads/games/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create_game') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $how_to_play = trim($_POST['how_to_play'] ?? '');
            $rules = trim($_POST['rules'] ?? '');
            $player_points = (int) ($_POST['player_points'] ?? 0);
            $team_points = (int) ($_POST['team_points'] ?? 0);
            $game_order = (int) ($_POST['game_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '') {
                $error = 'Preencha o nome do jogo.';
            } else {
                $imagePath = uploadGameImage($_FILES['image'] ?? null);

                $stmt = $pdo->prepare("
                    INSERT INTO games 
                    (name, image, description, how_to_play, rules, player_points, team_points, game_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $name,
                    $imagePath,
                    $description,
                    $how_to_play,
                    $rules,
                    $player_points,
                    $team_points,
                    $game_order,
                    $is_active
                ]);

                $message = 'Jogo cadastrado com sucesso.';
            }
        }

        if ($action === 'update_game') {
            $game_id = (int) ($_POST['game_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $how_to_play = trim($_POST['how_to_play'] ?? '');
            $rules = trim($_POST['rules'] ?? '');
            $player_points = (int) ($_POST['player_points'] ?? 0);
            $team_points = (int) ($_POST['team_points'] ?? 0);
            $game_order = (int) ($_POST['game_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($game_id <= 0 || $name === '') {
                $error = 'Dados inválidos para editar o jogo.';
            } else {
                $stmt = $pdo->prepare("SELECT image FROM games WHERE id = ? LIMIT 1");
                $stmt->execute([$game_id]);
                $currentGame = $stmt->fetch();

                $imagePath = uploadGameImage($_FILES['image'] ?? null, $currentGame['image'] ?? null);

                $stmt = $pdo->prepare("
                    UPDATE games
                    SET 
                        name = ?,
                        image = ?,
                        description = ?,
                        how_to_play = ?,
                        rules = ?,
                        player_points = ?,
                        team_points = ?,
                        game_order = ?,
                        is_active = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $name,
                    $imagePath,
                    $description,
                    $how_to_play,
                    $rules,
                    $player_points,
                    $team_points,
                    $game_order,
                    $is_active,
                    $game_id
                ]);

                $message = 'Jogo atualizado com sucesso.';
            }
        }

        if ($action === 'delete_game') {
            $game_id = (int) ($_POST['game_id'] ?? 0);

            if ($game_id > 0) {
                $stmt = $pdo->prepare("SELECT image FROM games WHERE id = ? LIMIT 1");
                $stmt->execute([$game_id]);
                $game = $stmt->fetch();

                $stmt = $pdo->prepare("DELETE FROM games WHERE id = ?");
                $stmt->execute([$game_id]);

                if (!empty($game['image'])) {
                    $oldFile = __DIR__ . '/../' . $game['image'];

                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $message = 'Jogo excluído com sucesso.';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$editGame = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ? LIMIT 1");
    $stmt->execute([$editId]);
    $editGame = $stmt->fetch();
}

$games = $pdo->query("
    SELECT *
    FROM games
    ORDER BY game_order ASC, id ASC
")->fetchAll();

function gameTypeLabel($game) {
    $player = (int)$game['player_points'];
    $team = (int)$game['team_points'];

    if ($player > 0 && $team > 0) return 'Individual + Equipe';
    if ($player > 0) return 'Individual';
    if ($team > 0) return 'Coletivo';

    return 'Sem pontuação';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Jogos | HALLOWEEN 2026</title>
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
            <a href="<?= BASE_URL ?>admin/games.php" class="active">Jogos</a>
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
                <h1>Gerenciar Jogos</h1>
                <p>Cadastre imagem, regras, descrição, modo de jogar e pontuação.</p>
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
                <h2><?= $editGame ? 'Editar Jogo' : 'Novo Jogo' ?></h2>

                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="<?= $editGame ? 'update_game' : 'create_game' ?>">

                    <?php if ($editGame): ?>
                        <input type="hidden" name="game_id" value="<?= (int)$editGame['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nome do jogo</label>
                        <input 
                            type="text" 
                            name="name" 
                            placeholder="Ex: Uno, Dixit, Imagem & Ação"
                            value="<?= htmlspecialchars($editGame['name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Imagem do jogo</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

                        <?php if (!empty($editGame['image'])): ?>
                            <div class="admin-current-image">
                                <span>Imagem atual:</span>
                                <img src="<?= BASE_URL . htmlspecialchars($editGame['image']) ?>" alt="Imagem atual do jogo">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea 
                            name="description" 
                            placeholder="Descrição geral do jogo"
                        ><?= htmlspecialchars($editGame['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Como jogar</label>
                        <textarea 
                            name="how_to_play" 
                            placeholder="Explique como o jogo será jogado no evento"
                        ><?= htmlspecialchars($editGame['how_to_play'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Regras</label>
                        <textarea 
                            name="rules" 
                            placeholder="Liste as regras principais"
                        ><?= htmlspecialchars($editGame['rules'] ?? '') ?></textarea>
                    </div>

                    <div class="admin-form-row">
                        <div class="form-group">
                            <label>Pontos para jogador</label>
                            <input 
                                type="number" 
                                name="player_points" 
                                min="0"
                                value="<?= htmlspecialchars($editGame['player_points'] ?? 0) ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label>Pontos para equipe</label>
                            <input 
                                type="number" 
                                name="team_points" 
                                min="0"
                                value="<?= htmlspecialchars($editGame['team_points'] ?? 0) ?>"
                            >
                        </div>
                    </div>

                    <div class="admin-form-row">
                        <div class="form-group">
                            <label>Ordem do jogo</label>
                            <input 
                                type="number" 
                                name="game_order" 
                                min="0"
                                value="<?= htmlspecialchars($editGame['game_order'] ?? 0) ?>"
                            >
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input 
                                    type="checkbox" 
                                    name="is_active"
                                    <?= !isset($editGame['is_active']) || (int)$editGame['is_active'] === 1 ? 'checked' : '' ?>
                                >
                                Jogo ativo
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary full">
                        <?= $editGame ? 'Salvar Alterações' : 'Cadastrar Jogo' ?>
                    </button>

                    <?php if ($editGame): ?>
                        <a href="<?= BASE_URL ?>admin/games.php" class="admin-cancel-link">
                            Cancelar edição
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-panel">
                <h2>Resumo da pontuação</h2>

                <p class="admin-note">
                    Se um jogo pontuar jogador, coloque valor em <strong>Pontos para jogador</strong>.
                </p>

                <p class="admin-note">
                    Se pontuar equipe, coloque valor em <strong>Pontos para equipe</strong>.
                </p>

                <p class="admin-note">
                    Se não pontuar em uma categoria, deixe como <strong>0</strong>.
                </p>

                <div class="admin-mini-stats">
                    <div>
                        <span>Total de jogos</span>
                        <strong><?= count($games) ?></strong>
                    </div>

                    <div>
                        <span>Ativos</span>
                        <strong><?= count(array_filter($games, fn($game) => (int)$game['is_active'] === 1)) ?></strong>
                    </div>
                </div>
            </div>

        </div>

        <div class="admin-panel">
            <h2>Jogos Cadastrados</h2>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ordem</th>
                            <th>Imagem</th>
                            <th>Jogo</th>
                            <th>Tipo</th>
                            <th>Pontos jogador</th>
                            <th>Pontos equipe</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($games) === 0): ?>
                            <tr>
                                <td colspan="8">Nenhum jogo cadastrado ainda.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td><?= (int)$game['game_order'] ?></td>

                                <td>
                                    <?php if (!empty($game['image'])): ?>
                                        <img 
                                            class="admin-game-thumb" 
                                            src="<?= BASE_URL . htmlspecialchars($game['image']) ?>" 
                                            alt="<?= htmlspecialchars($game['name']) ?>"
                                        >
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($game['name']) ?></strong>

                                    <?php if (!empty($game['description'])): ?>
                                        <small class="admin-table-description">
                                            <?= htmlspecialchars(mb_strimwidth($game['description'], 0, 90, '...')) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars(gameTypeLabel($game)) ?></td>
                                <td><?= (int)$game['player_points'] ?></td>
                                <td><?= (int)$game['team_points'] ?></td>

                                <td>
                                    <?php if ((int)$game['is_active'] === 1): ?>
                                        <span class="status-active">Ativo</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Inativo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="admin-table-actions">
                                        <a href="<?= BASE_URL ?>admin/games.php?edit=<?= (int)$game['id'] ?>" class="admin-edit-btn">
                                            Editar
                                        </a>

                                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este jogo?');">
                                            <input type="hidden" name="action" value="delete_game">
                                            <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
                                            <button type="submit" class="admin-danger-btn">Excluir</button>
                                        </form>
                                    </div>
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