<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: " . BASE_URL . "admin/index.php");
    } else {
        header("Location: " . BASE_URL . "index.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Preencha usuário e senha.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "admin/index.php");
            } else {
                header("Location: " . BASE_URL . "index.php");
            }
            exit;
        } else {
            $error = 'Usuário ou senha inválidos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login | HALLOWEEN 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Creepster&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/images/favicon.png">
</head>
<body>

<section class="login-page">
    <div class="login-card">

        <div class="login-logo">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo Halloween 2026">
        </div>

        <h1>ACESSO RESTRITO</h1>
        <p>Entre com seu usuário e senha para acessar o evento.</p>

        <?php if ($error): ?>
            <div class="login-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" name="username" id="username" autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" id="password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary full">
                ENTRAR
            </button>
        </form>

        <a href="<?= BASE_URL ?>index.php" class="back-home">
            <span class="img-icon"><img src="assets/images/icons/seta-esquerda-branco.png" class="icon-img" alt=""></span> Voltar para o início
        </a>

    </div>
</section>

</body>
</html>