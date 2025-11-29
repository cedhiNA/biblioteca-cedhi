<?php
require_once __DIR__ . '/../../app/paths.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_url = '/BibliotecaCEDHI';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca CEDHI</title>
    <link rel="icon" type="image/png" href="../../img/logo_cedhi_claro.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    cedhi: {
                        primary: "#2C3E50", // Gris Oscuro Azulado (Base)
                        secondary: "#34495E", // Tono m谩s claro para hover
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
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    </style>
</head>

<body>
    <header
        class="flex justify-between items-center p-4 sm:px-8 bg-cedhi-primary text-white shadow-xl sticky top-0 z-10">
        <div class="flex items-center space-x-2 sm:space-x-4">
            <img src="<?php echo url('img/logo_cedhi_claro.png') ?>" alt="logo"
                class="h-12 sm:h-14 w-auto drop-shadow-sm" />
            <h1 class="text-lg sm:text-2xl font-extrabold tracking-wide">
                Biblioteca <span class="text-cedhi-accent">CEDHI</span>
            </h1>
        </div>
        <div class="flex items-center space-x-2 sm:space-x-4">
            <img src="<?php echo url('img/default-avatar.jpg') ?>" class="user-avatar mr-2 shadow-inner" alt="Avatar">

            <div class="text-right hidden sm:block">
                <span class="block text-sm text-cedhi-accent">Hola,</span>
                <span class="block font-semibold text-lg">
                    <?php echo htmlspecialchars($_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']); ?>
                </span>
                <?php if ($_SESSION['role'] != 'general_user'):?>
                <span class="block text-sm text-cedhi-accent">
                    (<?php echo ucfirst($_SESSION['role']); ?>)
                </span>
                <?php endif; ?>
            </div>
            <button
                class="flex items-center space-x-1 sm:space-x-2 bg-cedhi-secondary border-cedhi-secondary text-white py-2 px-3 sm:py-2 sm:px-5 rounded-lg hover:bg-cedhi-accent transition-colors duration-200 outline-none"
                onclick="window.location.href='../../logout.php'">
                <i class="fa-solid fa-right-from-bracket text-sm sm:text-base"></i>
                <span class="hidden sm:block">Cerrar sesi贸n</span>
            </button>
        </div>
    </header>
</body>

</html>