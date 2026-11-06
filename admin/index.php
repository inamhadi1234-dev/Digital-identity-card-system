<?php
require __DIR__ . '/../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['application_id'])) {
    $applicationId = (int) $_POST['application_id'];
    $action = $_POST['action'];
    $allowed = ['approved', 'rejected'];

    if (in_array($action, $allowed, true)) {
        $stmt = $db->prepare('UPDATE applications SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->bind_param('si', $action, $applicationId);
        $stmt->execute();
        $stmt->close();
    }

    redirect('index.php');
}

$pendingResult = $db->query("SELECT * FROM applications WHERE status = 'pending' ORDER BY created_at DESC");
$approvedResult = $db->query("SELECT * FROM applications WHERE status IN ('approved', 'rejected') ORDER BY updated_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .topbar { background: #0f172a; color: #fff; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; }
        .title { font-size: 1.6rem; font-weight: 700; }
        .topbar a { color: #fff; text-decoration: none; font-weight: 700; }
        .container { max-width: 1180px; margin: 0 auto; padding: 30px 20px 60px; }
        h2 { margin: 0 0 18px; }
        .grid { display: grid; gap: 22px; }
        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
            padding: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .pending { background: #fef3c7; color: #92400e; }
        .approved { background: #dcfce7; color: #166534; }
        .rejected { background: #fee2e2; color: #991b1b; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn {
            padding: 8px 12px;
            border-radius: 8px;
            border: 0;
            font-weight: 700;
            cursor: pointer;
        }
        .approve { background: #15803d; color: white; }
        .reject { background: #dc2626; color: white; }
        .meta { color: #475569; font-size: 0.96rem; }
        @media (max-width: 760px) { table { display:block; overflow-x:auto; } }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="title">Digital Identity Admin</div>
        <a href="logout.php">Logout</a>
    </header>

    <main class="container">
        <div class="grid">
            <section class="panel">
                <h2>Pending Applications</h2>
                <?php if ($pendingResult->num_rows === 0): ?>
                    <p class="meta">No pending applications at the moment.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Contact</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pendingResult->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?><br>
                                        <span class="meta"><?= htmlspecialchars($row['national_id']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['department']) ?><br><span class="meta"><?= htmlspecialchars($row['job_title']) ?></span></td>
                                    <td><?= htmlspecialchars($row['email']) ?><br><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                                    <td>
                                        <form method="post" action="process.php">
                                            <input type="hidden" name="application_id" value="<?= (int) $row['id'] ?>">
                                            <div class="actions">
                                                <button class="btn approve" type="submit" name="action" value="approved">Approve</button>
                                                <button class="btn reject" type="submit" name="action" value="rejected">Reject</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Approval History</h2>
                <?php if ($approvedResult->num_rows === 0): ?>
                    <p class="meta">No decisions recorded yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $approvedResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><span class="badge <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars(getApplicationStatusText($row['status'])) ?></span></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td><?= htmlspecialchars($row['updated_at']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
