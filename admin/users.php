<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

$message = '';
$error = '';

$teams = $pdo->query("SELECT id, name FROM teams ORDER BY name ASC")->fetchAll();

$editUser = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'player' LIMIT 1");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $team_id = $_POST['team_id'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $username === '' || $password === '') {
            $error = 'Preencha nome, usuário e senha.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (name, username, password_hash, role, team_id, is_active)
                    VALUES (?, ?, ?, 'player', ?, ?)
                ");

                $stmt->execute([
                    $name,
                    $username,
                    $hash,
                    $team_id !== '' ? $team_id : null,
                    $is_active
                ]);

                $message = 'Jogador cadastrado com sucesso.';
            } catch (PDOException $e) {
                $error = 'Erro ao cadastrar jogador. Talvez o usuário já exista.';
            }
        }
    }

    if ($action === 'update_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $team_id = $_POST['team_id'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($user_id <= 0 || $name === '' || $username === '') {
            $error = 'Dados inválidos para atualizar jogador.';
        } else {
            try {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, username = ?, password_hash = ?, team_id = ?, is_active = ?
                        WHERE id = ? AND role = 'player'
                    ");

                    $stmt->execute([
                        $name,
                        $username,
                        $hash,
                        $team_id !== '' ? $team_id : null,
                        $is_active,
                        $user_id
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, username = ?, team_id = ?, is_active = ?
                        WHERE id = ? AND role = 'player'
                    ");

                    $stmt->execute([
                        $name,
                        $username,
                        $team_id !== '' ? $team_id : null,
                        $is_active,
                        $user_id
                    ]);
                }

                $message = 'Jogador atualizado com sucesso.';
                $editUser = null;
            } catch (PDOException $e) {
                $error = 'Erro ao atualizar jogador. Talvez o usuário já exista.';
            }
        }
    }

    if ($action === 'delete_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);

        if ($user_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'player'");
            $stmt->execute([$user_id]);

            $message = 'Jogador removido com sucesso.';
        }
    }
}

$stmt = $pdo->query("
    SELECT 
        users.id,
        users.name,
        users.username,
        users.total_score,
        users.costume_photo,
        users.is_active,
        teams.name AS team_name
    FROM users
    LEFT JOIN teams ON users.team_id = teams.id
    WHERE users.role = 'player'
    ORDER BY users.name ASC
");

$players = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Jogadores | HALLOWEEN 2026</title>
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
            <a href="<?= BASE_URL ?>admin/users.php" class="active">Jogadores</a>
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
                <h1>Gerenciar Jogadores</h1>
                <p>Cadastre, edite e defina quem está ativo no evento.</p>
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
                <h2><?= $editUser ? 'Editar Jogador' : 'Novo Jogador' ?></h2>

                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="<?= $editUser ? 'update_user' : 'create_user' ?>">

                    <?php if ($editUser): ?>
                        <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nome</label>
                        <input 
                            type="text" 
                            name="name" 
                            value="<?= htmlspecialchars($editUser['name'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Usuário</label>
                        <input 
                            type="text" 
                            name="username" 
                            value="<?= htmlspecialchars($editUser['username'] ?? '') ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label><?= $editUser ? 'Nova senha (opcional)' : 'Senha' ?></label>
                        <input 
                            type="text" 
                            name="password" 
                            placeholder="<?= $editUser ? 'Deixe vazio para manter a senha atual' : 'Senha inicial' ?>"
                            <?= $editUser ? '' : 'required' ?>
                        >
                    </div>

                    <div class="form-group">
                        <label>Time</label>
                        <select name="team_id">
                            <option value="">Sem time por enquanto</option>

                            <?php foreach ($teams as $team): ?>
                                <option 
                                    value="<?= $team['id'] ?>"
                                    <?= isset($editUser['team_id']) && (int)$editUser['team_id'] === (int)$team['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($team['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="is_active"
                                <?= !isset($editUser['is_active']) || (int)$editUser['is_active'] === 1 ? 'checked' : '' ?>
                            >
                            Jogador ativo no evento
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary full">
                        <?= $editUser ? 'Salvar Alterações' : 'Cadastrar Jogador' ?>
                    </button>

                    <?php if ($editUser): ?>
                        <a href="<?= BASE_URL ?>admin/users.php" class="admin-cancel-link">
                            Cancelar edição
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-panel">
                <h2>Resumo</h2>

                <div class="admin-mini-stats">
                    <div>
                        <span>Total de jogadores</span>
                        <strong><?= count($players) ?></strong>
                    </div>

                    <div>
                        <span>Jogadores ativos</span>
                        <strong>
                            <?= count(array_filter($players, fn($player) => (int)$player['is_active'] === 1)) ?>
                        </strong>
                    </div>
                </div>

                <p class="admin-note">
                    Apenas jogadores ativos serão considerados no sorteio dos times e na votação de fantasia.
                </p>
            </div>

        </div>

        <div class="admin-panel">
            <h2>Jogadores Cadastrados</h2>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Time</th>
                            <th>Pontos</th>
                            <th>Fantasia</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($players) === 0): ?>
                            <tr>
                                <td colspan="7">Nenhum jogador cadastrado ainda.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($players as $player): ?>
                            <tr>
                                <td><?= htmlspecialchars($player['name']) ?></td>
                                <td><?= htmlspecialchars($player['username']) ?></td>
                                <td><?= $player['team_name'] ? htmlspecialchars($player['team_name']) : 'Sem time' ?></td>
                                <td><?= (int) $player['total_score'] ?></td>
                                <td><?= $player['costume_photo'] ? 'Enviada' : 'Não enviada' ?></td>
                                <td>
                                    <?php if ((int)$player['is_active'] === 1): ?>
                                        <span class="status-active">Ativo</span>
                                    <?php else: ?>
                                        <span class="status-inactive">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="admin-table-actions">
                                        <a href="<?= BASE_URL ?>admin/users.php?edit=<?= $player['id'] ?>" class="admin-edit-btn">
                                            Editar
                                        </a>

                                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja remover este jogador?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $player['id'] ?>">
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