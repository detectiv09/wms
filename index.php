<?php
require 'config.php';
checkAuth();
$inbound_count = $conn->query("SELECT COUNT(*) FROM inbound")->fetch_row()[0];
$outbound_count = $conn->query("SELECT COUNT(*) FROM outbound")->fetch_row()[0];
$items_count = $conn->query("SELECT COUNT(*) FROM items")->fetch_row()[0];
$low_stock = $conn->query("SELECT COUNT(*) FROM items WHERE quantity < 10")->fetch_row()[0];
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Dashboard</h1>
        <div class="dashboard-cards" id="dashboardCards">
            <?php if (hasPermission('inbound_add') || hasPermission('inbound_edit') || hasPermission('inbound_delete')): ?>
                <a href="inbound.php" class="card card-link" data-tooltip="View Inbound">
                    <div class="card-content">
                        <h3><i class="fas fa-arrow-down card-icon"></i> Total Inbound</h3>
                        <p><?php echo $inbound_count; ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="card card-disabled">
                    <div class="card-content">
                        <h3><i class="fas fa-arrow-down card-icon"></i> Total Inbound</h3>
                        <p><?php echo $inbound_count; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (hasPermission('outbound_add') || hasPermission('outbound_edit') || hasPermission('outbound_delete')): ?>
                <a href="outbound.php" class="card card-link" data-tooltip="View Outbound">
                    <div class="card-content">
                        <h3><i class="fas fa-arrow-up card-icon"></i> Total Outbound</h3>
                        <p><?php echo $outbound_count; ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="card card-disabled">
                    <div class="card-content">
                        <h3><i class="fas fa-arrow-up card-icon"></i> Total Outbound</h3>
                        <p><?php echo $outbound_count; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (hasPermission('items_add') || hasPermission('items_edit') || hasPermission('items_delete')): ?>
                <a href="items.php" class="card card-link" data-tooltip="View Items">
                    <div class="card-content">
                        <h3><i class="fas fa-box card-icon"></i> Total Items</h3>
                        <p><?php echo $items_count; ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="card card-disabled">
                    <div class="card-content">
                        <h3><i class="fas fa-box card-icon"></i> Total Items</h3>
                        <p><?php echo $items_count; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (hasPermission('low_stocks_view')): ?>
                <a href="low_stocks.php" class="card card-link" data-tooltip="View Low Stocks">
                    <div class="card-content">
                        <h3><i class="fas fa-exclamation-triangle card-icon"></i> Low Stock Alerts</h3>
                        <p><?php echo $low_stock; ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="card card-disabled">
                    <div class="card-content">
                        <h3><i class="fas fa-exclamation-triangle card-icon"></i> Low Stock Alerts</h3>
                        <p><?php echo $low_stock; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>