<?php
require __DIR__ . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('index.php');
}

$stmt = $db->prepare("SELECT * FROM applications WHERE id = ? AND status = 'approved' LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    redirect('index.php');
}

$personName = getFullName($profile);
$qrValue = 'digital-identity:' . $profile['id'] . ':' . $profile['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($personName) ?> | Digital Card</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #dbeafe, #f8fafc);
            color: #0f172a;
        }
        .wrap { max-width: 980px; margin: 60px auto; padding: 20px; }
        .card {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr;
            border-radius: 24px;
            overflow: hidden;
            background: white;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
            border: 1px solid #e2e8f0;
        }
        .left {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: white;
            padding: 32px;
        }
        .top-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 24px;
        }
        .top-row img {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.8);
            object-fit: cover;
            background: rgba(255,255,255,0.2);
        }
        .top-row h1 { margin: 0 0 6px; font-size: 2rem; }
        .top-row p { margin: 0; color: rgba(255,255,255,0.8); }
        .info { display: grid; gap: 14px; }
        .info-item { display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.16); padding-bottom: 10px; }
        .info-item .label { color: rgba(255,255,255,0.75); }
        .right {
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #f8fafc;
        }
        .qr-wrap {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 18px;
            border-radius: 18px;
            margin-bottom: 18px;
        }
        .qr-wrap img { width: 220px; height: 220px; }
        .badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 700;
        }
        a { color: #1d4ed8; font-weight: 700; }
        @media (max-width: 760px) { .card { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="left">
                <div class="top-row">
                    <img src="<?= htmlspecialchars(getPhotoUrl($profile['photo'])) ?>" alt="<?= htmlspecialchars($personName) ?>">
                    <div>
                        <h1><?= htmlspecialchars($personName) ?></h1>
                        <p><?= htmlspecialchars($profile['job_title']) ?></p>
                    </div>
                </div>

                <div class="info">
                    <div class="info-item"><span class="label">Department</span><strong><?= htmlspecialchars($profile['department']) ?></strong></div>
                    <div class="info-item"><span class="label">Email</span><strong><?= htmlspecialchars($profile['email']) ?></strong></div>
                    <div class="info-item"><span class="label">Phone</span><strong><?= htmlspecialchars($profile['phone']) ?></strong></div>
                    <div class="info-item"><span class="label">National ID</span><strong><?= htmlspecialchars($profile['national_id']) ?></strong></div>
                    <div class="info-item"><span class="label">Address</span><strong><?= htmlspecialchars($profile['address']) ?></strong></div>
                    <div class="info-item"><span class="label">About</span><strong><?= htmlspecialchars($profile['about'] ?: 'No additional information provided.') ?></strong></div>
                </div>
            </div>

            <div class="right">
                <div class="badge">Verified Member</div>
                <div class="qr-wrap">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&amp;data=<?= urlencode($qrValue) ?>" alt="QR code for <?= htmlspecialchars($personName) ?>">
                </div>
                <a href="index.php">Back to home</a>
            </div>
        </div>
    </div>
</body>
</html>
