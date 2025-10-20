<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Check if a user is logged in
 * Redirects to login page if not authenticated
 */
function check_login() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        header("Location: ../public/login.php");
        exit();
    }
}

/**
 * Login function - verifies credentials and starts session
 */
function login($username, $password) {
    global $connect;

    $stmt = $connect->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Redirect by role
        if ($user['role'] === 'admin') {
            header("Location: ../public/admin_dashboard.php");
        } elseif ($user['role'] === 'parent') {
            header("Location: ../public/parent_dashboard.php");
        } else {
            header("Location: ../public/student_dashboard.php");
        }
        exit();
    } else {
        return "Invalid username or password!";
    }
}

/**
 * Logout function - destroys the session
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: ../public/login.php");
    exit();
}
