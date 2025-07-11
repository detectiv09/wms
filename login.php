<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            header('Location: index.php');
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid credentials";
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="login-container">
    <form method="POST" class="login-form">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>