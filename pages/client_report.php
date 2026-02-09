<?php

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$pdo = db();
$clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;

if ($clientId <= 0) {
    echo '<div class="alert alert-danger">Cliente no valido.</div>';
    return;
}

$clientStmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
$clientStmt->execute([':id' => $clientId]);
$client = $clientStmt->fetch();

if (!$client) {
    echo '<div class="alert alert-danger">Cliente no encontrado.</div>';
    return;
}

$loansStmt = $pdo->prepare('SELECT * FROM prestamos WHERE cliente_id = :id ORDER BY proxima_fecha_pago ASC');
$loansStmt->execute([':id' => $clientId]);
$loans = $loansStmt->fetchAll();

$summary = [
    'AL_DIA' => 0,
    'MORA_1_7' => 0,
    'MORA_8_30' => 0,
    'MORA_31_MAS' => 0,
    'CANCELADO' => 0,
];

$rows = [];
foreach ($loans as $loan) {
    $status = loan_status($loan);
    $days = loan_days_overdue($loan);
    if (isset($summary[$status])) {
        $summary[$status]++;
    }
    $loan['status'] = $status;
    $loan['days_overdue'] = $days;
    $rows[] = $loan;
}
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item active" aria-current="page">Reporte de mora</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="mb-0">Reporte de mora</h3>
        <div class="text-muted"><?= htmlspecialchars($client['nombre']) ?> (<?= htmlspecialchars($client['tipo']) ?>)</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="index.php?page=loans">Volver</a>
        <a class="btn btn-outline-primary" href="index.php?action=export_client_report&client_id=<?= (int) $clientId ?>">Exportar CSV</a>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Al dia</div>
                <div class="fs-4 fw-semibold text-success"><?= $summary['AL_DIA'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Mora 1-7</div>
                <div class="fs-4 fw-semibold text-danger"><?= $summary['MORA_1_7'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Mora 8-30</div>
                <div class="fs-4 fw-semibold text-danger"><?= $summary['MORA_8_30'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Mora 31+</div>
                <div class="fs-4 fw-semibold text-danger"><?= $summary['MORA_31_MAS'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Cancelados</div>
                <div class="fs-4 fw-semibold text-secondary"><?= $summary['CANCELADO'] ?></div>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Saldo</th>
                <th>Proximo pago</th>
                <th>Dias atraso</th>
                <th>Estado</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-muted">Sin prestamos.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $loan): ?>
                <tr>
                    <td>#<?= (int) $loan['id'] ?></td>
                    <td><?= number_format((float) $loan['saldo_pendiente'], 2) ?></td>
                    <td><?= htmlspecialchars($loan['proxima_fecha_pago']) ?></td>
                    <td><?= (int) $loan['days_overdue'] ?></td>
                    <td><span class="badge bg-secondary status-<?= strtolower($loan['status']) ?>"><?= $loan['status'] ?></span></td>
                    <td><a class="btn btn-outline-primary btn-sm" href="index.php?page=loan_detail&id=<?= (int) $loan['id'] ?>">Ver</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
