<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/loan.php';

$config = require __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';

if ($action !== '') {
    switch ($action) {
        case 'login':
            include __DIR__ . '/actions/login.php';
            break;
        case 'logout':
            include __DIR__ . '/actions/logout.php';
            break;
        case 'add_payment':
            include __DIR__ . '/actions/add_payment.php';
            break;
        case 'add_collection':
            include __DIR__ . '/actions/add_collection.php';
            break;
        case 'add_loan':
            include __DIR__ . '/actions/add_loan.php';
            break;
        case 'add_client':
            include __DIR__ . '/actions/add_client.php';
            break;
        case 'add_user':
            include __DIR__ . '/actions/add_user.php';
            break;
        case 'export_overdue':
            include __DIR__ . '/actions/export_overdue.php';
            break;
        case 'export_client_report':
            include __DIR__ . '/actions/export_client_report.php';
            break;
        case 'update_payment':
            include __DIR__ . '/actions/update_payment.php';
            break;
        case 'delete_payment':
            include __DIR__ . '/actions/delete_payment.php';
            break;
        case 'update_collection':
            include __DIR__ . '/actions/update_collection.php';
            break;
        case 'delete_collection':
            include __DIR__ . '/actions/delete_collection.php';
            break;
        case 'update_loan':
            include __DIR__ . '/actions/update_loan.php';
            break;
        case 'delete_loan':
            include __DIR__ . '/actions/delete_loan.php';
            break;
        case 'update_client':
            include __DIR__ . '/actions/update_client.php';
            break;
        case 'delete_client':
            include __DIR__ . '/actions/delete_client.php';
            break;
        default:
            $_SESSION['flash'] = 'Accion no valida.';
            header('Location: index.php?page=dashboard');
            exit;
    }
}

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'login') {
    if (is_logged_in()) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    include __DIR__ . '/partials/header.php';
    include __DIR__ . '/pages/login.php';
    include __DIR__ . '/partials/footer.php';
    exit;
}

require_login();

include __DIR__ . '/partials/header.php';

switch ($page) {
    case 'dashboard':
        include __DIR__ . '/pages/dashboard.php';
        break;
    case 'loans':
        include __DIR__ . '/pages/loans.php';
        break;
    case 'loan_detail':
        include __DIR__ . '/pages/loan_detail.php';
        break;
    case 'loan_new':
        if (current_user_role() !== 'ADMIN') {
            echo '<div class="alert alert-warning">No tienes permisos para crear prestamos.</div>';
            break;
        }
        include __DIR__ . '/pages/loan_new.php';
        break;
    case 'loan_edit':
        if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
            echo '<div class="alert alert-warning">No tienes permisos para editar prestamos.</div>';
            break;
        }
        include __DIR__ . '/pages/loan_edit.php';
        break;
    case 'client_edit':
        if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
            echo '<div class="alert alert-warning">No tienes permisos para editar clientes.</div>';
            break;
        }
        include __DIR__ . '/pages/client_edit.php';
        break;
    case 'client_report':
        include __DIR__ . '/pages/client_report.php';
        break;
    case 'payment_edit':
        if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
            echo '<div class="alert alert-warning">No tienes permisos para editar pagos.</div>';
            break;
        }
        include __DIR__ . '/pages/payment_edit.php';
        break;
    case 'collection_edit':
        if (!in_array(current_user_role(), ['ADMIN', 'COBRADOR'], true)) {
            echo '<div class="alert alert-warning">No tienes permisos para editar gestiones.</div>';
            break;
        }
        include __DIR__ . '/pages/collection_edit.php';
        break;
    case 'user_new':
        if (current_user_role() !== 'ADMIN') {
            echo '<div class="alert alert-warning">No tienes permisos para crear usuarios.</div>';
            break;
        }
        include __DIR__ . '/pages/user_new.php';
        break;
    default:
        echo '<div class="alert alert-warning">Pagina no encontrada.</div>';
        break;
}

include __DIR__ . '/partials/footer.php';
