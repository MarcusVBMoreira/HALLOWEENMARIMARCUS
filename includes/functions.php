<?php

require_once __DIR__ . '/config.php';

function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

/**
 * Lê uma configuração da tabela event_settings.
 * Se a chave ainda não existir no banco, devolve o valor padrão.
 */
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    $stmt = $pdo->prepare("SELECT setting_value FROM event_settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();

    return $value === false ? $default : (string) $value;
}

/**
 * Grava uma configuração (cria a linha na primeira vez, só isso;
 * não altera a estrutura do banco).
 */
function setSetting(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare("
        INSERT INTO event_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$key, $value]);
}

/** Placar escondido do público (para a revelação final)? */
function isScoreboardHidden(PDO $pdo): bool {
    return getSetting($pdo, 'scoreboard_hidden', '0') === '1';
}
