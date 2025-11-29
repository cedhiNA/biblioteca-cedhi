<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

function canAccessModule($userId, $moduleId, $userRole) {
    global $pdo;

    if (!$userId || !$userRole) return 'no_user';

    $SALA_DE_LECTURA_ID = 4;
    $PLANES_NEGOCIO_ID = 2;

    $nonAdminRoles = ['bibliotecario', 'tutor', 'owner', 'general_user'];

    if ($moduleId == $SALA_DE_LECTURA_ID) {
        if ($userRole === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM module_admins WHERE user_id = ? AND module_id = ?");
            $stmt->execute([$userId, $moduleId]);
            return $stmt->fetch() ? 'access' : 'not_assigned';
        }
        if (in_array($userRole, $nonAdminRoles)) return 'access';
       
    }

    if ($moduleId == $PLANES_NEGOCIO_ID) {
        if ($userRole === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM module_admins WHERE user_id = ? AND module_id = ?");
            $stmt->execute([$userId, $moduleId]);
            return $stmt->fetch() ? 'access' : 'not_assigned';
        }
        return 'access';
    }

    return 'access';
}
?>