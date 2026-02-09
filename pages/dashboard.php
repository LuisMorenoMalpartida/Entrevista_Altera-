<?php

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$pdo = db();
$loans = $pdo->query('SELECT prestamos.*, clientes.nombre AS client_name FROM prestamos JOIN clientes ON clientes.id = prestamos.cliente_id')->fetchAll();

$summary = [
    'AL_DIA' => 0,
    'MORA_1_7' => 0,
    'MORA_8_30' => 0,
    'MORA_31_MAS' => 0,
    'CANCELADO' => 0,
];

foreach ($loans as $loan) {
    $status = loan_status($loan);
    if (isset($summary[$status])) {
        $summary[$status]++;
    }
}

$topOverdue = $pdo->query('SELECT prestamos.*, clientes.nombre AS client_name, DATEDIFF(CURDATE(), prestamos.proxima_fecha_pago) AS days_overdue
    FROM prestamos
    JOIN clientes ON clientes.id = prestamos.cliente_id
    WHERE prestamos.saldo_pendiente > 0 AND CURDATE() > prestamos.proxima_fecha_pago
    ORDER BY days_overdue DESC
    LIMIT 5')->fetchAll();
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Dashboard</h3>
    <span class="text-muted"><?= (new DateTimeImmutable('today'))->format('Y-m-d') ?></span>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Prestamos al dia</h6>
                <div class="display-6 fw-semibold text-success"><?= $summary['AL_DIA'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Mora 1-7</h6>
                <div class="display-6 fw-semibold text-danger"><?= $summary['MORA_1_7'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Mora 8-30</h6>
                <div class="display-6 fw-semibold text-danger"><?= $summary['MORA_8_30'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Mora 31+</h6>
                <div class="display-6 fw-semibold text-danger"><?= $summary['MORA_31_MAS'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Prestamos cancelados</h6>
                <div class="display-6 fw-semibold text-secondary"><?= $summary['CANCELADO'] ?></div>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Top atrasados</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Prestamo</th>
                    <th>Dias atraso</th>
                    <th>Saldo</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($topOverdue)): ?>
                    <tr><td colspan="5" class="text-muted">Sin atrasos.</td></tr>
                <?php endif; ?>
                <?php foreach ($topOverdue as $loan): ?>
                    <tr>
                        <td><?= htmlspecialchars($loan['client_name']) ?></td>
                        <td>#<?= htmlspecialchars($loan['id']) ?></td>
                        <td><?= (int) $loan['days_overdue'] ?></td>
                        <td><?= number_format((float) $loan['saldo_pendiente'], 2) ?></td>
                        <td><a class="btn btn-outline-primary btn-sm" href="index.php?page=loan_detail&id=<?= (int) $loan['id'] ?>">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
