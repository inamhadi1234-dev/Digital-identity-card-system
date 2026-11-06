<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$requiredFields = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'address', 'department', 'job_title'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['flash_message'] = 'Please fill in all required fields.';
        redirect('index.php');
    }
}

$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$nationalId = trim($_POST['national_id']);
$address = trim($_POST['address']);
$department = trim($_POST['department']);
$jobTitle = trim($_POST['job_title']);
$about = trim($_POST['about'] ?? '');
$uploadedPhoto = uploadPhoto($_FILES['photo'] ?? []);

$stmt = $db->prepare("INSERT INTO applications (first_name, last_name, email, phone, national_id, address, department, job_title, about, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param('ssssssssss', $firstName, $lastName, $email, $phone, $nationalId, $address, $department, $jobTitle, $about, $uploadedPhoto);

if (!$stmt->execute()) {
    $stmt->close();
    $_SESSION['flash_message'] = 'This email or ID is already in use.';
    redirect('index.php');
}

$stmt->close();
$_SESSION['flash_message'] = 'Registration submitted successfully. Please wait for admin approval.';
redirect('index.php?registered=1');
