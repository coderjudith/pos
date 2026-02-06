<?php
require_once('../includes/tcpdf/tcpdf.php');
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('admin');


// Get parameters
$type = $_GET['type'] ?? 'csv';
$report = $_GET['report'] ?? 'daily';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Validate dates
if ($start_date > $end_date) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

// Generate filename
$filename = $report . '_report_' . $start_date . '_to_' . $end_date;

if ($report === 'daily') {
    // Get daily sales data
    $stmt = $pdo->prepare("
        SELECT DATE(sale_date) as sale_day, 
               COUNT(*) as transactions,
               SUM(total_amount) as total_sales,
               SUM(cash) as total_cash,
               SUM(change_amount) as total_change
        FROM sales 
        WHERE DATE(sale_date) BETWEEN ? AND ?
        GROUP BY DATE(sale_date)
        ORDER BY sale_day
    ");
    $stmt->execute([$start_date, $end_date]);
    $data = $stmt->fetchAll();
    
    $headers = ['Date', 'Transactions', 'Total Sales', 'Cash Received', 'Change Given'];
    
} elseif ($report === 'products') {
    // Get best selling products
    $stmt = $pdo->prepare("
        SELECT p.name, 
               p.barcode,
               SUM(si.qty) as total_qty,
               SUM(si.subtotal) as total_amount
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        JOIN sales s ON si.sale_id = s.id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        GROUP BY p.id
        ORDER BY total_qty DESC
        LIMIT 50
    ");
    $stmt->execute([$start_date, $end_date]);
    $data = $stmt->fetchAll();
    
    $headers = ['Product Name', 'Barcode', 'Quantity Sold', 'Total Amount'];
}

// CSV Export
if ($type === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write headers
    fputcsv($output, $headers);
    
    // Write data
    foreach ($data as $row) {
        if ($report === 'daily') {
            fputcsv($output, [
                date('Y-m-d', strtotime($row['sale_day'])),
                $row['transactions'],
                CURRENCY_SYMBOL . number_format($row['total_sales'], 2),
                CURRENCY_SYMBOL . number_format($row['total_cash'], 2),
                CURRENCY_SYMBOL . number_format($row['total_change'], 2)
            ]);
        } elseif ($report === 'products') {
            fputcsv($output, [
                $row['name'],
                $row['barcode'],
                $row['total_qty'],
                CURRENCY_SYMBOL . number_format($row['total_amount'], 2)
            ]);
        }
    }
    
    fclose($output);
    exit;
    
} elseif ($type === 'pdf') {
    // For PDF, we need to use TCPDF with proper font for Peso symbol
    require_once('../includes/tcpdf/tcpdf.php');
    
    // Create new PDF document
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('POS System');
    $pdf->SetAuthor('POS System');
    $pdf->SetTitle($report . ' Report');
    $pdf->SetSubject('Sales Report');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // **IMPORTANT: Use a font that supports the Peso symbol**
    // Try different fonts to see which works best
    $font = 'dejavusans';  // Supports many Unicode characters
    // Alternatives: 'freesans', 'freeserif', 'cid0jp' (for Asian characters)
    
    $pdf->SetFont($font, '', 10);
    
    // Add title
    $pdf->SetFont($font, 'B', 16);
    $pdf->Cell(0, 10, ucfirst($report) . ' Sales Report', 0, 1, 'C');
    $pdf->SetFont($font, '', 10);
    $pdf->Cell(0, 5, 'Period: ' . date('F d, Y', strtotime($start_date)) . ' to ' . date('F d, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generated on: ' . date('F d, Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // **Option 1: Use "PHP" instead of ₱ if font doesn't support it**
    $currency_symbol = CURRENCY_SYMBOL;
    
    // **Option 2: Check if font supports the symbol, fallback to "PHP"**
    // Some fonts may not have the Peso symbol
    // You can use: $currency_symbol = 'PHP ';
    
    // Create HTML content for the table
    $html = '<table border="1" cellpadding="5" style="font-family:'.$font.';">';
    
    if ($report === 'daily') {
        // Add headers
        $html .= '<tr style="background-color:#f2f2f2; font-weight:bold;">';
        $html .= '<th width="60">Date</th>';
        $html .= '<th width="50">Transactions</th>';
        $html .= '<th width="60">Total Sales</th>';
        $html .= '<th width="60">Cash Received</th>';
        $html .= '<th width="60">Change Given</th>';
        $html .= '</tr>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td>' . date('M d, Y', strtotime($row['sale_day'])) . '</td>';
            $html .= '<td>' . number_format($row['transactions']) . '</td>';
            $html .= '<td>' . $currency_symbol . number_format($row['total_sales'], 2) . '</td>';
            $html .= '<td>' . $currency_symbol . number_format($row['total_cash'], 2) . '</td>';
            $html .= '<td>' . $currency_symbol . number_format($row['total_change'], 2) . '</td>';
            $html .= '</tr>';
        }
        
    } elseif ($report === 'products') {
        // Add headers
        $html .= '<tr style="background-color:#f2f2f2; font-weight:bold;">';
        $html .= '<th width="30">#</th>';
        $html .= '<th width="100">Product Name</th>';
        $html .= '<th width="60">Barcode</th>';
        $html .= '<th width="50">Quantity</th>';
        $html .= '<th width="60">Total Amount</th>';
        $html .= '</tr>';
        
        $counter = 1;
        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $counter++ . '</td>';
            $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
            $html .= '<td>' . $row['barcode'] . '</td>';
            $html .= '<td>' . number_format($row['total_qty']) . '</td>';
            $html .= '<td>' . $currency_symbol . number_format($row['total_amount'], 2) . '</td>';
            $html .= '</tr>';
        }
    }
    
    $html .= '</table>';
    
    // Write HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Output PDF
    $pdf->Output($filename . '.pdf', 'D');
    exit;
}
?>