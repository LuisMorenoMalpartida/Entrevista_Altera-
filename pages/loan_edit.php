<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT prestamos.*, clientes.nombre AS client_name FROM prestamos JOIN clientes ON clientes.id = prestamos.cliente_id WHERE prestamos.id = :id');
$stmt->execute([':id' => $id]);
$loan = $stmt->fetch();

if (!$loan) {
    echo '<div class="alert alert-danger">Prestamo no encontrado.</div>';
    return;
}
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loan_detail&id=<?= (int) $loan['id'] ?>">Prestamo #<?= (int) $loan['id'] ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Editar prestamo #<?= (int) $loan['id'] ?></h3>
    <a class="btn btn-outline-secondary" href="index.php?page=loan_detail&id=<?= (int) $loan['id'] ?>">Volver</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?action=update_loan">
            <input type="hidden" name="loan_id" value="<?= (int) $loan['id'] ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($loan['client_name']) ?>" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Monto original</label>
                    <input type="number" step="0.01" name="monto_original" class="form-control" value="<?= htmlspecialchars($loan['monto_original']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Saldo pendiente</label>
                    <input type="number" step="0.01" name="saldo_pendiente" class="form-control" value="<?= htmlspecialchars($loan['saldo_pendiente']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tasa</label>
                    <input type="number" step="0.001" name="tasa" class="form-control" value="<?= htmlspecialchars($loan['tasa']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha desembolso</label>
                    <input type="date" name="fecha_desembolso" class="form-control" value="<?= htmlspecialchars($loan['fecha_desembolso']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Proxima fecha pago</label>
                    <input type="date" name="proxima_fecha_pago" class="form-control" value="<?= htmlspecialchars($loan['proxima_fecha_pago']) ?>" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
