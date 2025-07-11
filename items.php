<?php
require 'config.php';
checkAuth();
if (!(hasPermission('items_add') || hasPermission('items_edit') || hasPermission('items_delete'))) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create' && hasPermission('items_add')) {
        $article = $_POST['article'];
        $barcode = $_POST['barcode'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $conn->query("INSERT INTO items (article, barcode, description, quantity) VALUES ('$article', '$barcode', '$description', $quantity)");
    } elseif ($_POST['action'] == 'update' && hasPermission('items_edit')) {
        $id = $_POST['id'];
        $article = $_POST['article'];
        $barcode = $_POST['barcode'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $conn->query("UPDATE items SET article='$article', barcode='$barcode', description='$description', quantity=$quantity WHERE id=$id");
    } elseif ($_POST['action'] == 'delete' && hasPermission('items_delete')) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM items WHERE id=$id");
    }
}
$items = $conn->query("SELECT * FROM items");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Items / Inventory</h1>
        <?php if (hasPermission('items_add')): ?>
            <button class="btn btn-icon" data-tooltip="Add Item" onclick="openModal('create')"><i class="fas fa-plus"></i></button>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Barcode</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['article']; ?></td>
                        <td><canvas class="barcode" data-barcode="<?php echo htmlspecialchars($row['barcode']); ?>"></canvas></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td class="actions">
                            <?php if (hasPermission('items_edit')): ?>
                                <button class="btn btn-icon" data-tooltip="Edit" onclick='openModal("update", <?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                            <?php endif; ?>
                            <?php if (hasPermission('items_delete')): ?>
                                <button class="btn btn-icon btn-danger" data-tooltip="Delete" onclick='deleteItem(<?php echo $row['id']; ?>)'><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="itemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="modalTitle">Add Item</h2>
        <form id="itemForm" method="POST">
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="id" id="itemId">
            <div class="form-group">
                <label for="article"><i class="fas fa-box"></i> Article</label>
                <input type="text" id="article" name="article" required>
            </div>
            <div class="form-group">
                <label for="barcode"><i class="fas fa-barcode"></i> Barcode</label>
                <input type="text" id="barcode" name="barcode" required>
            </div>
            <div class="form-group">
                <label for="description"><i class="fas fa-info-circle"></i> Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="quantity"><i class="fas fa-cubes"></i> Quantity</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>