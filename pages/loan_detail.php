<?php

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/loan.php';

$pdo = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT prestamos.*, clientes.nombre AS client_name, clientes.email, clientes.telefono, clientes.tipo, clientes.documento FROM prestamos JOIN clientes ON clientes.id = prestamos.cliente_id WHERE prestamos.id = :id');
$stmt->execute([':id' => $id]);
$loan = $stmt->fetch();

if (!$loan) {
    echo '<div class="alert alert-danger">Prestamo no encontrado.</div>';
    return;
}

$status = loan_status($loan);
$daysOverdue = loan_days_overdue($loan);

$payments = $pdo->prepare('SELECT * FROM pagos WHERE prestamo_id = :id ORDER BY fecha_pago DESC, id DESC');
$payments->execute([':id' => $id]);
$payments = $payments->fetchAll();

$collections = $pdo->prepare('SELECT * FROM gestiones_cobranza WHERE prestamo_id = :id ORDER BY fecha_hora DESC, id DESC');
$collections->execute([':id' => $id]);
$collections = $collections->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item active" aria-current="page">Prestamo #<?= (int) $loan['id'] ?></li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="mb-0">Prestamo #<?= (int) $loan['id'] ?></h3>
        <div class="text-muted"><?= htmlspecialchars($loan['client_name']) ?> (<?= htmlspecialchars($loan['tipo']) ?>) - <?= htmlspecialchars($loan['documento']) ?></div>
        <div class="text-muted"><?= htmlspecialchars($loan['email']) ?> - <?= htmlspecialchars($loan['telefono']) ?></div>
    </div>
    <a class="btn btn-outline-secondary" href="index.php?page=loans">Volver</a>
</div>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Saldo</div>
                <div class="fs-4 fw-semibold"><?= number_format((float) $loan['saldo_pendiente'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Proxima fecha</div>
                <div class="fs-5 fw-semibold"><?= htmlspecialchars($loan['proxima_fecha_pago']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Estado</div>
                <div class="fs-5 fw-semibold"><?= $status ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-muted">Dias atraso</div>
                <div class="fs-5 fw-semibold"><?= (int) $daysOverdue ?></div>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="card-title mb-0">Recordatorio WhatsApp</h5>
            <button class="btn btn-outline-primary btn-sm" type="button" id="copy_whatsapp">Copiar</button>
        </div>
        <?php
            $reminderText = sprintf(
                'Hola %s, le recordamos el pago del prestamo #%d. Saldo pendiente: %s. Proxima fecha de pago: %s. Dias de atraso: %d. Gracias.',
                $loan['client_name'],
                $loan['id'],
                number_format((float) $loan['saldo_pendiente'], 2),
                $loan['proxima_fecha_pago'],
                $daysOverdue
            );
        ?>
        <textarea class="form-control" rows="3" id="whatsapp_text" readonly><?= htmlspecialchars($reminderText) ?></textarea>
    </div>
</div>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab">Pagos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="gestion-tab" data-bs-toggle="tab" data-bs-target="#gestion" type="button" role="tab">Gestion</button>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Registrar pago</h5>
                        <form method="post" action="index.php?action=add_payment">
                            <input type="hidden" name="prestamo_id" value="<?= (int) $loan['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha_pago" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Monto</label>
                                <input type="number" step="0.01" name="monto" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Metodo</label>
                                <select name="metodo" class="form-select" required>
                                    <option value="TRANSFERENCIA">Transferencia</option>
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="YAPE">Yape</option>
                                    <option value="PLIN">Plin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nota</label>
                                <textarea name="nota" class="form-control" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">Guardar pago</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Pagos</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Metodo</th>
                                    <th>Nota</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($payments)): ?>
                                    <tr><td colspan="4" class="text-muted">Sin pagos.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payment['fecha_pago']) ?></td>
                                        <td><?= number_format((float) $payment['monto'], 2) ?></td>
                                        <td><?= htmlspecialchars($payment['metodo']) ?></td>
                                        <td><?= htmlspecialchars($payment['nota']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="gestion" role="tabpanel" aria-labelledby="gestion-tab">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Registrar gestion</h5>
                        <form method="post" action="index.php?action=add_collection">
                            <input type="hidden" name="prestamo_id" value="<?= (int) $loan['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Fecha y hora</label>
                                <input type="datetime-local" name="fecha_hora" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Canal</label>
                                <select name="canal" class="form-select" required>
                                    <option value="LLAMADA">Llamada</option>
                                    <option value="WHATSAPP">WhatsApp</option>
                                    <option value="EMAIL">Email</option>
                                    <option value="VISITA">Visita</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Resultado</label>
                                <select name="resultado" class="form-select" required>
                                    <option value="CONTACTADO">Contactado</option>
                                    <option value="NO_CONTACTADO">No contactado</option>
                                    <option value="PROMESA_PAGO">Promesa de pago</option>
                                    <option value="YA_PAGO">Ya pago</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comentario</label>
                                <textarea name="comentario" class="form-control" rows="2" required></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">Guardar gestion</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Bitacora de gestion</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Canal</th>
                                    <th>Resultado</th>
                                    <th>Comentario</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($collections)): ?>
                                    <tr><td colspan="4" class="text-muted">Sin gestiones.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($collections as $collection): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($collection['fecha_hora']) ?></td>
                                        <td><?= htmlspecialchars($collection['canal']) ?></td>
                                        <td><?= htmlspecialchars($collection['resultado']) ?></td>
                                        <td><?= htmlspecialchars($collection['comentario']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var button = document.getElementById('copy_whatsapp');
        var textarea = document.getElementById('whatsapp_text');
        if (!button || !textarea) {
            return;
        }
        button.addEventListener('click', function () {
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);
            document.execCommand('copy');
        });
    })();
</script>
