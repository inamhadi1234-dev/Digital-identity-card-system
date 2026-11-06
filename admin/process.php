<?php
require __DIR__ . '/../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'], $_POST['application_id'])) {
    redirect('index.php');
}

$applicationId = (int) $_POST['application_id'];
$action = $_POST['action'];

if (!in_array($action, ['approved', 'rejected'], true)) {
    redirect('index.php');
}

$stmt = $db->prepare('UPDATE applications SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
$stmt->bind_param('si', $action, $applicationId);
$stmt->execute();
$stmt->close();

redirect('index.php');
