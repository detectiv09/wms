<?php
require 'config.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['sidebar_collapsed'] = !$_SESSION['sidebar_collapsed'];
    echo json_encode(['success' => true, 'collapsed' => $_SESSION['sidebar_collapsed']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>