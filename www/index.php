<?php
require_once 'common.inc';
require_once 'src/Display/PageRenderer.php';

use Top7\Auth\SessionManager;
use Top7\Display\PageRenderer;
use Top7\Security\CsrfToken;

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
$token = CsrfToken::generate();
$_SESSION['token'] = $token; // Keep legacy token for now

PageRenderer::header('login', 'Top7 - Connexion');
?>

<!-- Background with rugby image -->
<div class="min-h-screen bg-cover bg-center bg-no-repeat flex items-center justify-center p-4"
     style="background-image: url('top7_login.jpeg')">

    <!-- Login Card - Responsive -->
    <div class="w-full max-w-md bg-gray-800 bg-opacity-90 rounded-lg shadow-2xl p-8"
         x-data="{ error: false }">

        <!-- Logo/Title -->
        <div class="text-center mb-8">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                TOP7
            </h1>
            <p class="text-2xl text-white">
                Saison <?= isset($top7_season['title']) ? $top7_season['title'] : '2025-2026' ?>
            </p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="space-y-6">
            <?= CsrfToken::field() ?>
            <input type="hidden" name="token" value="<?= $token ?>">

            <!-- Username -->
            <div>
                <label for="login" class="block text-white text-sm font-medium mb-2">
                    Nom d'utilisateur
                </label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    required
                    class="w-full px-4 py-3 rounded bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-top7-orange
                           transition duration-200"
                    placeholder="Entrez votre nom"
                >
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-white text-sm font-medium mb-2">
                    Mot de passe
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-3 rounded bg-white text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-top7-orange
                           transition duration-200"
                    placeholder="Entrez votre mot de passe"
                >
            </div>

            <!-- Links -->
            <div class="flex items-center justify-between text-white text-sm">
                <a href="password.php" class="hover:text-top7-orange transition">
                    Mot de passe oublié ?
                </a>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-top7-orange hover:bg-orange-600 text-white font-bold
                       py-3 rounded transition duration-200 transform hover:scale-105">
                SE CONNECTER
            </button>
        </form>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-300 text-sm">
                Pas encore de compte ?
                <a href="register.php" class="text-top7-orange hover:text-white font-bold transition ml-1">
                    S'inscrire
                </a>
            </p>
        </div>
        
        <div class="mt-8 text-center text-gray-400 text-xs">
            <?php \Top7\Utils\Logger::printVersion(); ?>
        </div>
    </div>
</div>

<?php
PageRenderer::footer();
?>