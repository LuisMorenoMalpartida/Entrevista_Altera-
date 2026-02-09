<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$paymentId = isset($_GET['payment_id']) ? (int) $_GET['payment_id'] : 0;
$loanId = isset($_GET['loan_id']) ? (int) $_GET['loan_id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM pagos WHERE id = :id AND prestamo_id = :loan_id');
$stmt->execute([':id' => $paymentId, ':loan_id' => $loanId]);
$payment = $stmt->fetch();

if (!$payment) {
    echo '<div class="alert alert-danger">Pago no encontrado.</div>';
    return;
}
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Prestamo #<?= (int) $loanId ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar pago</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Editar pago</h3>
    <a class="btn btn-outline-secondary" href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Volver</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?action=update_payment">
            <input type="hidden" name="loan_id" value="<?= (int) $loanId ?>">
            <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha_pago" class="form-control" value="<?= htmlspecialchars($payment['fecha_pago']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($payment['monto']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metodo</label>
                    <select name="metodo" class="form-select" required>
                        <option value="TRANSFERENCIA" <?= $payment['metodo'] === 'TRANSFERENCIA' ? 'selected' : '' ?>>Transferencia</option>
                        <option value="EFECTIVO" <?= $payment['metodo'] === 'EFECTIVO' ? 'selected' : '' ?>>Efectivo</option>
                        <option value="YAPE" <?= $payment['metodo'] === 'YAPE' ? 'selected' : '' ?>>Yape</option>
                        <option value="PLIN" <?= $payment['metodo'] === 'PLIN' ? 'selected' : '' ?>>Plin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nota</label>
                    <input type="text" name="nota" class="form-control" value="<?= htmlspecialchars($payment['nota']) ?>">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
