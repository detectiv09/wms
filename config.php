<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'wms_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function hasPermission($permission) {
    global $conn;
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT r.permissions FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = $user_id");
    if (!$result || $result->num_rows == 0) return false;
    $row = $result->fetch_assoc();
    $permissions = json_decode($row['permissions'], true);
    return is_array($permissions) && in_array($permission, $permissions);
}

function isAdmin() {
    return hasPermission('users_add') || hasPermission('users_edit') || hasPermission('users_delete');
}

// Initialize sidebar state if not set
if (!isset($_SESSION['sidebar_collapsed'])) {
    $_SESSION['sidebar_collapsed'] = false;
}
?>