<?php
/**
 * news-fetch.php
 * -----------------------------------------------------------------------
 * Reads posts from the `news` MySQL table via PDO and returns them as
 * JSON, newest first (pinned posts first, then by date). news.js calls
 * this with a plain GET fetch() and renders the results client-side.
 *
 * Requires db-config.php's real credentials to be filled in (see the
 * CHANGE_ME placeholders left over from the contact form setup).
 * -----------------------------------------------------------------------
 */

header('Content-Type: application/json');
require __DIR__ . '/db-config.php';

$pdo = getDbConnection();

if ($pdo === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database is not configured yet. Update includes/db-config.php with real credentials.',
    ]);
    exit;
}

try {
    // Pinned posts always surface first, then everything else newest-first.
    $stmt = $pdo->query(
        'SELECT id, title, category, snippet, content, pinned, created_at
         FROM news
         ORDER BY pinned DESC, created_at DESC'
    );
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'posts' => $posts]);
} catch (PDOException $e) {
    error_log('News fetch error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not load news right now.']);
}
