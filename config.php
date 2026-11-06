<?php
session_start();

function getDbConnection()
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $setup = new mysqli('localhost', 'root', '');
    if ($setup->connect_error) {
        throw new RuntimeException('Unable to connect to MySQL server.');
    }

    $setup->query("CREATE DATABASE IF NOT EXISTS digital_identity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $setup->close();

    $connection = new mysqli('localhost', 'root', '', 'digital_identity');
    if ($connection->connect_error) {
        throw new RuntimeException('Unable to select the digital_identity database.');
    }

    $connection->set_charset('utf8mb4');

    $schema = "
        CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            phone VARCHAR(30) NOT NULL,
            national_id VARCHAR(50) NOT NULL,
            address TEXT NOT NULL,
            department VARCHAR(100) NOT NULL,
            job_title VARCHAR(100) NOT NULL,
            about TEXT,
            photo VARCHAR(255) DEFAULT NULL,
            status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(120) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if (!$connection->multi_query($schema)) {
        throw new RuntimeException('Unable to create database tables: ' . $connection->error);
    }

    do {
        $connection->store_result();
    } while ($connection->next_result());

    $adminHash = '$2y$10$vv4H9WcbmqQxt2PM5q9Hd.UBJE9XwXsR2/CdCKuMqqrwR/KTHvIKC';
    $defaultUsername = 'admin';
    $defaultFullName = 'System Administrator';
    $adminStmt = $connection->prepare("INSERT IGNORE INTO admin_users (username, password_hash, full_name) VALUES (?, ?, ?)");
    $adminStmt->bind_param('sss', $defaultUsername, $adminHash, $defaultFullName);
    $adminStmt->execute();
    $adminStmt->close();

    return $connection;
}

$db = getDbConnection();

function redirect(string $path): void
{
    if (preg_match('#^(https?:)?//#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    $path = ltrim($path, '/');
    header('Location: ' . $path);
    exit;
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_username']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        redirect('login.php');
    }
}

function getApplicationStatusText(string $status): string
{
    return match ($status) {
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default => 'Pending',
    };
}

function getFullName(array $person): string
{
    return trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
}

function getPhotoUrl(?string $photo): string
{
    if (!$photo) {
        return 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/person-circle.svg';
    }

    if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
        return $photo;
    }

    return ltrim($photo, '/');
}

function uploadPhoto(array $file): ?string
{
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes, true)) {
        return null;
    }

    $targetDir = __DIR__ . '/uploads';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return null;
    }

    return 'uploads/' . $filename;
}
