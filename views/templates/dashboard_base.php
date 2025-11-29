<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/paths.php';
require_once __DIR__ . '/../../app/tokenController.php';
require_once __DIR__ . '/../../app/moduleAccess.php';

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

$SALA_ID = 4;
$PLANES_ID = 2;

$accesoPlanes = canAccessModule($userId, $PLANES_ID, $userRole);
$accesoSalaLectura = canAccessModule($userId, $SALA_ID, $userRole);

$tokenPlanes = ($accesoPlanes == "access") ? generateToken() : null;
$tokenSala = ($accesoSalaLectura == "access") ? generateToken() : null;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CEDHI</title>
    <link rel="icon" type="image/png" href="../../img/logo_cedhi_claro.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
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
        margin-bottom: 2rem;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .module-card {
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .module-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
    </style>
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
                    }
                }
            }
        }
    }
    </script>
</head>

<body class="bg-cedhi-light min-h-screen">
    <?php include __DIR__ . '/header.php'; ?>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

        <div class="welcome-header mb-8 rounded-xl shadow-xl">
            <h1 class="text-3xl font-extrabold mb-1">
                ¡Hola,
                <?php echo htmlspecialchars($_SESSION['user_first_name']); ?>!
            </h1>
            <p class="text-xl font-light opacity-90">
                Selecciona un módulo para acceder a los recursos de la Biblioteca.
            </p>
        </div>

        <div class="grid gap-6 justify-center" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <div
                class="module-card bg-white rounded-xl p-8 flex flex-col items-center text-center border-b-4 border-cedhi-accent">
                <div
                    class="h-16 w-16 flex items-center justify-center rounded-full bg-cedhi-light text-cedhi-accent mb-4 text-3xl shadow-md">
                    <i class="fa-solid fa-book"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Biblioteca Virtual</h2>
                <p class="text-base text-gray-500 mb-6">
                    Accede a libros digitales y recursos en línea.
                </p>
                <a href="https://elibro.net/es/lc/cedhinuevaarequipa/login_usuario/"
                    class="w-full py-3 px-4 rounded-lg font-semibold text-white bg-cedhi-primary hover:bg-cedhi-secondary transition shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-book-open-reader mr-2"></i> Entrar
                </a>
            </div>

            <?php if($accesoPlanes == "access"): ?>
            <div
                class="module-card bg-white rounded-xl p-8 flex flex-col items-center text-center border-b-4 border-cedhi-accent">
                <div
                    class="h-16 w-16 flex items-center justify-center rounded-full bg-cedhi-light text-cedhi-accent mb-4 text-3xl shadow-md">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Planes de Negocio</h2>
                <p class="text-base text-gray-500 mb-6">
                    Consulta repositorios de planes y proyectos de la institución.
                </p>
                <button id="IrPlanesNegocio"
                    class="w-full py-3 px-4 rounded-lg font-semibold text-white bg-cedhi-primary hover:bg-cedhi-secondary transition shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-folder-open mr-2"></i> Entrar
                </button>
            </div>
            <?php endif; ?>

            <div
                class="module-card bg-white rounded-xl p-8 flex flex-col items-center text-center border-b-4 border-cedhi-accent">
                <div
                    class="h-16 w-16 flex items-center justify-center rounded-full bg-cedhi-light text-cedhi-accent mb-4 text-3xl shadow-md">
                    <i class="fa-solid fa-link"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Repositorios Externos</h2>
                <p class="text-base text-gray-500 mb-6">
                    Accede a enlaces directos a recursos gratuitos.
                </p>
                <a href="<?php echo url('pages/repositorios.php') ?>"
                    class="w-full py-3 px-4 rounded-lg font-semibold text-white bg-cedhi-primary hover:bg-cedhi-secondary transition shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-external-link-alt mr-2"></i> Entrar
                </a>
            </div>

            <?php if ($accesoSalaLectura === 'access'): ?>
            <div
                class="module-card bg-white rounded-xl p-8 flex flex-col items-center text-center border-b-4 border-cedhi-accent">
                <div
                    class="h-16 w-16 flex items-center justify-center rounded-full bg-cedhi-light text-cedhi-accent mb-4 text-3xl shadow-md">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Sala de Lectura</h2>
                <p class="text-base text-gray-500 mb-6">
                    Gestión y control de préstamos de libros físicos.
                </p>
                <button id="IrSalaLectura"
                    class="w-full py-3 px-4 rounded-lg font-semibold text-white bg-cedhi-primary hover:bg-cedhi-secondary transition shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-clipboard-list mr-2"></i> Entrar
                </button>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tokenPlanes = '<?php echo $tokenPlanes; ?>';
    const tokenSala = '<?php echo $tokenSala; ?>';
    console.log("token planes: ", tokenPlanes);
    console.log("token sala: ", tokenSala);
    const planesBtn = document.getElementById('IrPlanesNegocio');
    if (planesBtn) {
        planesBtn.addEventListener('click', () => {
            window.location.href = 'https://repositorio-planes.cedhinuevaarequipa.edu.pe?token=' +
                tokenPlanes;
        });
    }
    const salaBtn = document.getElementById('IrSalaLectura');
    if (salaBtn) {
        salaBtn.addEventListener('click', () => {
            window.location.href = 'https://salalectura.cedhinuevaarequipa.edu.pe/token-login?token=' +
                tokenSala;
        });
    }
});
</script>

</html>