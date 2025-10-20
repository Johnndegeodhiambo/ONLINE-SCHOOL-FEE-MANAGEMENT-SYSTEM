<?php
require_once '../includes/auth.php';
check_login('admin');
require_once '../includes/db.php';

// Include TCPDF library
require_once '../tcpdf/tcpdf.php';

try {
    // Fetch data (same queries as your reports.php)
    $students_total = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $fees_total = $pdo->query("SELECT SUM(amount_paid) FROM fees")->fetchColumn() ?: 0;
    $payments_total = $pdo->query("SELECT SUM(amount_paid) FROM payments WHERE status='paid'")->fetchColumn() ?: 0;
    $pending_fees = $fees_total - $payments_total;

    $statusData = $pdo->query("
        SELECT status, COUNT(*) AS count 
        FROM fees 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $paidCount = $statusData['paid'] ?? 0;
    $pendingCount = $statusData['pending'] ?? 0;
    $overdueCount = $statusData['overdue'] ?? 0;

    $stmt = $pdo->query("
        SELECT 
            p.id, 
            s.full_name AS student_name, 
            p.amount_paid, 
            p.payment_date, 
            p.method, 
            p.status
        FROM payments p
        LEFT JOIN students s ON p.student_id = s.id
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");
    $recent_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Admin Dashboard');
$pdf->SetTitle('Reports Summary');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reports & Analytics Summary', 0, 1, 'C');
$pdf->Ln(5);

// Summary Cards
$pdf->SetFont('helvetica', '', 12);
$html = '
<table cellpadding="5" border="1" cellspacing="0" style="width: 100%;">
<tr>
    <td><b>Total Students</b><br><h2>' . $students_total . '</h2></td>
    <td><b>Total Fees</b><br><h2>' . number_format($fees_total, 2) . '</h2></td>
    <td><b>Total Payments</b><br><h2>' . number_format($payments_total, 2) . '</h2></td>
    <td><b>Pending Fees</b><br><h2>' . number_format($pending_fees, 2) . '</h2></td>
</tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(8);

// Fee Status Breakdown
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Fee Status Overview', 0, 1);

$pdf->SetFont('helvetica', '', 12);
$html = '
<table cellpadding="5" border="1" cellspacing="0" style="width: 60%;">
<tr>
    <th>Status</th>
    <th>Count</th>
</tr>
<tr><td>Paid</td><td>' . $paidCount . '</td></tr>
<tr><td>Pending</td><td>' . $pendingCount . '</td></tr>
<tr><td>Overdue</td><td>' . $overdueCount . '</td></tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(8);

// Recent Payments Table
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Recent Payments', 0, 1);

$pdf->SetFont('helvetica', '', 10);
$html = '<table cellpadding="5" border="1" cellspacing="0" style="width: 100%;">
<tr style="background-color:#6a5acd; color:#fff;">
    <th>ID</th>
    <th>Student</th>
    <th>Amount Paid (KES)</th>
    <th>Date</th>
    <th>Method</th>
    <th>Status</th>
</tr>';

if ($recent_payments) {
    foreach ($recent_payments as $p) {
        $statusColor = match($p['status']) {
            'paid' => '#2ecc71',
            'pending' => '#f1c40f',
            'overdue' => '#e74c3c',
            default => '#999',
        };
        $html .= '<tr>
            <td>' . htmlspecialchars($p['id']) . '</td>
            <td>' . htmlspecialchars($p['student_name']) . '</td>
            <td style="text-align:right;">' . number_format($p['amount_paid'], 2) . '</td>
            <td>' . htmlspecialchars($p['payment_date']) . '</td>
            <td>' . ucfirst(htmlspecialchars($p['method'])) . '</td>
            <td style="color:' . $statusColor . '; font-weight:bold;">' . ucfirst($p['status']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align:center;">No payments found.</td></tr>';
}

$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF to browser (force download)
$pdf->Output('reports_summary.pdf', 'D');
