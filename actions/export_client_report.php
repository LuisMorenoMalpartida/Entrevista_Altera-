<?php

require_login();
if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR', 'SUPERVISOR', 'AUDITOR'], true)) {
    $_SESSION['flash'] = 'No tienes permisos para exportar.';
    header('Location: index.php?page=loans');
    exit;
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;
if ($clientId <= 0) {
    $_SESSION['flash'] = 'Cliente no valido.';
    header('Location: index.php?page=loans');
    exit;
}

$pdo = db();
$clientStmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
$clientStmt->execute([':id' => $clientId]);
$client = $clientStmt->fetch();

if (!$client) {
    $_SESSION['flash'] = 'Cliente no encontrado.';
    header('Location: index.php?page=loans');
    exit;
}

$loansStmt = $pdo->prepare('SELECT * FROM prestamos WHERE cliente_id = :id ORDER BY proxima_fecha_pago ASC');
$loansStmt->execute([':id' => $clientId]);
$loans = $loansStmt->fetchAll();

$filename = 'reporte_mora_cliente_' . $clientId . '_' . (new DateTimeImmutable('today'))->format('Ymd') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Cliente', $client['nombre']]);
fputcsv($output, ['Tipo', $client['tipo']]);
fputcsv($output, []);
fputcsv($output, ['ID', 'Saldo', 'Proxima Fecha Pago', 'Dias Atraso', 'Estado']);

foreach ($loans as $loan) {
    $days = loan_days_overdue($loan);
    $status = loan_status($loan);
    fputcsv($output, [
        $loan['id'],
        number_format((float) $loan['saldo_pendiente'], 2, '.', ''),
        $loan['proxima_fecha_pago'],
        $days,
        $status,
    ]);
}

fclose($output);
exit;
