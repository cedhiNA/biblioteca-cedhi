<?php
require_once __DIR__ . '/../../app/middleware.php';
requireRole(['owner']);
try {
    require_once __DIR__ . '/../../app/database.php';
} catch (Exception $e) {
    error_log("Fallo al incluir la conexión a la BD: " . $e->getMessage());
}


$admin_count = "Error BD";
$admin_modules = ['Owner', 'Planes de Negocio', 'Sala de Lectura'];
$in_clause = "'" . implode("','", $admin_modules) . "'";

if (isset($pdo)) {
    try {
        $sql_admins = "
            SELECT COUNT(DISTINCT ma.user_id) 
            FROM module_admins ma
            INNER JOIN users u ON ma.user_id = u.id
            WHERE u.estado = 'activo'
        ";
        $stmt_admins = $pdo->query($sql_admins);
        $admin_count = $stmt_admins->fetchColumn();
    } catch (PDOException $e) {
        $query_error = "Error al cargar el total de administradores: " . $e->getMessage();
        error_log($query_error);
    }
}


?>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    cedhi: {
                        primary: "#2C3E50", // Gris Oscuro Azulado (Base)
                        secondary: "#34495E", // Tono más claro para hover
                        accent: "#1ABC9C", // Verde Turquesa (Acento)
                        light: "#ECF0F1", // Fondo
                        success: "#27AE60", // Verde Esmeralda
                    }
                }
            }
        }
    }
</script>

<style>
    body {
        background-color: #ECF0F1;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .welcome-header {
        background-color: #2C3E50;
        color: white;
        border-radius: 15px;
        padding: 2.5rem;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .card-style {
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
        border-radius: 0.75rem;
    }

    .card-style:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
</style>

<div class="max-w-6xl mx-auto px-6 mt-12 mb-12">
    <div class="bg-white border border-cedhi-primary rounded-xl">

        <div class="bg-cedhi-primary text-white px-6 py-4 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-crown text-cedhi-accent mr-3 text-xl"></i>
                <h5 class="font-bold text-xl mb-0">Panel de Administración</h5>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="card-style bg-white border-l-4 border-cedhi-accent shadow p-5 flex items-center">
                    <i class="fas fa-user-shield fa-3x text-cedhi-accent mr-4"></i>
                    <div>
                        <div class="text-sm font-semibold text-gray-500 uppercase mb-1">Administradores Activos</div>
                        <div class="text-gray-900 text-3xl font-extrabold"><?php echo $admin_count; ?></div>
                    </div>
                </div>

                <div
                    class="card-style bg-white shadow p-6 text-center flex flex-col justify-center items-center border-b-4 border-cedhi-primary">
                    <i class="fas fa-users-cog fa-3x text-cedhi-primary mb-3"></i>
                    <h6 class="font-bold text-gray-800 mb-3 text-xl">Gestionar Usuarios</h6>
                    <p class="text-gray-500 text-sm mb-4">Administrar roles de usuarios y asignar módulos de acceso por rol a los administradores.</p>
                    <a href="owner/admin_gestion.php"
                        class="bg-cedhi-accent text-white font-medium px-4 py-3 rounded-lg w-full flex justify-center items-center gap-2 shadow-md hover:bg-cedhi-primary transition">
                        <i class="fas fa-tools"></i> Abrir Panel de Gestión
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>