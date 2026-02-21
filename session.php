<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['full_name'] ?? 'Guest';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /PamudiNew/auth/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        header('Location: /PamudiNew/auth/login.php?error=unauthorized');
        exit;
    }
}

function redirectToDashboard() {
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            header('Location: /PamudiNew/admin/dashboard.php');
            break;
        case 'doctor':
            header('Location: /PamudiNew/doctor/dashboard.php');
            break;
        case 'patient':
            header('Location: /PamudiNew/patient/dashboard.php');
            break;
        default:
            header('Location: /PamudiNew/auth/login.php');
    }
    exit;
}
?>
