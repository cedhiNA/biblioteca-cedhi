<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/database.php';
require_once __DIR__ . '/../../app/middleware.php';
requireRole(['owner']);

// Obtener parámetros
$q = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? '');

// Roles permitidos (excluir owner)
$allowed_roles = ['admin', 'general_user', 'bibliotecario', 'tutor'];

try {
    // Base de la consulta
    $sql = "
        SELECT 
            u.id, 
            CONCAT(u.first_name, ' ', u.last_name) AS nombre, 
            u.first_name,
            u.last_name,
            u.email,
            u.role,
            u.estado
        FROM users u
        WHERE u.role != 'owner'
    ";

    $params = [];

    // Filtro de búsqueda por texto
    if ($q !== '') {
        $sql .= " AND (
            u.first_name LIKE :q1 OR 
            u.last_name LIKE :q2 OR 
            u.email LIKE :q3 OR
            CONCAT(u.first_name, ' ', u.last_name) LIKE :q4
        )";
        $searchTerm = "%$q%";
        $params[":q1"] = $searchTerm;
        $params[":q2"] = $searchTerm;
        $params[":q3"] = $searchTerm;
        $params[":q4"] = $searchTerm;
    }

    // Filtro por rol específico
    if ($role !== '' && in_array($role, $allowed_roles)) {
        $sql .= " AND u.role = :role";
        $params[":role"] = $role;
    }

    // Ordenar y limitar resultados
    $sql .= " ORDER BY u.first_name ASC, u.last_name ASC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agregar información adicional para cada usuario
    foreach ($resultados as &$usuario) {
        // Mapear nombres de roles a español
        $rol_labels = [
            'admin' => 'Administrador',
            'general_user' => 'Usuario General',
            'bibliotecario' => 'Bibliotecario',
            'tutor' => 'Tutor'
        ];
        
        $usuario['rol_label'] = $rol_labels[$usuario['role']] ?? $usuario['role'];
        
        // Si es admin, obtener módulos asignados
        if ($usuario['role'] === 'admin') {
            $stmt_modules = $pdo->prepare("
                SELECT m.module_name 
                FROM module_admins ma
                JOIN modules m ON ma.module_id = m.id
                WHERE ma.user_id = ?
            ");
            $stmt_modules->execute([$usuario['id']]);
            $modulos = $stmt_modules->fetchAll(PDO::FETCH_COLUMN);
            $usuario['modulos'] = $modulos;
            $usuario['modulos_texto'] = empty($modulos) ? 'Sin módulos' : implode(', ', $modulos);
        }
        
        // Estado con badge
        $usuario['estado_badge'] = $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo';
    }

    echo json_encode($resultados);

} catch (PDOException $e) {
    error_log("Error en buscar_usuario.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al buscar usuarios'
    ]);
}