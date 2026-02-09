<?php

require_once __DIR__ . '/../lib/db.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
    </ol>
</nav>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Nuevo usuario</h3>
    <a class="btn btn-outline-secondary" href="index.php?page=dashboard">Volver</a>
</div>
<?php if ($flash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="index.php?action=add_user">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rol</label>
                    <select name="role" class="form-select" required>
                        <option value="ADMIN">ADMIN</option>
                        <option value="COBRADOR">COBRADOR</option>
                        <option value="SUPERVISOR">SUPERVISOR</option>
                        <option value="AUDITOR">AUDITOR</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Crear usuario</button>
            </div>
        </form>
    </div>
</div>
