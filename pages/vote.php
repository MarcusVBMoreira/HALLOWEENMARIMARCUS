<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$error = '';

/* =========================
   CONFIGURAÇÃO DE VOTAÇÃO
========================= */
$stmt = $pdo->prepare("
    SELECT setting_value 
    FROM event_settings 
    WHERE setting_key = 'costume_voting_enabled'
    LIMIT 1
");
$stmt->execute();
$votingEnabled = $stmt->fetchColumn() === '1';

/* =========================
   USUÁRIO ATUAL
========================= */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if (!$currentUser || (int)$currentUser['is_active'] !== 1) {
    die("Você não está ativo no evento.");
}

if (!$votingEnabled) {
    die("A votação ainda não foi liberada.");
}

/* =========================
   USUÁRIOS ATIVOS
========================= */
$activeUsers = $pdo->query("
    SELECT id, name, costume_photo
    FROM users
    WHERE role = 'player'
    AND is_active = 1
    ORDER BY name ASC
")->fetchAll();

$totalActive = count($activeUsers);
$totalPhotos = count(array_filter($activeUsers, fn($u) => !empty($u['costume_photo'])));
$allUploaded = $totalActive > 0 && $totalActive === $totalPhotos;

/* =========================
   AÇÕES
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (($action === 'upload_photo' || $action === 'delete_photo') && $allUploaded) {
        $error = 'Todas as fotos já foram enviadas. Não é mais possível alterar ou excluir fotos.';
    }

    if ($action === 'upload_photo' && !$allUploaded) {
        if (!isset($_FILES['costume_photo']) || $_FILES['costume_photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Envie uma imagem válida.';
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $type = mime_content_type($_FILES['costume_photo']['tmp_name']);

            if (!in_array($type, $allowed)) {
                $error = 'Formato inválido. Use JPG, PNG ou WEBP.';
            } else {
                $ext = match ($type) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                };

                $dir = __DIR__ . '/../assets/uploads/costumes/';

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $fileName = 'fantasia_' . $userId . '_' . time() . '.' . $ext;
                $dest = $dir . $fileName;

                if (!empty($currentUser['costume_photo'])) {
                    $old = __DIR__ . '/../' . $currentUser['costume_photo'];

                    if (file_exists($old)) {
                        unlink($old);
                    }
                }

                if (move_uploaded_file($_FILES['costume_photo']['tmp_name'], $dest)) {
                    $path = 'assets/uploads/costumes/' . $fileName;

                    $stmt = $pdo->prepare("UPDATE users SET costume_photo = ? WHERE id = ?");
                    $stmt->execute([$path, $userId]);

                    $currentUser['costume_photo'] = $path;
                    $message = 'Foto enviada com sucesso!';
                } else {
                    $error = 'Erro ao salvar a imagem.';
                }
            }
        }
    }

    if ($action === 'delete_photo' && !$allUploaded) {
        if (!empty($currentUser['costume_photo'])) {
            $old = __DIR__ . '/../' . $currentUser['costume_photo'];

            if (file_exists($old)) {
                unlink($old);
            }

            $stmt = $pdo->prepare("UPDATE users SET costume_photo = NULL WHERE id = ?");
            $stmt->execute([$userId]);

            $stmt = $pdo->prepare("DELETE FROM costume_votes WHERE voted_user_id = ?");
            $stmt->execute([$userId]);

            $currentUser['costume_photo'] = null;
            $message = 'Foto excluída com sucesso.';
        }
    }

    if ($action === 'vote') {
        if (!$allUploaded) {
            $error = 'A votação só começa quando todos enviarem foto.';
        } else {
            $voted = (int) ($_POST['voted_user_id'] ?? 0);

            if ($voted === $userId) {
                $error = 'Você não pode votar em si mesmo.';
            } else {
                $stmt = $pdo->prepare("
                    SELECT id 
                    FROM users
                    WHERE id = ?
                    AND role = 'player'
                    AND is_active = 1
                    AND costume_photo IS NOT NULL
                    LIMIT 1
                ");
                $stmt->execute([$voted]);

                if (!$stmt->fetch()) {
                    $error = 'Candidato inválido.';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO costume_votes (voter_user_id, voted_user_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$userId, $voted]);

                        $message = 'Voto registrado com sucesso!';
                    } catch (PDOException $e) {
                        $error = 'Você já votou.';
                    }
                }
            }
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();

    $activeUsers = $pdo->query("
        SELECT id, name, costume_photo
        FROM users
        WHERE role = 'player'
        AND is_active = 1
        ORDER BY name ASC
    ")->fetchAll();

    $totalActive = count($activeUsers);
    $totalPhotos = count(array_filter($activeUsers, fn($u) => !empty($u['costume_photo'])));
    $allUploaded = $totalActive > 0 && $totalActive === $totalPhotos;
}

/* =========================
   VOTO DO USUÁRIO
========================= */
$stmt = $pdo->prepare("SELECT * FROM costume_votes WHERE voter_user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$myVote = $stmt->fetch();

/* =========================
   CANDIDATOS
========================= */
$candidates = array_filter($activeUsers, fn($u) =>
    (int)$u['id'] !== $userId && !empty($u['costume_photo'])
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Votação de Fantasia | HALLOWEEN 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Creepster&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>

<section class="vote-page">
    <div class="vote-container">

        <h1>Votação de Fantasia</h1>
        <p class="vote-subtitle">Envie sua foto e vote na melhor fantasia da noite.</p>

        <?php if ($message): ?>
            <div class="admin-alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="admin-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="vote-panel">
            <h2>Sua Fantasia</h2>

            <?php if (!empty($currentUser['costume_photo'])): ?>

                <img 
                    class="my-costume-photo" 
                    src="<?= BASE_URL . htmlspecialchars($currentUser['costume_photo']) ?>" 
                    alt="Minha fantasia"
                >

                <?php if (!$allUploaded): ?>
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <input type="hidden" name="action" value="upload_photo">

                        <div class="form-group">
                            <label>Substituir foto</label>
                            <input 
                                type="file" 
                                name="costume_photo" 
                                accept="image/jpeg,image/png,image/webp" 
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-secondary full">
                            Substituir Foto
                        </button>
                    </form>

                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua foto?');">
                        <input type="hidden" name="action" value="delete_photo">

                        <button type="submit" class="admin-danger-btn full">
                            Excluir Foto
                        </button>
                    </form>
                <?php else: ?>
                    <p class="admin-note">
                        Todas as fotos já foram enviadas. A partir de agora não é mais possível alterar ou excluir sua foto.
                    </p>
                <?php endif; ?>

            <?php else: ?>

                <?php if (!$allUploaded): ?>
                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <input type="hidden" name="action" value="upload_photo">

                        <div class="form-group">
                            <label>Enviar foto</label>
                            <input 
                                type="file" 
                                name="costume_photo" 
                                accept="image/jpeg,image/png,image/webp" 
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary full">
                            Enviar Foto
                        </button>
                    </form>
                <?php else: ?>
                    <p class="admin-note">
                        O envio de fotos foi encerrado.
                    </p>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <div class="vote-panel">
            <h2>Status da Votação</h2>

            <p class="admin-note">
                <?= (int)$totalPhotos ?> de <?= (int)$totalActive ?> jogadores ativos já enviaram foto.
            </p>

            <?php if (!$allUploaded): ?>
                <p class="admin-note">
                    A votação será liberada aqui quando todos os jogadores ativos enviarem suas fotos.
                </p>
            <?php elseif ($myVote): ?>
                <p class="admin-note">
                    Seu voto já foi registrado.
                </p>
            <?php else: ?>

                <div class="costume-grid">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="costume-card">
                            <img 
                                src="<?= BASE_URL . htmlspecialchars($candidate['costume_photo']) ?>" 
                                alt="Fantasia de <?= htmlspecialchars($candidate['name']) ?>"
                            >

                            <h3><?= htmlspecialchars($candidate['name']) ?></h3>

                            <form method="POST">
                                <input type="hidden" name="action" value="vote">
                                <input type="hidden" name="voted_user_id" value="<?= (int)$candidate['id'] ?>">

                                <button type="submit" class="btn btn-primary full">
                                    Votar
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>

        <a href="<?= BASE_URL ?>index.php" class="back-home">
            ← Voltar para o início
        </a>

    </div>
</section>

</body>
</html>