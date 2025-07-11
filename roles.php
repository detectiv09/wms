<?php
require 'config.php';
checkAuth();
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
$permissions_list = [
    'users' => ['users_add' => 'Users Add', 'users_edit' => 'Users Edit', 'users_delete' => 'Users Delete'],
    'roles' => ['roles_add' => 'Roles Add', 'roles_edit' => 'Roles Edit', 'roles_delete' => 'Roles Delete'],
    'items' => ['items_add' => 'Items Add', 'items_edit' => 'Items Edit', 'items_delete' => 'Items Delete'],
    'inbound' => ['inbound_add' => 'Inbound Add', 'inbound_edit' => 'Inbound Edit', 'inbound_delete' => 'Inbound Delete'],
    'outbound' => ['outbound_add' => 'Outbound Add', 'outbound_edit' => 'Outbound Edit', 'outbound_delete' => 'Outbound Delete'],
    'reports' => ['reports_add' => 'Reports Add', 'reports_edit' => 'Reports Edit', 'reports_delete' => 'Reports Delete'],
    'low_stocks' => ['low_stocks_view' => 'Low Stocks View']
];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $role_name = $_POST['role_name'];
        $permissions = json_encode($_POST['permissions'] ?? []);
        $conn->query("INSERT INTO roles (role_name, permissions) VALUES ('$role_name', '$permissions')");
    } elseif ($_POST['action'] == 'update') {
        $id = $_POST['id'];
        $role_name = $_POST['role_name'];
        $permissions = json_encode($_POST['permissions'] ?? []);
        $conn->query("UPDATE roles SET role_name='$role_name', permissions='$permissions' WHERE id=$id");
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM roles WHERE id=$id");
    }
}
$roles = $conn->query("SELECT * FROM roles");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Roles Management</h1>
        <button class="btn btn-icon" data-tooltip="Add Role" onclick="openRoleModal('create')"><i class="fas fa-plus"></i></button>
        <table>
            <thead>
                <tr>

                    <th>Role Name</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $roles->fetch_assoc()): ?>
                    <tr>

                        <td><?php echo $row['role_name']; ?></td>
                        <td><?php
                            $perms = json_decode($row['permissions'], true);
                            echo implode(', ', is_array($perms) ? $perms : []);
                        ?></td>
                        <td class="actions">
                            <button class="btn btn-icon" data-tooltip="Edit" onclick='openRoleModal("update", <?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                            <button class="btn btn-icon btn-danger" data-tooltip="Delete" onclick='deleteRole(<?php echo $row['id']; ?>)'><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="roleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="roleModalTitle">Add Role</h2>
        <form id="roleForm" method="POST">
            <input type="hidden" name="action" id="roleAction">
            <input type="hidden" name="id" id="roleId">
            <div class="form-group">
                <label for="role_name"><i class="fas fa-user-shield"></i> Role Name</label>
                <input type="text" id="role_name" name="role_name" required>
            </div>
            <div class="form-group">
                <label>Permissions</label>
                <?php foreach ($permissions_list as $module => $perms): ?>
                    <div class="permission-group">
                        <h4><?php echo ucfirst($module); ?></h4>
                        <div class="select-all">
                            <input type="checkbox" id="select_all_<?php echo $module; ?>" onchange="toggleSelectAll('<?php echo $module; ?>')">
                            <label for="select_all_<?php echo $module; ?>">Select All</label>
                        </div>
                        <?php foreach ($perms as $perm_key => $perm_label): ?>
                            <div>
                                <input type="checkbox" name="permissions[]" value="<?php echo $perm_key; ?>" id="perm_<?php echo $perm_key; ?>" class="perm-checkbox" data-group="<?php echo $module; ?>">
                                <label for="perm_<?php echo $perm_key; ?>"><?php echo $perm_label; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>