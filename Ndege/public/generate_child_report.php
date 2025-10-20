<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';
session_start();

// Only parents
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'parent') {
    die("Access denied");
}

if (!isset($_POST['student_id'])) {
    die("Student not found.");
}

$student_id = $_POST['student_id'];

// Get student details
$stmt = $pdo->prepare("SELECT full_name, class, balance FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Invalid student ID.");
}

// Get payments
$stmt = $pdo->prepare("SELECT amount_paid, method, status, payment_date FROM payments WHERE student_id = ? ORDER BY payment_date DESC");
$stmt->execute([$student_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$html = '<h2 style="color:#6a5acd;">Student Payment Report</h2>';
$html .= '<strong>Name:</strong> ' . htmlspecialchars($student['full_name']) . '<br>';
$html .= '<strong>Class:</strong> ' . htmlspecialchars($student['class']) . '<br>';
$html .= '<strong>Outstanding Balance:</strong> Ksh ' . number_format($student['balance'], 2) . '<br><br>';

$html .= '<h3>Payment History</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="5">
<tr style="background-color:#f2f2f2;">
<th>Date</th>
<th>Amount Paid (Ksh)</th>
<th>Method</th>
<th>Status</th>
</tr>';

if (count($payments) > 0) {
    foreach ($payments as $p) {
        $html .= '<tr>
            <td>' . htmlspecialchars($p['payment_date']) . '</td>
            <td>' . number_format($p['amount_paid'], 2) . '</td>
            <td>' . htmlspecialchars($p['method']) . '</td>
            <td>' . htmlspecialchars($p['status']) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="4" align="center">No payments found</td></tr>';
}

$html .= '</table>';

$pdf->writeHTML($html);
$pdf->Output('Payment_Report_' . $student['full_name'] . '.pdf', 'I');
 