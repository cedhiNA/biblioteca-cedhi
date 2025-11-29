<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Biblioteca CEDHI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-screen w-screen bg-cover bg-center flex items-center justify-center relative" style="
      background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1600&q=80');
    ">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm z-0"></div>
    <div class="relative z-10 bg-white bg-opacity-90 shadow-xl rounded-2xl p-6 sm:p-8 w-full max-w-xs sm:max-w-md">

        <?php if (isset($_SESSION['login_error'])): ?>
        <div id="toast"
            class="fixed top-5 left-1/2 transform -translate-x-1/2 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg flex items-center gap-2 opacity-0 transition-opacity duration-500 z-50">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span><?php echo $_SESSION['login_error']; ?></span>
            <?php unset($_SESSION['login_error']); ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 4000);
        });
        </script>
        <?php endif; ?>


        <h1 class="text-2xl sm:text-3xl font-bold text-center text-blue-900 mb-5">
            BIBLIOTECA CEDHI
        </h1>

        <div class="flex justify-center mb-4">
            <img src="img/logo_cedhi.png" alt="Logo" class="h-24 sm:h-32" />
        </div>
        <p class="text-center text-gray-700 mb-7 text-sm sm:text-base">
            Accede a la Biblioteca Virtual CEDHI para explorar nuestros recursos
            digitales
        </p>
        <div class="flex items-center my-6">
            <hr class="flex-grow border-gray-300" />
            <span class="px-2 text-gray-400 text-sm">Accede con</span>
            <hr class="flex-grow border-gray-300" />
        </div>
        <a href="<?php echo $google_client->createAuthUrl(); ?>">
            <button
                class="w-full flex items-center justify-center gap-1 sm:gap-2 border border-gray-300 py-2 sm:py-3 rounded-lg shadow-md transition transform hover:-translate-y-1 hover:shadow-xl font-medium text-sm sm:text-base">

                <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" class="h-5 w-5 mr-2" />
                Iniciar sesi贸n con Google
            </button>
        </a>
        <p class="text-center text-xs text-gray-500 mt-6">
            Solo se permite el acceso con cuentas institucionales.<br />
            Si eres administrador, ingresa con tu correo designado.
        </p>
    </div>
    </div>
    </div>

</body>

</html>