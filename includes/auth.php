<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
}

// Redirect based on role
function redirect_based_on_role() {
    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: dashboard.php');
        } else {
            header('Location: cashier/pos.php');
        }
        exit();
    }
}

// Check if user has required role
function require_role($required_role) {
    require_login();
    if ($_SESSION['user_role'] !== $required_role) {
        header('Location: ../dashboard.php');
        exit();
    }
}

// Logout function
function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>