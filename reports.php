<?php
require 'config.php';
checkAuth();
require 'vendor/tcpdf/tcpdf.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_type'])) {
    $report_type = $_POST['report_type'];
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    if ($report_type == 'inventory') {
        $pdf->Cell(0, 10, 'Inventory Report', 0, 1, 'C');
        $items = $conn->query("SELECT * FROM items");
        $html = '<table border="1"><tr><th>ID</th><th>Article</th><th>Barcode</th><th>Quantity</th></tr>';
        while ($row = $items->fetch_assoc()) {
            $html .= "<tr><td>{$row['id']}</td><td>{$row['article']}</td><td>{$row['barcode']}</td><td>{$row['quantity']}</td></tr>";
        }
        $html .= '</table>';
        $pdf->writeHTML($html);
    } elseif ($report_type == 'inbound') {
        $pdf->Cell(0, 10, 'Inbound Report', 0, 1, 'C');
        $inbound = $conn->query("SELECT i.*, t.article FROM inbound i JOIN items t ON i.item_id = t.id");
        $html = '<table border="1"><tr><th>ID</th><th>Item</th><th>Quantity</th><th>Received Date</th><th>Supplier</th></tr>';
        while ($row = $inbound->fetch_assoc()) {
            $html .= "<tr><td>{$row['id']}</td><td>{$row['article']}</td><td>{$row['quantity']}</td><td>{$row['received_date']}</td><td>{$row['supplier']}</td></tr>";
        }
        $html .= '</table>';
        $pdf->writeHTML($html);
    } elseif ($report_type == 'outbound') {
        $pdf->Cell(0, 10, 'Outbound Report', 0, 1, 'C');
        $outbound = $conn->query("SELECT o.*, t.article FROM outbound o JOIN items t ON o.item_id = t.id");
        $html = '<table border="1"><tr><th>ID</th><th>Item</th><th>Quantity</th><th>Shipped Date</th><th>Destination</th></tr>';
        while ($row = $outbound->fetch_assoc()) {
            $html .= "<tr><td>{$row['id']}</td><td>{$row['article']}</td><td>{$row['quantity']}</td><td>{$row['shipped_date']}</td><td>{$row['destination']}</td></tr>";
        }
        $html .= '</table>';
        $pdf->writeHTML($html);
    }
    $pdf->Output('report.pdf', 'D');
}
?>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/nav.php'; ?>
    <div class="main-content">
        <h1>Reports</h1>
        <form method="POST">
            <div class="form-group">
                <label for="report_type"><i class="fas fa-file-alt"></i> Select Report</label>
                <select id="report_type" name="report_type" required>
                    <option value="inventory">Inventory Report</option>
                    <option value="inbound">Inbound Report</option>
                    <option value="outbound">Outbound Report</option>
                </select>
            </div>
            <button type="submit" class="btn"><i class="fas fa-download"></i> Generate PDF</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>