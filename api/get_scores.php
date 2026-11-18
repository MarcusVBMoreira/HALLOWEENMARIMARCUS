<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Quando o admin oculta o placar, as páginas públicas não recebem pontos nem a
// ordem do ranking (vale até para o admin, caso o site esteja na TV).
// O admin continua vendo tudo em Painel > Resultados.
$hidden = isScoreboardHidden($pdo);

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

    if ($hidden) {
        $team['total_score'] = null;
        foreach ($team['players'] as &$player) {
            $player['total_score'] = null;
        }
        unset($player);
    }
}
unset($team);

$ranking = $pdo->query("
    SELECT id, name, total_score
    FROM users
    WHERE role = 'player'
    AND is_active = 1
    ORDER BY " . ($hidden ? "name ASC" : "total_score DESC, name ASC") . "
")->fetchAll();

if ($hidden) {
    foreach ($ranking as &$player) {
        $player['total_score'] = null;
    }
    unset($player);
}

echo json_encode([
    'success' => true,
    'hidden' => $hidden,
    'teams' => $teams,
    'ranking' => $ranking
]);
