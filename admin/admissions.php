<?php
require __DIR__ . '/includes/require-login.php';
require __DIR__ . '/../includes/db-config.php';

$pdo = getDbConnection();
$message = '';
$allowedStatuses = ['Pending', 'Reviewed', 'Accepted', 'Rejected'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $pdo !== null) {
    if (isset($_POST['update_status'])) {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, $allowedStatuses, true) && $id > 0) {
            $stmt = $pdo->prepare('UPDATE admissions SET status = :status WHERE id = :id');
            $stmt->execute(['status' => $status, 'id' => $id]);
            $message = 'Status updated.';
        }
    } elseif (isset($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];

        // Look up the file paths first so we can remove them from disk too —
        // otherwise deleting the row just leaves orphaned documents behind.
        $stmt = $pdo->prepare('SELECT birth_certificate_path, student_photo_path FROM admissions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $toDelete = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($toDelete) {
            foreach (['birth_certificate_path', 'student_photo_path'] as $col) {
                $filePath = __DIR__ . '/../' . $toDelete[$col];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
            $pdo->prepare('DELETE FROM admissions WHERE id = :id')->execute(['id' => $id]);
            $message = 'Application deleted.';
        }
    }
}

$rows = $pdo !== null ? $pdo->query('SELECT * FROM admissions ORDER BY submitted_at DESC')->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admissions | Imarika Admin</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <?php require __DIR__ . '/includes/admin-nav.php'; ?>
  <main class="admin-main">
    <h1>Admission Applications</h1>
    <?php if ($message): ?><p class="admin-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if (!$rows): ?>
      <p>No applications yet.</p>
    <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Student</th><th>DOB</th><th>Gender</th><th>Level</th><th>Guardian</th>
            <th>Phone</th><th>Email</th><th>Documents</th><th>Submitted</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['student_name']) ?></td>
            <td><?= htmlspecialchars($r['dob']) ?></td>
            <td><?= htmlspecialchars($r['gender']) ?></td>
            <td><?= htmlspecialchars($r['level']) ?></td>
            <td><?= htmlspecialchars($r['guardian_name']) ?><br><small><?= htmlspecialchars($r['relationship']) ?></small></td>
            <td><?= htmlspecialchars($r['phone']) ?></td>
            <td><?= htmlspecialchars($r['email'] ?: '—') ?></td>
            <td>
              <a href="view-upload.php?file=<?= urlencode(basename($r['birth_certificate_path'])) ?>" target="_blank">Birth Cert.</a><br>
              <a href="view-upload.php?file=<?= urlencode(basename($r['student_photo_path'])) ?>" target="_blank">Photo</a>
            </td>
            <td><?= htmlspecialchars($r['submitted_at']) ?></td>
            <td>
              <form method="POST" class="admin-inline-form">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <select name="status">
                  <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" value="1" class="admin-btn-sm">Save</button>
              </form>
            </td>
            <td>
              <form method="POST" class="admin-inline-form" onsubmit="return confirm('Delete this application permanently? This also removes the uploaded documents and cannot be undone.');">
                <input type="hidden" name="delete_id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="admin-btn-sm admin-btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </main>
</body>
</html>
