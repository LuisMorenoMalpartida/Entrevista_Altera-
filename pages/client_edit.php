<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$loanId = isset($_GET['loan_id']) ? (int) $_GET['loan_id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id');
$stmt->execute([':id' => $id]);
$client = $stmt->fetch();

if (!$client) {
    echo '<div class="alert alert-danger">Cliente no encontrado.</div>';
    return;
}

$docParts = explode(' ', $client['documento'], 2);
$docType = $docParts[0] ?? 'DNI';
$docNumber = $docParts[1] ?? '';
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <?php if ($loanId > 0): ?>
            <li class="breadcrumb-item"><a href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Prestamo #<?= (int) $loanId ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">Editar cliente</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Editar cliente</h3>
    <?php if ($loanId > 0): ?>
        <a class="btn btn-outline-secondary" href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Volver</a>
    <?php else: ?>
        <a class="btn btn-outline-secondary" href="index.php?page=loans">Volver</a>
    <?php endif; ?>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?action=update_client">
            <input type="hidden" name="client_id" value="<?= (int) $client['id'] ?>">
            <input type="hidden" name="loan_id" value="<?= (int) $loanId ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" id="cliente_tipo" class="form-select" required>
                        <option value="PERSONA" <?= $client['tipo'] === 'PERSONA' ? 'selected' : '' ?>>PERSONA</option>
                        <option value="NEGOCIO" <?= $client['tipo'] === 'NEGOCIO' ? 'selected' : '' ?>>NEGOCIO</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($client['nombre']) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Documento</label>
                    <select name="documento_tipo" id="documento_tipo" class="form-select" required>
                        <option value="DNI" <?= $docType === 'DNI' ? 'selected' : '' ?>>DNI</option>
                        <option value="RUC" <?= $docType === 'RUC' ? 'selected' : '' ?>>RUC</option>
                        <option value="PASAPORTE" <?= $docType === 'PASAPORTE' ? 'selected' : '' ?>>Pasaporte</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Numero</label>
                    <input type="text" name="documento_numero" class="form-control" value="<?= htmlspecialchars($docNumber) ?>" required>
                    <div class="form-text" id="documento_help">DNI: 8 digitos.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($client['telefono']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var tipo = document.getElementById('cliente_tipo');
        var docTipo = document.getElementById('documento_tipo');
        var help = document.getElementById('documento_help');

        function setHelp() {
            if (docTipo.value === 'PASAPORTE') {
                help.textContent = 'Pasaporte: 6 a 12 caracteres.';
            } else if (docTipo.value === 'DNI') {
                help.textContent = 'DNI: 8 digitos.';
            } else {
                help.textContent = 'RUC: 11 digitos.';
            }
        }

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
            } else if (docTipo.value === 'RUC') {
                docTipo.value = 'DNI';
            }
            setHelp();
        }

        docTipo.addEventListener('change', setHelp);
        tipo.addEventListener('change', syncDocOptions);
        syncDocOptions();
    })();
</script>
