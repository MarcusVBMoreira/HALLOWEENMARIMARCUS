<?php
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$teams = $pdo->query("
    SELECT id, name, color, total_score
    FROM teams
    ORDER BY id ASC
    LIMIT 2
")->fetchAll();

foreach ($teams as &$team) {
    $stmt = $pdo->prepare("
        SELECT id, name, total_score
        FROM users
        WHERE role = 'player'
        AND is_active = 1
        AND team_id = ?
        ORDER BY name ASC
    ");
    $stmt->execute([$team['id']]);
    $team['players'] = $stmt->fetchAll();
}

$ranking = $pdo->query("
    SELECT id, name, total_score
    FROM users
    WHERE role = 'player'
    AND is_active = 1
    ORDER BY total_score DESC, name ASC
")->fetchAll();

echo json_encode([
    'success' => true,
    'teams' => $teams,
    'ranking' => $ranking
]);