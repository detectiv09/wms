<?php
require 'config.php';
checkAuth();
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create' && hasPermission('users_add')) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role_id = $_POST['role_id'];
        $conn->query("INSERT INTO users (username, password, role_id) VALUES ('$username', '$password', $role_id)");
    } elseif ($_POST['action'] == 'update' && hasPermission('users_edit')) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role_id = $_POST['role_id'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
        $query = "UPDATE users SET username='$username', role_id=$role_id";
        if ($password) $query .= ", password='$password'";
        $query .= " WHERE id=$id";
        $conn->query($query);
    } elseif ($_POST['action'] == 'delete' && hasPermission('users_delete')) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM users WHERE id=$id");
    }
}
$users = $conn->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id");
$roles = $conn->query("SELECT * FROM roles");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Users Management</h1>
        <?php if (hasPermission('users_add')): ?>
            <button class="btn btn-icon" data-tooltip="Add User" onclick="openUserModal('create')"><i class="fas fa-plus"></i></button>
        <?php endif; ?>
        <table>
            <thead>
                <tr>

                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>

                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['role_name']; ?></td>
                        <td class="actions">
                            <?php if (hasPermission('users_edit')): ?>
                                <button class="btn btn-icon" data-tooltip="Edit" onclick='openUserModal("update", <?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                            <?php endif; ?>
                            <?php if (hasPermission('users_delete')): ?>
                                <button class="btn btn-icon btn-danger" data-tooltip="Delete" onclick='deleteUser(<?php echo $row['id']; ?>)'><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="userModalTitle">Add User</h2>
        <form id="userForm" method="POST">
            <input type="hidden" name="action" id="userAction">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="role_id"><i class="fas fa-user-shield"></i> Role</label>
                <select id="role_id" name="role_id" required>
                    <?php while ($role = $roles->fetch_assoc()): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['role_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>