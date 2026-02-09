<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();
$collectionId = isset($_GET['collection_id']) ? (int) $_GET['collection_id'] : 0;
$loanId = isset($_GET['loan_id']) ? (int) $_GET['loan_id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM gestiones_cobranza WHERE id = :id AND prestamo_id = :loan_id');
$stmt->execute([':id' => $collectionId, ':loan_id' => $loanId]);
$collection = $stmt->fetch();

if (!$collection) {
    echo '<div class="alert alert-danger">Gestion no encontrada.</div>';
    return;
}
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loans">Prestamos</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Prestamo #<?= (int) $loanId ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Editar gestion</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Editar gestion</h3>
    <a class="btn btn-outline-secondary" href="index.php?page=loan_detail&id=<?= (int) $loanId ?>">Volver</a>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?action=update_collection">
            <input type="hidden" name="loan_id" value="<?= (int) $loanId ?>">
            <input type="hidden" name="collection_id" value="<?= (int) $collection['id'] ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha y hora</label>
                    <input type="datetime-local" name="fecha_hora" class="form-control" value="<?= htmlspecialchars(str_replace(' ', 'T', $collection['fecha_hora'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Canal</label>
                    <select name="canal" class="form-select" required>
                        <option value="LLAMADA" <?= $collection['canal'] === 'LLAMADA' ? 'selected' : '' ?>>Llamada</option>
                        <option value="WHATSAPP" <?= $collection['canal'] === 'WHATSAPP' ? 'selected' : '' ?>>WhatsApp</option>
                        <option value="EMAIL" <?= $collection['canal'] === 'EMAIL' ? 'selected' : '' ?>>Email</option>
                        <option value="VISITA" <?= $collection['canal'] === 'VISITA' ? 'selected' : '' ?>>Visita</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Resultado</label>
                    <select name="resultado" class="form-select" required>
                        <option value="CONTACTADO" <?= $collection['resultado'] === 'CONTACTADO' ? 'selected' : '' ?>>Contactado</option>
                        <option value="NO_CONTACTADO" <?= $collection['resultado'] === 'NO_CONTACTADO' ? 'selected' : '' ?>>No contactado</option>
                        <option value="PROMESA_PAGO" <?= $collection['resultado'] === 'PROMESA_PAGO' ? 'selected' : '' ?>>Promesa de pago</option>
                        <option value="YA_PAGO" <?= $collection['resultado'] === 'YA_PAGO' ? 'selected' : '' ?>>Ya pago</option>
                        <option value="OTRO" <?= $collection['resultado'] === 'OTRO' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Comentario</label>
                    <textarea name="comentario" class="form-control" rows="2" required><?= htmlspecialchars($collection['comentario']) ?></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
