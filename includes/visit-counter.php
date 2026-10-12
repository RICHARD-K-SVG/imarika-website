<?php
/**
 * visit-counter.php
 * -----------------------------------------------------------------------
 * Returns the site's total visit count as JSON.
 * A POST request counts one new visit (the page sends this once per
 * browsing session); a GET request only reads the current total.
 * Uses the single-row `site_stats` table. Obvious bots are not counted.
 * -----------------------------------------------------------------------
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');
require __DIR__ . '/db-config.php';

$pdo = getDbConnection();
if ($pdo === null) {
    http_response_code(500);
    echo json_encode(['success' => false]);
    exit;
}

try {
    $isCounting = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isBot = $userAgent === '' || preg_match('/bot|crawl|spider|slurp|curl|wget|python|preview/i', $userAgent);

    if ($isCounting && !$isBot) {
        // Creates the row on the very first visit, then adds 1 each time after.
        $pdo->exec(
            'INSERT INTO site_stats (id, total_views) VALUES (1, 1)
             ON DUPLICATE KEY UPDATE total_views = total_views + 1'
        );
    }

    $total = (int) $pdo->query('SELECT total_views FROM site_stats WHERE id = 1')->fetchColumn();
    echo json_encode(['success' => true, 'total' => $total]);
} catch (PDOException $e) {
    error_log('Visit counter error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false]);
}
