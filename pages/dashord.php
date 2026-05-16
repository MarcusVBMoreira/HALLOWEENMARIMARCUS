<?php
require_once '../includes/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h1>Bem-vindo, <?php echo $_SESSION['name']; ?></h1>

<p>Seu time: <?php echo $_SESSION['team_id']; ?></p>

<a href="../logout.php">Sair</a>

</body>
</html>