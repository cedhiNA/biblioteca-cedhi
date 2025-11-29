<?php
require_once __DIR__ . '/../../app/middleware.php';
requireRole(['admin', 'owner']);

include __DIR__ . '/../templates/dashboard_base.php';