<?php
require_once __DIR__ . '/../app/middleware.php';
requireRole(['general_user', 'bibliotecario', 'tutor','admin', 'owner']);

include __DIR__ . '/templates/dashboard_base.php';

if ($_SESSION['role'] === 'owner') {
    include __DIR__ . '/templates/admin_panel.php';
}
?>