<?php
require 'config.php';
checkAuth();
if (!hasPermission('low_stocks_view')) {
    header('Location: index.php');
    exit;
}
$low_stock_threshold = 10; // Configurable threshold
$low_stocks = $conn->query("SELECT * FROM items WHERE quantity < $low_stock_threshold");
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Low Stocks</h1>
        <p>Items with quantity less than <?php echo $low_stock_threshold; ?> units</p>
        <table>
            <thead>
                <tr>

                    <th>Article</th>
                    <th>Barcode</th>
                    <th>Description</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($low_stocks->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No low stock items found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $low_stocks->fetch_assoc()): ?>
                        <tr>

                            <td><?php echo $row['article']; ?></td>
                            <td><canvas class="barcode" data-barcode="<?php echo htmlspecialchars($row['barcode']); ?>"></canvas></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>