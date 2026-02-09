<?php

$config = require __DIR__ . '/../config.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-3">Ingreso</h4>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="index.php?action=login">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Entrar</button>
                </form>
                <p class="text-muted small mt-3 mb-0">Primera vez: admin / admin123 (crea el usuario ADMIN si la tabla usuarios esta vacia).</p>
            </div>
        </div>
    </div>
</div>
