<?php
/*
 * Status da votação de fantasia (usado pela página de votação para
 * liberar a votação automaticamente quando todos enviarem a foto).
 */
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$row = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(costume_photo IS NOT NULL AND costume_photo <> '') AS photos
    FROM users
    WHERE role = 'player'
    AND is_active = 1
")->fetch();

$total = (int) $row['total'];
$photos = (int) $row['photos'];

echo json_encode([
    'success' => true,
    'total' => $total,
    'photos' => $photos,
    'all_uploaded' => $total > 0 && $total === $photos,
]);
