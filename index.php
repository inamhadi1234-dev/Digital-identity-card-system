<?php
require __DIR__ . '/config.php';

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!$message && isset($_GET['registered']) && $_GET['registered'] === '1') {
    $message = 'Registration submitted successfully. Please wait for admin approval.';
}

$approvedQuery = $db->prepare("SELECT * FROM applications WHERE status = 'approved' ORDER BY created_at DESC");
$approvedQuery->execute();
$approvedResult = $approvedQuery->get_result();
$approvedProfiles = $approvedResult->fetch_all(MYSQLI_ASSOC);
$approvedQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Identity Card System</title>
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1d4ed8;
            --accent: #f59e0b;
            --bg: #f8fafc;
            --card: #ffffff;
            --muted: #475569;
            --success: #15803d;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            color: var(--primary);
        }
        .topbar {
            background: var(--primary);
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand { font-size: 1.6rem; font-weight: 700; }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
            font-weight: 600;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }
        .hero {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 28px;
            align-items: center;
            margin-bottom: 40px;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 4rem);
            line-height: 1.1;
            margin: 0 0 16px;
        }
        .hero p {
            color: var(--muted);
            line-height: 1.7;
            font-size: 1.05rem;
        }
        .cta {
            margin-top: 20px;
            display: inline-flex;
            gap: 12px;
            align-items: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            border: 0;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--primary-light);
            color: white;
        }
        .btn-secondary {
            background: var(--accent);
            color: var(--primary);
        }
        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .hero-form {
            padding: 24px;
        }
        .alert {
            background: #dcfce7;
            color: var(--success);
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        form .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        label {
            font-size: 0.92rem;
            font-weight: 700;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 1rem;
            background: white;
        }
        input[type='file'] {
            padding: 10px;
            background: #f8fafc;
        }
        textarea { min-height: 110px; resize: vertical; }
        .full-width { grid-column: 1 / -1; }
        .submit-row {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
        }
        .identity-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
        }
        .identity-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
        }
        .identity-top img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            background: #f1f5f9;
        }
        .identity-top h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .identity-top p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }
        .mini-list {
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.8;
            margin: 10px 0 16px;
        }
        .qr-box {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 16px;
        }
        .qr-box img {
            width: 140px;
            height: 140px;
        }
        .card-link {
            display: inline-block;
            text-decoration: none;
            color: var(--primary-light);
            font-weight: 700;
        }
        .section-head {
            margin: 40px 0 20px;
            font-size: 2rem;
        }
        @media (max-width: 768px) {
            .hero { grid-template-columns: 1fr; }
            form .grid { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; gap: 10px; padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">Digital Identity</div>
        <nav>
            <a href="#register">Register</a>
            <a href="#approved">Approved Cards</a>
            <a href="admin/login.php">Admin</a>
        </nav>
    </header>

    <main class="container">
        <section class="hero">
            <div>
                <h1>Secure digital identity cards for every member.</h1>
                <p>
                    Create a verified identity profile, submit it for review, and receive an approved digital card with a QR code,
                    profile photo, and contact details that can be shared securely across your organization.
                </p>
                <div class="cta">
                    <a class="btn btn-primary" href="#register">Join Now</a>
                    <a class="btn btn-secondary" href="#approved">View Cards</a>
                </div>
            </div>

            <div class="panel hero-form" id="register">
                <?php if ($message): ?>
                    <div class="alert"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form action="register.php" method="post" enctype="multipart/form-data">
                    <div class="grid">
                        <div class="field">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" type="text" required>
                        </div>
                        <div class="field">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" type="text" required>
                        </div>

                        <div class="field">
                            <label for="email">Email Address</label>
                            <input id="email" name="email" type="email" required>
                        </div>
                        <div class="field">
                            <label for="phone">Phone Number</label>
                            <input id="phone" name="phone" type="tel" required>
                        </div>

                        <div class="field full-width">
                            <label for="address">Home Address</label>
                            <textarea id="address" name="address" required></textarea>
                        </div>

                        <div class="field">
                            <label for="department">Department</label>
                            <input id="department" name="department" type="text" required>
                        </div>
                        <div class="field">
                            <label for="job_title">Job Title</label>
                            <input id="job_title" name="job_title" type="text" required>
                        </div>

                        <div class="field">
                            <label for="national_id">National ID / Employee ID</label>
                            <input id="national_id" name="national_id" type="text" required>
                        </div>
                        <div class="field">
                            <label for="photo">Profile Photo</label>
                            <input id="photo" name="photo" type="file" accept="image/*">
                        </div>

                        <div class="field full-width">
                            <label for="about">Additional Information</label>
                            <textarea id="about" name="about"></textarea>
                        </div>
                    </div>

                    <div class="submit-row">
                        <button type="submit" class="btn btn-primary">Submit Registration</button>
                    </div>
                </form>
            </div>
        </section>

        <section id="approved">
            <h2 class="section-head">Approved Digital Cards</h2>
            <div class="cards">
                <?php if (empty($approvedProfiles)): ?>
                    <div class="panel identity-card">
                        <p>No approved cards yet. The first approved member will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($approvedProfiles as $profile): ?>
                        <?php $personName = getFullName($profile); ?>
                        <article class="identity-card">
                            <div class="identity-top">
                                <img src="<?= htmlspecialchars(getPhotoUrl($profile['photo'])) ?>" alt="<?= htmlspecialchars($personName) ?>">
                                <div>
                                    <h3><?= htmlspecialchars($personName) ?></h3>
                                    <p><?= htmlspecialchars($profile['job_title']) ?> • <?= htmlspecialchars($profile['department']) ?></p>
                                </div>
                            </div>

                            <div class="mini-list">
                                <div>Email: <?= htmlspecialchars($profile['email']) ?></div>
                                <div>Phone: <?= htmlspecialchars($profile['phone']) ?></div>
                                <div>ID: <?= htmlspecialchars($profile['national_id']) ?></div>
                            </div>

                            <div class="qr-box">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&amp;data=<?= urlencode('digital-identity:' . $profile['id'] . ':' . $profile['email']) ?>" alt="QR code">
                            </div>
                            <a class="card-link" href="card.php?id=<?= (int) $profile['id'] ?>">View full digital card</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
