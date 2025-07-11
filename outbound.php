<?php
require 'config.php';
checkAuth();
if (!(hasPermission('outbound_add') || hasPermission('outbound_edit') || hasPermission('outbound_delete'))) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create' && hasPermission('outbound_add')) {
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $shipped_date = $_POST['shipped_date'];
        $destination = $_POST['destination'] ?? '';
        $stmt = $conn->prepare("INSERT INTO outbound (item_id, quantity, shipped_date, destination) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $quantity, $shipped_date, $destination);
        $conn->begin_transaction();
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        $conn->commit();
    } elseif ($_POST['action'] == 'update' && hasPermission('outbound_edit')) {
        $id = $_POST['id'];
        $item_id = $_POST['item_id'];
        $quantity = $_POST['quantity'];
        $shipped_date = $_POST['shipped_date'];
        $destination = $_POST['destination'] ?? '';
        $stmt = $conn->prepare("SELECT quantity FROM outbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old_quantity = $stmt->get_result()->fetch_row()[0];
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE outbound SET item_id = ?, quantity = ?, shipped_date = ?, destination = ? WHERE id = ?");
        $stmt->bind_param("iissi", $item_id, $quantity, $shipped_date, $destination, $id);
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity + ? - ? WHERE id = ?");
        $stmt->bind_param("iii", $old_quantity, $quantity, $item_id);
        $stmt->execute();
        $conn->commit();
    } elseif ($_POST['action'] == 'delete' && hasPermission('outbound_delete')) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("SELECT item_id, quantity FROM outbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $item_id = $stmt->get_result()->fetch_assoc();
        $conn->begin_transaction();
        $stmt = $conn->prepare("DELETE FROM outbound WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
        $stmt->bind_param("ii", $item_id['quantity'], $item_id['item_id']);
        $stmt->execute();
        $conn->commit();
    }
}
$outbound = $conn->query("SELECT o.*, t.article FROM outbound o JOIN items t ON o.item_id = t.id");
$items = $conn->query("SELECT id, article FROM items");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Outbound Items</h1>
        <?php if (hasPermission('outbound_add')): ?>
            <button class="btn btn-icon" data-tooltip="Add Outbound" onclick="openOutboundModal('create')"><i class="fas fa-plus"></i></button>
        <?php endif; ?>
        <table>
            <thead>
                <tr>

                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Shipped Date</th>
                    <th>Destination</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($outbound->num_rows == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No outbound transactions found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $outbound->fetch_assoc()): ?>
                        <tr>

                            <td><?php echo $row['article']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['shipped_date']; ?></td>
                            <td><?php echo $row['destination']; ?></td>
                            <td class="actions">
                                <?php if (hasPermission('outbound_edit')): ?>
                                    <button class="btn btn-icon" data-tooltip="Edit" onclick='openOutboundModal("update", <?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                <?php endif; ?>
                                <?php if (hasPermission('outbound_delete')): ?>
                                    <button class="btn btn-icon btn-danger" data-tooltip="Delete" onclick='deleteOutbound(<?php echo $row['id']; ?>)'><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="outboundModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">×</span>
        <h2 id="outboundModalTitle">Add Outbound</h2>
        <form id="outboundForm" method="POST">
            <input type="hidden" name="action" id="outboundAction">
            <input type="hidden" name="id" id="outboundId">
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
                <input type="number" id="outboundQuantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="shipped_date"><i class="fas fa-calendar"></i> Shipped Date</label>
                <input type="date" id="shipped_date" name="shipped_date" value="<?php echo date('Y-m-dd'); ?>" required>
            </div>
            <div class="form-group">
                <label for="destination"><i class="fas fa-map-marker-alt"></i> Destination</label>
                <input type="text" id="destination" name="destination">
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>