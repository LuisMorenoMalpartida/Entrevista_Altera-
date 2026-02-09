<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$clients = $pdo->query('SELECT id, nombre, tipo, documento FROM clientes ORDER BY nombre')->fetchAll();
$selectedClientId = isset($_GET['cliente_id']) ? (int) $_GET['cliente_id'] : 0;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nuevo prestamo</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Nuevo prestamo</h3>
    <a class="btn btn-outline-secondary" href="index.php?page=loans">Volver</a>
</div>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">Nuevo cliente</h5>
        <form class="mb-4" method="post" action="index.php?action=add_client">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" id="cliente_tipo" class="form-select" required>
                        <option value="PERSONA">PERSONA</option>
                        <option value="NEGOCIO">NEGOCIO</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Documento</label>
                    <select name="documento_tipo" id="documento_tipo" class="form-select" required>
                        <option value="DNI">DNI</option>
                        <option value="RUC">RUC</option>
                        <option value="PASAPORTE">Pasaporte</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Numero</label>
                    <input type="text" name="documento_numero" class="form-control" required>
                    <div class="form-text" id="documento_help">DNI: 8 digitos.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100" type="submit">Crear cliente</button>
                </div>
            </div>
        </form>
        <script>
            (function () {
                var tipo = document.getElementById('cliente_tipo');
                var docTipo = document.getElementById('documento_tipo');

                function syncDocOptions() {
                    var isNegocio = tipo.value === 'NEGOCIO';
                    Array.prototype.forEach.call(docTipo.options, function (opt) {
                        if (opt.value === 'RUC') {
                            opt.disabled = !isNegocio;
                        } else {
                            opt.disabled = isNegocio;
                        }
                    });
                    if (isNegocio) {
                        docTipo.value = 'RUC';
                        document.getElementById('documento_help').textContent = 'RUC: 11 digitos.';
                    } else if (docTipo.value === 'RUC') {
                        docTipo.value = 'DNI';
                        document.getElementById('documento_help').textContent = 'DNI: 8 digitos.';
                    }
                }

                docTipo.addEventListener('change', function () {
                    if (docTipo.value === 'PASAPORTE') {
                        document.getElementById('documento_help').textContent = 'Pasaporte: 6 a 12 caracteres.';
                    } else if (docTipo.value === 'DNI') {
                        document.getElementById('documento_help').textContent = 'DNI: 8 digitos.';
                    } else {
                        document.getElementById('documento_help').textContent = 'RUC: 11 digitos.';
                    }
                });

                tipo.addEventListener('change', syncDocOptions);
                syncDocOptions();
            })();
        </script>
        <hr>
        <h5 class="card-title">Nuevo prestamo</h5>
        <form method="post" action="index.php?action=add_loan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">Selecciona</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= (int) $client['id'] ?>" <?= $selectedClientId === (int) $client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['nombre']) ?> (<?= htmlspecialchars($client['tipo']) ?>) - <?= htmlspecialchars($client['documento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Monto original</label>
                    <input type="number" step="0.01" name="monto_original" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Saldo pendiente</label>
                    <input type="number" step="0.01" name="saldo_pendiente" class="form-control" placeholder="Si vacio, usa monto original">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tasa</label>
                    <input type="number" step="0.001" name="tasa" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha desembolso</label>
                    <input type="date" name="fecha_desembolso" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Proxima fecha pago</label>
                    <input type="date" name="proxima_fecha_pago" class="form-control" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Crear prestamo</button>
            </div>
        </form>
    </div>
</div>
