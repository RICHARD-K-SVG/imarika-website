<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$error = '';
$csrfToken = adminCsrfToken();
$allowedStatuses = ['Pending', 'Reviewed', 'Accepted', 'Rejected'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!adminCsrfTokenIsValid()) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } elseif ($pdo === null) {
        $error = 'Admission records are temporarily unavailable. Please try again later.';
    } else {
        try {
            if (isset($_POST['update_status'])) {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $status = $_POST['status'] ?? '';
                if ($id === false || !is_string($status) || !in_array($status, $allowedStatuses, true)) {
                    $error = 'Select a valid application and status.';
                } else {
                    $stmt = $pdo->prepare('UPDATE admissions SET status = :status WHERE id = :id');
                    $stmt->execute(['status' => $status, 'id' => $id]);
                    $message = $stmt->rowCount() > 0 ? 'Status updated.' : 'Application status is unchanged.';
                }
            } elseif (isset($_POST['delete_id'])) {
                $id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id === false) {
                    $error = 'Select a valid application to delete.';
                } else {
                    $columns = $pdo->query('SHOW COLUMNS FROM admissions')->fetchAll(PDO::FETCH_COLUMN);
                    $hasDocumentPaths = in_array('birth_certificate_path', $columns, true)
                        && in_array('student_photo_path', $columns, true);

                    if ($hasDocumentPaths) {
                        $stmt = $pdo->prepare('SELECT birth_certificate_path, student_photo_path FROM admissions WHERE id = :id');
                    } else {
                        $stmt = $pdo->prepare('SELECT id FROM admissions WHERE id = :id');
                    }
                    $stmt->execute(['id' => $id]);
                    $toDelete = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($toDelete) {
                        $pdo->prepare('DELETE FROM admissions WHERE id = :id')->execute(['id' => $id]);
                        if ($hasDocumentPaths) {
                            $uploadDirectory = realpath(__DIR__ . '/../includes/uploads/admissions');
                            foreach (['birth_certificate_path', 'student_photo_path'] as $column) {
                                $filePath = realpath(__DIR__ . '/../' . ($toDelete[$column] ?? ''));
                                if ($uploadDirectory && $filePath && str_starts_with($filePath, $uploadDirectory . DIRECTORY_SEPARATOR) && is_file($filePath) && !unlink($filePath)) {
                                    error_log('Admin admissions: failed to delete an uploaded document.');
                                }
                            }
                        }
                        $message = 'Application deleted.';
                    } else {
                        $error = 'That application could not be found.';
                    }
                }
            }
        } catch (PDOException $exception) {
            error_log('Admin admissions action failed: ' . $exception->getMessage());
            $error = 'The application could not be updated. Please try again.';
        }
    }
}

$rows = [];
$listingAvailable = false;
if ($pdo === null) {
    $error = $error ?: 'Admission records are temporarily unavailable. Please try again later.';
} else {
    try {
        $rows = $pdo->query('SELECT * FROM admissions ORDER BY submitted_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        $listingAvailable = true;
    } catch (PDOException $exception) {
        error_log('Admin admissions listing failed: ' . $exception->getMessage());
        $error = 'Applications could not be loaded. Please check the database setup and try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admissions | Imarika Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&amp;family=Work+Sans:wght@400;500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css?v=20261016-responsive-perf1">
  <link rel="stylesheet" href="admin.css?v=20261019-records1">
  <script src="admin-record-search.js?v=20261019-records1" defer></script>
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <div class="admin-page-heading">
      <div>
        <p class="admin-page-eyebrow">Student records</p>
        <h1>Admission applications</h1>
        <p>Review application details, documents and each learner’s admission status.</p>
      </div>
    </div>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($listingAvailable && !$rows): ?>
      <div class="admin-empty-state">
        <span class="admin-empty-state-icon" aria-hidden="true">+</span>
        <h2>No applications yet</h2>
        <p>New admission applications will appear here when families submit them.</p>
      </div>
    <?php elseif ($listingAvailable): ?>
    <section class="admin-record-search" data-record-search data-search-label="applications" aria-label="Search admission applications">
      <div class="admin-record-search-bar">
        <label for="application-search">Find an application</label>
        <input type="search" id="application-search" data-record-search-input placeholder="Search student, guardian, phone or status">
        <p class="admin-record-search-count" data-record-search-count aria-live="polite"><?= count($rows) ?> <?= count($rows) === 1 ? 'application' : 'applications' ?></p>
      </div>
      <div class="admin-table-wrap admin-responsive-records" data-record-search-results>
      <table class="admin-table admin-applications-table" data-record-search-table>
        <thead>
          <tr>
            <th>Student</th><th>DOB</th><th>Gender</th><th>Level</th><th>Guardian</th>
            <th>Phone</th><th>Email</th><th>Documents</th><th>Submitted</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Student"><?= htmlspecialchars($r['student_name'] ?? '') ?></td>
            <td data-label="Date of birth"><?= htmlspecialchars($r['dob'] ?? '') ?></td>
            <td data-label="Gender"><?= htmlspecialchars($r['gender'] ?? '') ?></td>
            <td data-label="Level"><?= htmlspecialchars($r['level'] ?? '') ?></td>
            <td data-label="Guardian"><?= htmlspecialchars($r['guardian_name'] ?? '') ?><br><small><?= htmlspecialchars($r['relationship'] ?? '') ?></small></td>
            <td data-label="Phone"><?= htmlspecialchars($r['phone'] ?? '') ?></td>
            <td data-label="Email"><?= htmlspecialchars($r['email'] ?? '—') ?></td>
            <td data-label="Documents">
              <?php if (!empty($r['birth_certificate_path'])): ?>
                <a href="view-upload.php?file=<?= urlencode(basename($r['birth_certificate_path'])) ?>" target="_blank">Birth Cert.</a><br>
              <?php endif; ?>
              <?php if (!empty($r['student_photo_path'])): ?>
                <a href="view-upload.php?file=<?= urlencode(basename($r['student_photo_path'])) ?>" target="_blank">Photo</a>
              <?php endif; ?>
              <?php if (empty($r['birth_certificate_path']) && empty($r['student_photo_path'])): ?>Not provided<?php endif; ?>
            </td>
            <td data-label="Submitted"><?= htmlspecialchars($r['submitted_at'] ?? '') ?></td>
            <td data-label="Status">
              <form method="POST" class="admin-inline-form">
                <input type="hidden" name="admin_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <select name="status">
                  <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>" <?= ($r['status'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" value="1" class="admin-btn-sm">Save</button>
              </form>
            </td>
            <td data-label="Actions">
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this application permanently? This also removes the uploaded documents and cannot be undone.');">
                <input type="hidden" name="admin_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="delete_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <p class="admin-record-search-empty" data-record-search-empty hidden>No applications match your search. Try another name, phone number or status.</p>
    </section>
    <?php endif; ?>
  </main>
  <?php require __DIR__ . '/includes/admin-footer.php'; ?>
</body>
</html>
