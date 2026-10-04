<?php
$pageTitle = "Customer & B2B Inquiries | Zenkit Admin";
require_once __DIR__ . '/includes/admin_header.php';
$db = getDB();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $inqId = (int)$_POST['inquiry_id'];
    $newStatus = trim($_POST['status']);
    $stmt = $db->prepare("UPDATE contact_inquiries SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $inqId]);
    setFlash('success', "Inquiry #$inqId status marked as $newStatus.");
    header("Location: inquiries.php");
    exit;
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM contact_inquiries WHERE id = ?");
    $stmt->execute([$delId]);
    setFlash('info', "Inquiry deleted.");
    header("Location: inquiries.php");
    exit;
}

$inquiries = $db->query("SELECT * FROM contact_inquiries ORDER BY id DESC")->fetchAll();
?>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: #1E293B; margin-bottom: 4px;">Customer & B2B Inquiries</h1>
    <p style="color: #64748B; font-size: 0.88rem;">Manage support tickets, warranty claims, and corporate procurement leads.</p>
  </div>
</div>

<div style="background: #FFFFFF; border: 1px solid var(--border-light); border-radius: var(--radius-xs); padding: 20px; box-shadow: var(--shadow-sm);">
  <?php if (empty($inquiries)): ?>
    <div style="text-align: center; padding: 40px; color: #64748B;">No incoming inquiries logged yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Contact Details</th>
            <th>Subject / Reason</th>
            <th>Message</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inquiries as $inq): ?>
            <tr>
              <td>#<?= $inq['id'] ?></td>
              <td>
                <div style="font-weight: 700; color: #1E293B;"><?= htmlspecialchars($inq['name']) ?></div>
                <div style="font-size: 11px; color: #64748B;"><?= htmlspecialchars($inq['email']) ?></div>
                <?php if (!empty($inq['phone'])): ?>
                  <div style="font-size: 11px; color: #64748B;">📞 <?= htmlspecialchars($inq['phone']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <strong style="color: var(--fk-blue); font-size: 12px;"><?= htmlspecialchars($inq['subject']) ?></strong>
              </td>
              <td style="font-size: 12px; max-width: 320px; color: #334155; line-height: 1.4;">
                <?= nl2br(htmlspecialchars($inq['message'])) ?>
              </td>
              <td style="font-size: 11px; color: #94A3B8;"><?= date('M d, H:i', strtotime($inq['created_at'])) ?></td>
              <td>
                <form action="inquiries.php" method="POST" style="display: flex; gap: 4px;">
                  <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                  <input type="hidden" name="update_status" value="1">
                  <select name="status" class="form-control" onchange="this.form.submit()" style="font-size: 11px; padding: 2px 6px;">
                    <option value="New" <?= $inq['status'] === 'New' ? 'selected' : '' ?>>New</option>
                    <option value="In Progress" <?= $inq['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Resolved" <?= $inq['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                  </select>
                </form>
              </td>
              <td>
                <a href="inquiries.php?action=delete&id=<?= $inq['id'] ?>" onclick="return confirm('Delete inquiry?')" style="color: #DC2626; font-size: 11px; font-weight: 700;">
                  Delete
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
