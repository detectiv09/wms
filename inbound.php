<?php
require 'config.php';
checkAuth();
if (!(hasPermission('inbound_add') || hasPermission('inbound_edit') || hasPermission('inbound_delete'))) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create' && hasPermission('inbound_add')) {
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $received_date = $_POST['received_date'];
        $supplier = $_POST['supplier'] ?? '';
        $stmt = $conn->prepare("INSERT INTO inbound (item_id, quantity, received_date, supplier) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $quantity, $received_date, $supplier);
        $conn->begin_transaction();
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        $conn->commit();
    } elseif ($_POST['action'] == 'update' && hasPermission('inbound_edit')) {
        $id = $_POST['id'];
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $received_date = $_POST['received_date'];
        $supplier = $_POST['supplier'] ?? '';
        $stmt = $conn->prepare("SELECT quantity FROM inbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old_quantity = $stmt->get_result()->fetch_row()[0];
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE inbound SET item_id = ?, quantity = ?, received_date = ?, supplier = ? WHERE id = ?");
        $stmt->bind_param("iissi", $item_id, $quantity, $received_date, $supplier, $id);
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? + ? WHERE id = ?");
        $stmt->bind_param("iii", $old_quantity, $quantity, $item_id);
        $stmt->execute();
        $conn->commit();
    } elseif ($_POST['action'] == 'delete' && hasPermission('inbound_delete')) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("SELECT item_id, quantity FROM inbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $item_id = $stmt->get_result()->fetch_assoc();
        $conn->begin_transaction();
        $stmt = $conn->prepare("DELETE FROM inbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $item_id['quantity'], $item_id['item_id']);
        $stmt->execute();
        $conn->commit();
    }
}
$inbound = $conn->query("SELECT i.*, t.article FROM inbound i JOIN items t ON i.item_id = t.id");
$items = $conn->query("SELECT id, article FROM items");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Inbound Items</h1>
        <?php if (hasPermission('inbound_add')): ?>
            <button class="btn btn-icon" data-tooltip="Add Inbound" onclick="openInboundModal('create')"><i class="fas fa-plus"></i></button>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Received Date</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($inbound->num_rows == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No inbound transactions found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $inbound->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['article']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['received_date']; ?></td>
                            <td><?php echo $row['supplier']; ?></td>
                            <td class="actions">
                                <?php if (hasPermission('inbound_edit')): ?>
                                    <button class="btn btn-icon" data-tooltip="Edit" onclick='openInboundModal("update", <?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                <?php endif; ?>
                                <?php if (hasPermission('inbound_delete')): ?>
                                    <button class="btn btn-icon btn-danger" data-tooltip="Delete" onclick='deleteInbound(<?php echo $row['id']; ?>)'><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="inboundModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="inboundModalTitle">Add Inbound</h2>
        <form id="inboundForm" method="POST">
            <input type="hidden" name="action" id="inboundAction">
            <input type="hidden" name="id" id="inboundId">
            <div class="form-group">
                <label for="item_id"><i class="fas fa-box"></i> Item</label>
                <select id="item_id" name="item_id" required>
                    <?php
                    $items->data_seek(0); // Reset items cursor
                    while ($item = $items->fetch_assoc()): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo $item['article']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity"><i class="fas fa-cubes"></i> Quantity</label>
                <input type="number" id="inboundQuantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="received_date"><i class="fas fa-calendar"></i> Received Date</label>
                <input type="date" id="received_date" name="received_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="supplier"><i class="fas fa-truck"></i> Supplier</label>
                <input type="text" id="supplier" name="supplier">
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>