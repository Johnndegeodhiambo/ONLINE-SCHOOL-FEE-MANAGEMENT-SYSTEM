<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . 'C:\xampp\htdocs\newsystem\includes\tcpdf\tcpdf.php
';

$query = $pdo->query("
    SELECT 
        s.full_name AS student_name,
        p.amount_paid,
        p.method,
        p.payment_date
    FROM payments p
    JOIN students s ON p.student_id = s.id
    ORDER BY p.payment_date DESC
");
$payments = $query->fetchAll(PDO::FETCH_ASSOC);

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Online School Fee Management - Payment Report', 0, 1, 'C');
$pdf->Ln(5);

$html = '<table border="1" cellpadding="4">
<tr>
<th><b>Student</b></th>
<th><b>Amount (Ksh)</b></th>
<th><b>Method</b></th>
<th><b>Date</b></th>
</tr>';

foreach ($payments as $p) {
    $html .= '<tr>
        <td>'.htmlspecialchars($p['student_name']).'</td>
        <td>'.number_format($p['amount_paid'], 2).'</td>
        <td>'.htmlspecialchars($p['method']).'</td>
        <td>'.date('d M Y', strtotime($p['payment_date'])).'</td>
    </tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('payment_report.pdf', 'I');
?>
