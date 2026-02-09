<?php

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR', 'SUPERVISOR', 'AUDITOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para exportar.';
    header('Location: index.php?page=loans');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$pdo = db();
$stmt = $pdo->query('SELECT prestamos.*, clientes.nombre AS client_name, clientes.tipo AS client_type FROM prestamos JOIN clientes ON clientes.id = prestamos.cliente_id WHERE prestamos.saldo_pendiente > 0 AND prestamos.proxima_fecha_pago < CURDATE() ORDER BY prestamos.proxima_fecha_pago ASC');
$loans = $stmt->fetchAll();

$filename = 'prestamos_mora_' . (new DateTimeImmutable('today'))->format('Ymd') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Cliente', 'Tipo', 'Saldo', 'Proxima Fecha Pago', 'Dias Atraso', 'Estado']);

foreach ($loans as $loan) {
    $days = loan_days_overdue($loan);
    $status = loan_status($loan);
    fputcsv($output, [
        $loan['id'],
        $loan['client_name'],
        $loan['client_type'],
        number_format((float) $loan['saldo_pendiente'], 2, '.', ''),
        $loan['proxima_fecha_pago'],
        $days,
        $status,
    ]);
}

fclose($output);
exit;
