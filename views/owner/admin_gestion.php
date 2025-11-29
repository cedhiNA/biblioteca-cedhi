<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../app/middleware.php';
requireRole(['owner']);

function url($path)
{
    global $base_url;
    return $base_url . '/' . $path;
}

try {
    require_once __DIR__ . '/../../app/database.php';
} catch (Exception $e) {
    error_log("Error al incluir la BD: " . $e->getMessage());
    $pdo = null;
}

$message = '';
$message_type = '';
$modulos_asignables = [];
$module_map_name_to_id = [];

$current_role_filter = $_GET['role'] ?? $_SESSION['admin_gestion_role'] ?? 'admin';
$_SESSION['admin_gestion_role'] = $current_role_filter;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20; // Registros por página
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$roles_disponibles = [
    'admin' => ['group' => 'Administradores','label' => 'Administrador', 'icon' => 'fa-user-shield', 'show_modules' => true],
    'general_user' => ['group' => 'Usuarios Generales', 'label' => 'Usuario General', 'icon' => 'fa-user', 'show_modules' => false],
    'bibliotecario' => ['group' => 'Bibliotecarios','label' => 'Bibliotecario', 'icon' => 'fa-book-reader', 'show_modules' => false],
    'tutor' => ['group' => 'Tutores', 'label' => 'Tutor', 'icon' => 'fa-chalkboard-teacher', 'show_modules' => false]
];

if (isset($pdo)) {
    try {
        $stmt_mods = $pdo->prepare("
            SELECT id, module_name 
            FROM modules 
            WHERE module_name IN ('Sala de Lectura', 'Repositorio de Planes de Negocios')
            ORDER BY id ASC
        ");
        $stmt_mods->execute();
        $modules = $stmt_mods->fetchAll(PDO::FETCH_ASSOC);

        foreach ($modules as $mod) {
            $modulos_asignables[$mod['module_name']] = $mod['module_name'];
            $module_map_name_to_id[$mod['module_name']] = $mod['id'];
        }
    } catch (PDOException $e) {
        $message = "Error al cargar los módulos: " . $e->getMessage();
        $message_type = 'danger';
    }
}

$usuarios_registrados = [];
$total_usuarios = 0;
$total_pages = 0;

if (isset($pdo)) {
    try {
        $where_search = "";
        $params_search = [];
        
        if ($search !== '') {
            $where_search = " AND (u.first_name LIKE :search1 OR u.last_name LIKE :search2 OR u.email LIKE :search3 OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search4)";
            $search_term = "%$search%";
            $params_search = [
                ':search1' => $search_term,
                ':search2' => $search_term,
                ':search3' => $search_term,
                ':search4' => $search_term
            ];
        }

        $count_sql = "SELECT COUNT(*) FROM users u WHERE u.role = :role" . $where_search;
        $stmt_count = $pdo->prepare($count_sql);
        $stmt_count->bindParam(':role', $current_role_filter);
        foreach ($params_search as $key => $value) {
            $stmt_count->bindValue($key, $value);
        }
        $stmt_count->execute();
        $total_usuarios = $stmt_count->fetchColumn();
        $total_pages = ceil($total_usuarios / $per_page);

        if ($current_role_filter === 'admin') {
            $sql_fetch = "
                SELECT 
                    u.id AS user_id, 
                    u.first_name AS nombre, 
                    u.last_name AS apellido, 
                    u.email AS correo,
                    u.estado,
                    u.role,
                    GROUP_CONCAT(DISTINCT COALESCE(m.module_name, 'Sin módulo asignado') SEPARATOR ', ') AS modulos
                FROM users u
                LEFT JOIN module_admins ma ON u.id = ma.user_id
                LEFT JOIN modules m ON ma.module_id = m.id
                WHERE u.role = :role" . $where_search . "
                GROUP BY u.id
                ORDER BY u.first_name ASC, u.last_name ASC
                LIMIT :limit OFFSET :offset
            ";
        } else {
            $sql_fetch = "
                SELECT 
                    u.id AS user_id, 
                    u.first_name AS nombre, 
                    u.last_name AS apellido, 
                    u.email AS correo,
                    u.estado,
                    u.role
                FROM users u
                WHERE u.role = :role" . $where_search . "
                ORDER BY u.first_name ASC, u.last_name ASC
                LIMIT :limit OFFSET :offset
            ";
        }
        
        $stmt_fetch = $pdo->prepare($sql_fetch);
        $stmt_fetch->bindParam(':role', $current_role_filter);
        $stmt_fetch->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmt_fetch->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($params_search as $key => $value) {
            $stmt_fetch->bindValue($key, $value);
        }
        $stmt_fetch->execute();
        $usuarios_registrados = $stmt_fetch->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error al cargar usuarios: " . $e->getMessage();
        $message_type = 'danger';
        error_log("Error al cargar lista: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_modulos') {
    $user_id = intval($_POST['user_id']);
    $modulos = isset($_POST['module_ids']) ? explode(',', $_POST['module_ids']) : [];

    try {
        $stmt = $pdo->prepare("DELETE FROM module_admins WHERE user_id = ?");
        $stmt->execute([$user_id]);

        if (!empty($modulos)) {
            $stmt = $pdo->prepare("INSERT INTO module_admins (user_id, module_id) VALUES (?, ?)");
            foreach ($modulos as $mod_id) {
                if (is_numeric($mod_id) && $mod_id > 0) {
                    $stmt->execute([$user_id, intval($mod_id)]);
                }
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado') {
    $user_id = intval($_POST['user_id']);
    $nuevo_estado = $_POST['estado'] === 'activo' ? 'activo' : 'inactivo';
    try {
        $stmt = $pdo->prepare("UPDATE users SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $user_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_rol') {
    $user_id = intval($_POST['user_id']);
    $nuevo_rol = $_POST['nuevo_rol'];
    
    if (array_key_exists($nuevo_rol, $roles_disponibles)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$nuevo_rol, $user_id]);
            
            if ($nuevo_rol !== 'admin') {
                $stmt_del = $pdo->prepare("DELETE FROM module_admins WHERE user_id = ?");
                $stmt_del->execute([$user_id]);
            }
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Rol no válido']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    cedhi: {
                        primary: "#2C3E50",
                        secondary: "#34495E",
                        accent: "#1ABC9C",
                        light: "#ECF0F1",
                        success: "#27AE60",
                        danger: "#E74C3C"
                    }
                }
            }
        }
    };
    </script>
    <style>
    body {
        background-color: #ECF0F1;
    }

    .input-focus:focus {
        border-color: #1ABC9C;
        box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.5);
    }

    input[type="checkbox"].form-checkbox {
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        border: 2px solid #1ABC9C;
        width: 18px;
        height: 18px;
        border-radius: 0.25rem;
        display: inline-block;
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        vertical-align: middle;
    }

    input[type="checkbox"].form-checkbox:checked {
        background-color: #1ABC9C;
        border-color: #1ABC9C;
    }

    input[type="checkbox"].form-checkbox:checked::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 5px;
        width: 4px;
        height: 8px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .role-tab {
        transition: all 0.3s ease;
    }

    .role-tab.active {
        background-color: #1ABC9C;
        color: white;
        transform: scale(1.05);
    }

    .pagination-btn {
        transition: all 0.2s;
    }

    .pagination-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    </style>
</head>

<body class="bg-cedhi-light min-h-screen">

    <header
        class="flex justify-between items-center p-4 sm:px-8 bg-cedhi-primary text-white shadow-xl sticky top-0 z-10">
        <div class="flex items-center space-x-2 sm:space-x-4">
            <a href="../dashboard.php" class="text-cedhi-accent hover:text-white transition duration-200">
                <i class="fas fa-arrow-left text-xl mr-2"></i>
            </a>
            <h1 class="text-lg sm:text-2xl font-extrabold tracking-wide">
                Gestión de <span class="text-cedhi-accent">Usuarios</span>
            </h1>
        </div>
        <div class="flex items-center">
            <span class="text-sm font-light mr-4 hidden sm:block">
                <?php echo htmlspecialchars($_SESSION['user_first_name']); ?> (Owner)
            </span>
            <button
                class="flex items-center space-x-1 bg-cedhi-secondary text-white py-2 px-3 rounded-lg hover:bg-cedhi-accent transition-colors"
                onclick="window.location.href='../../logout.php'">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </button>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        <?php if (!empty($message)): ?>
        <div class="p-4 rounded-xl shadow-md mb-6 
                <?php echo ($message_type === 'success' ? 'bg-cedhi-success/10 text-cedhi-success border border-cedhi-success' : 'bg-cedhi-danger/10 text-cedhi-danger border border-cedhi-danger'); ?> 
                flex items-center">
            <i
                class="fas <?php echo ($message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?> mr-3"></i>
            <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
            <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3">
                <?php foreach ($roles_disponibles as $role_key => $role_info): 
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
            $count_stmt->execute([$role_key]);
            $role_count = $count_stmt->fetchColumn();
        ?>
                <button onclick="cambiarRol('<?php echo $role_key; ?>')"
                    class="role-tab px-4 py-2 rounded-lg font-semibold flex flex-row items-center justify-center space-x-2 border-2 border-cedhi-accent transition-all <?php echo $current_role_filter === $role_key ? 'active' : 'bg-white text-cedhi-primary hover:bg-cedhi-light'; ?>">
                    <i class="fas <?php echo $role_info['icon']; ?> text-2xl"></i>
                    <span class="text-sm text-center"><?php echo $role_info['group']; ?></span>
                    <span class="bg-cedhi-accent text-white px-3 py-1 rounded-full text-xs font-bold">
                        <?php echo $role_count; ?>
                    </span>
                </button>

                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-96">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por nombre o email..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full pl-12 pr-10 py-3 border-2 border-gray-300 rounded-lg focus:border-cedhi-accent focus:outline-none transition"
                        onkeyup="buscarConDebounce()" autocomplete="off">
                    <div id="searchLoader" class="hidden absolute right-4 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-spinner fa-spin text-cedhi-accent"></i>
                    </div>
                </div>

                <div class="flex items-center space-x-4 text-sm">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-users text-cedhi-accent"></i>
                        <span class="font-medium">Total: <strong><?php echo $total_usuarios; ?></strong></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-file-alt text-cedhi-accent"></i>
                        <span class="font-medium">Página: <strong><?php echo $page; ?> de
                                <?php echo max(1, $total_pages); ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border-t-4 border-cedhi-accent">
            <h2 class="text-2xl font-bold text-cedhi-primary mb-6 flex items-center">
                <i
                    class="fas <?php echo $roles_disponibles[$current_role_filter]['icon']; ?> text-cedhi-accent mr-3"></i>
                <?php echo $roles_disponibles[$current_role_filter]['group']; ?>
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-cedhi-light">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-cedhi-primary uppercase tracking-wider rounded-tl-lg">
                                Nombre Completo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-cedhi-primary uppercase tracking-wider">
                                Correo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-cedhi-primary uppercase tracking-wider">
                                Rol</th>
                            <?php if ($roles_disponibles[$current_role_filter]['show_modules']): ?>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-cedhi-primary uppercase tracking-wider">
                                Módulo Asignado</th>
                            <?php endif; ?>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-cedhi-primary uppercase tracking-wider rounded-tr-lg">
                                Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($usuarios_registrados)): ?>
                        <tr>
                            <td colspan="<?php echo $roles_disponibles[$current_role_filter]['show_modules'] ? '5' : '4'; ?>"
                                class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                                <p class="italic">
                                    <?php echo $search !== '' ? 'No se encontraron resultados para tu búsqueda.' : 'No hay usuarios con rol "' . $roles_disponibles[$current_role_filter]['label'] . '" registrados.'; ?>
                                </p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($usuarios_registrados as $usuario): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($usuario['correo']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <select onchange="cambiarRolUsuario(<?php echo $usuario['user_id']; ?>, this.value)"
                                    class="px-3 py-1 rounded-lg text-sm font-medium border border-gray-300 
                                        focus:ring-2 focus:ring-cedhi-accent focus:outline-none cursor-pointer transition bg-white">
                                    <?php foreach ($roles_disponibles as $rol_key => $rol_info): ?>
                                    <option value="<?php echo $rol_key; ?>"
                                        <?php echo $usuario['role'] === $rol_key ? 'selected' : ''; ?>>
                                        <?php echo $rol_info['label']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php if ($roles_disponibles[$current_role_filter]['show_modules']): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="flex flex-col space-y-1">
                                    <?php
                                    $stmt_mods = $pdo->prepare("SELECT module_id FROM module_admins WHERE user_id = ?");
                                    $stmt_mods->execute([$usuario['user_id']]);
                                    $modulos_asignados = $stmt_mods->fetchAll(PDO::FETCH_COLUMN);
                                    ?>

                                    <?php foreach ($modules as $mod): ?>
                                    <label class="inline-flex items-center space-x-2">
                                        <input type="checkbox"
                                            class="form-checkbox h-4 w-4 text-cedhi-accent focus:ring-cedhi-accent cursor-pointer"
                                            value="<?php echo $mod['id']; ?>"
                                            <?php echo in_array($mod['id'], $modulos_asignados) ? 'checked' : ''; ?>
                                            onchange="actualizarCheckboxModulos(<?php echo $usuario['user_id']; ?>)">
                                        <span
                                            class="text-sm text-gray-800"><?php echo htmlspecialchars($mod['module_name']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <?php
                                    $estado_actual = $usuario['estado'] ?? 'inactivo';
                                ?>
                                <select
                                    onchange="actualizarEstadoUsuario(<?php echo $usuario['user_id']; ?>, this.value)"
                                    class="px-3 py-1 rounded-lg text-sm font-medium border border-gray-300 
                                        focus:ring-2 focus:ring-cedhi-accent focus:outline-none cursor-pointer transition
                                        <?php echo $estado_actual === 'activo' 
                                            ? 'bg-cedhi-accent/10 text-cedhi-accent border-cedhi-accent/30' 
                                            : 'bg-cedhi-light text-gray-700 border-gray-300'; ?>">
                                    <option value="activo" class="text-cedhi-accent"
                                        <?php echo $estado_actual === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" class="text-gray-700"
                                        <?php echo $estado_actual === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-600">
                    Mostrando <?php echo count($usuarios_registrados); ?> de <?php echo $total_usuarios; ?> usuarios
                </div>

                <div class="flex items-center space-x-2">
                    <button onclick="irAPagina(1)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                        class="pagination-btn px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-cedhi-accent hover:text-white hover:border-cedhi-accent transition">
                        <i class="fas fa-angle-double-left"></i>
                    </button>

                    <button onclick="irAPagina(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>
                        class="pagination-btn px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-cedhi-accent hover:text-white hover:border-cedhi-accent transition">
                        <i class="fas fa-angle-left"></i>
                    </button>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                    <button onclick="irAPagina(<?php echo $i; ?>)"
                        class="pagination-btn px-4 py-2 rounded-lg border transition <?php echo $i === $page ? 'bg-cedhi-accent text-white border-cedhi-accent' : 'bg-white border-gray-300 hover:bg-cedhi-accent hover:text-white hover:border-cedhi-accent'; ?>">
                        <?php echo $i; ?>
                    </button>
                    <?php endfor; ?>

                    <button onclick="irAPagina(<?php echo $page + 1; ?>)"
                        <?php echo $page >= $total_pages ? 'disabled' : ''; ?>
                        class="pagination-btn px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-cedhi-accent hover:text-white hover:border-cedhi-accent transition">
                        <i class="fas fa-angle-right"></i>
                    </button>

                    <button onclick="irAPagina(<?php echo $total_pages; ?>)"
                        <?php echo $page >= $total_pages ? 'disabled' : ''; ?>
                        class="pagination-btn px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-cedhi-accent hover:text-white hover:border-cedhi-accent transition">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    let debounceTimer;
    let isSearching = false;

    function buscarConDebounce() {
        clearTimeout(debounceTimer);
        const searchInput = document.getElementById('searchInput');

        debounceTimer = setTimeout(() => {
            const searchValue = searchInput.value;
            const currentRole = '<?php echo $current_role_filter; ?>';

            const cursorPosition = searchInput.selectionStart;

            buscarUsuarios(currentRole, searchValue, 1, () => {
                searchInput.focus();
                searchInput.setSelectionRange(cursorPosition, cursorPosition);
            });
        }, 500);
    }

    function buscarUsuarios(role, search, page, callback) {
        if (isSearching) return;
        isSearching = true;

        // Mostrar indicador de carga
        const loader = document.getElementById('searchLoader');
        loader.classList.remove('hidden');

        const tbody = document.querySelector('tbody');
        const originalContent = tbody.innerHTML;

        const url = `?role=${encodeURIComponent(role)}&search=${encodeURIComponent(search)}&page=${page}&ajax=1`;

        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTbody = doc.querySelector('tbody');
                const newPagination = doc.querySelector('.mt-6.flex');
                const newStats = doc.querySelector('.text-sm.text-gray-600');

                if (newTbody) {
                    tbody.innerHTML = newTbody.innerHTML;
                }

                const paginationContainer = document.querySelector('.mt-6.flex');
                if (paginationContainer && newPagination) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }

                const statsContainer = document.querySelector('.text-sm.text-gray-600');
                if (statsContainer && newStats) {
                    statsContainer.innerHTML = newStats.innerHTML;
                }

                const newUrl = `?role=${role}&search=${encodeURIComponent(search)}&page=${page}`;
                window.history.pushState({}, '', newUrl);

                loader.classList.add('hidden');
                isSearching = false;
                if (callback) callback();
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                tbody.innerHTML = originalContent;
                loader.classList.add('hidden');
                isSearching = false;
                if (callback) callback();
            });
    }

    function cambiarRol(role) {
        const searchValue = document.getElementById('searchInput').value;
        window.location.href = `?role=${encodeURIComponent(role)}&search=${encodeURIComponent(searchValue)}&page=1`;
    }

    function irAPagina(pagina) {
        const currentRole = '<?php echo $current_role_filter; ?>';
        const searchValue = document.getElementById('searchInput').value;
        buscarUsuarios(currentRole, searchValue, pagina);
    }

    function actualizarCheckboxModulos(userId) {
        const row = event.target.closest('tr');
        const checkboxes = row.querySelectorAll('input[type="checkbox"]');
        const seleccionados = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `accion=actualizar_modulos&user_id=${encodeURIComponent(userId)}&module_ids=${encodeURIComponent(seleccionados.join(','))}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('Módulos actualizados correctamente', 'success');
                } else {
                    mostrarToast('Error al actualizar: ' + (data.error || 'Error desconocido'), 'error');
                }
            })
            .catch(err => {
                mostrarToast('Error de conexión: ' + err.message, 'error');
            });
    }

    function actualizarEstadoUsuario(userId, nuevoEstado) {
        fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `accion=actualizar_estado&user_id=${encodeURIComponent(userId)}&estado=${encodeURIComponent(nuevoEstado)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('Estado actualizado correctamente', 'success');
                } else {
                    mostrarToast('Error al actualizar estado: ' + (data.error || 'Error desconocido'), 'error');
                }
            })
            .catch(err => {
                mostrarToast('Error de conexión: ' + err.message, 'error');
            });
        const select = document.querySelector(`select[data-user-id="${userId}"]`);

        if (select) {
            if (nuevoEstado === "activo") {
                select.className =
                    "px-3 py-1 rounded-lg text-sm font-medium border bg-green-100 text-green-700 border-green-300";
            } else {
                select.className =
                    "px-3 py-1 rounded-lg text-sm font-medium border bg-red-100 text-red-700 border-red-300";
            }
        }
    }

    function cambiarRolUsuario(userId, nuevoRol) {
        if (!confirm('¿Estás seguro de cambiar el rol de este usuario? Esto puede afectar sus permisos.')) {
            location.reload();
            return;
        }

        fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `accion=cambiar_rol&user_id=${encodeURIComponent(userId)}&nuevo_rol=${encodeURIComponent(nuevoRol)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('Rol actualizado correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast('Error al actualizar rol: ' + (data.error || 'Error desconocido'), 'error');
                    location.reload();
                }
            })
            .catch(err => {
                mostrarToast('Error de conexión: ' + err.message, 'error');
            });
    }

    function mostrarToast(mensaje, tipo) {
        const colores = tipo === 'success' ?
            'bg-green-500 text-white' :
            'bg-red-500 text-white';
        const toast = document.createElement('div');
        toast.className =
            `${colores} fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-xl transition-all duration-500 opacity-0 z-50 flex items-center space-x-2`;

        const icon = tipo === 'success' ?
            '<i class="fas fa-check-circle"></i>' :
            '<i class="fas fa-exclamation-circle"></i>';

        toast.innerHTML = `${icon}<span>${mensaje}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.style.opacity = 1, 50);
        setTimeout(() => {
            toast.style.opacity = 0;
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
    </script>
</body>

</html>