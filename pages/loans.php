<?php

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$pdo = db();
$typeFilter = isset($_GET['type']) && $_GET['type'] !== '' ? $_GET['type'] : '';
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';
$onlyOverdue = isset($_GET['solo_mora']) && $_GET['solo_mora'] === '1';
$nameFilter = isset($_GET['q']) ? trim($_GET['q']) : '';

$types = $pdo->query('SELECT DISTINCT tipo FROM clientes ORDER BY tipo')->fetchAll();

$sql = 'SELECT prestamos.*, clientes.nombre AS client_name, clientes.tipo AS client_type FROM prestamos JOIN clientes ON clientes.id = prestamos.cliente_id';
$params = [];
$conditions = [];
if ($typeFilter !== '') {
    $conditions[] = 'clientes.tipo = :type';
    $params[':type'] = $typeFilter;
}
if ($nameFilter !== '') {
    $conditions[] = 'clientes.nombre LIKE :name';
    $params[':name'] = '%' . $nameFilter . '%';
}
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY prestamos.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$loans = $stmt->fetchAll();

$filtered = [];
foreach ($loans as $loan) {
    $status = loan_status($loan);
    if ($statusFilter !== '' && $status !== $statusFilter) {
        continue;
    }
    if ($onlyOverdue && strpos($status, 'MORA_') !== 0) {
        continue;
    }
    $loan['status'] = $status;
    $loan['days_overdue'] = loan_days_overdue($loan);
    $filtered[] = $loan;
}

$statuses = ['AL_DIA', 'MORA_1_7', 'MORA_8_30', 'MORA_31_MAS', 'CANCELADO'];
?>
<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Prestamos</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Prestamos</h3>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="index.php?action=export_overdue">Exportar mora CSV</a>
        <a class="btn btn-primary" href="index.php?page=loan_new">Nuevo prestamo</a>
    </div>
</div>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<form class="row g-2 mb-3" method="get" action="index.php">
    <input type="hidden" name="page" value="loans">
    <div class="col-md-3">
        <label class="form-label">Tipo cliente</label>
        <select class="form-select" name="type">
            <option value="">Todos</option>
            <?php foreach ($types as $row): ?>
                <option value="<?= htmlspecialchars($row['tipo']) ?>" <?= $typeFilter === $row['tipo'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['tipo']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select" name="status">
            <option value="">Todos</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                    <?= $status ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Cliente</label>
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre" value="<?= htmlspecialchars($nameFilter) ?>">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="solo_mora" value="1" id="solo_mora" <?= $onlyOverdue ? 'checked' : '' ?>>
            <label class="form-check-label" for="solo_mora">Solo mora</label>
        </div>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100" type="submit">Filtrar</button>
    </div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Saldo</th>
                <th>Proximo pago</th>
                <th>Dias atraso</th>
                <th>Estado</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($filtered)): ?>
                <tr><td colspan="7" class="text-muted">Sin prestamos.</td></tr>
            <?php endif; ?>
            <?php foreach ($filtered as $loan): ?>
                <tr>
                    <td><?= htmlspecialchars($loan['client_name']) ?></td>
                    <td><?= htmlspecialchars($loan['client_type']) ?></td>
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
