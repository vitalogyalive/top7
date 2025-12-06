<?php
namespace Top7\Display;

require_once __DIR__ . '/Navigation.php';

class PageRenderer {

    /**
     * Render the HTML header
     */
    public static function header(string $pageClass = '', string $title = 'Top7'): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr" class="<?= htmlspecialchars($pageClass) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Top7 - Jeu de pronostics rugby TOP 14">
            <title><?= htmlspecialchars($title) ?></title>

            <!-- Tailwind CSS -->
            <link rel="stylesheet" href="styles/output.css">

            <!-- Favicons -->
            <link rel="icon" type="image/png" sizes="32x32" href="favicons/favicon-32x32.png">

            <!-- Alpine.js -->
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

            <!-- Legacy CSS (for Phase 1) -->
            <link rel="stylesheet" href="common.css">
        </head>
        <body class="<?= htmlspecialchars($pageClass) ?>">
        <?php
    }

    /**
     * Render modern header with navigation
     */
    public static function modernHeader(string $title = 'Top7', array $session = [], string $currentPage = ''): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr" class="h-full">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="Top7 - Jeu de pronostics rugby TOP 14">
            <title><?= htmlspecialchars($title) ?></title>

            <!-- Tailwind CSS -->
            <link rel="stylesheet" href="styles/output.css">

            <!-- Favicons -->
            <link rel="icon" type="image/png" sizes="32x32" href="favicons/favicon-32x32.png">

            <!-- Alpine.js -->
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        </head>
        <body class="h-full bg-gray-50">
        <?php
        // Render navigation if session is available
        if (!empty($session)) {
            Navigation::render($session, $currentPage);
        }
    }

    /**
     * Start main content wrapper
     */
    public static function startMain(): void {
        ?>
        <main class="container py-6">
        <?php
    }

    /**
     * End main content wrapper
     */
    public static function endMain(): void {
        ?>
        </main>
        <?php
    }

    /**
     * Render a card container
     */
    public static function card(string $title = '', string $class = ''): void {
        ?>
        <div class="card <?= htmlspecialchars($class) ?>">
            <?php if ($title): ?>
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?= htmlspecialchars($title) ?></h2>
            <?php endif; ?>
        <?php
    }

    /**
     * End card container
     */
    public static function endCard(): void {
        ?>
        </div>
        <?php
    }

    /**
     * Render a section with title
     */
    public static function section(string $title, string $subtitle = ''): void {
        ?>
        <section class="mb-8">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($title) ?></h2>
                <?php if ($subtitle): ?>
                <p class="text-gray-500"><?= htmlspecialchars($subtitle) ?></p>
                <?php endif; ?>
            </div>
        <?php
    }

    /**
     * End section
     */
    public static function endSection(): void {
        ?>
        </section>
        <?php
    }

    /**
     * Render a grid container
     */
    public static function grid(int $cols = 3): void {
        $gridClass = match($cols) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-1 md:grid-cols-2',
            3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
            4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
            default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
        };
        ?>
        <div class="grid <?= $gridClass ?> gap-6">
        <?php
    }

    /**
     * End grid container
     */
    public static function endGrid(): void {
        ?>
        </div>
        <?php
    }

    /**
     * Render a stat card
     */
    public static function statCard(string $label, string $value, string $icon = '', string $trend = ''): void {
        ?>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($label) ?></p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?= htmlspecialchars($value) ?></p>
                    <?php if ($trend): ?>
                    <p class="text-sm text-green-600 mt-1"><?= htmlspecialchars($trend) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($icon): ?>
                <div class="text-3xl"><?= $icon ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render an alert message
     */
    public static function alert(string $message, string $type = 'info'): void {
        $config = match($type) {
            'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'icon' => '✓'],
            'error' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'icon' => '✕'],
            'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'icon' => '⚠'],
            default => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'ℹ']
        };
        ?>
        <div class="<?= $config['bg'] ?> border <?= $config['border'] ?> <?= $config['text'] ?> rounded-lg p-4 mb-4 flex items-center">
            <span class="mr-3"><?= $config['icon'] ?></span>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
        <?php
    }

    /**
     * Render modern footer with version info
     */
    public static function modernFooter(): void {
        ?>
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="container py-6">
                <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span>Top7 - Jeu de pronostics rugby</span>
                        <?php if (class_exists('\Top7\Utils\Logger')): ?>
                        <span><?php \Top7\Utils\Logger::printVersion(); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="mailto:contact@top7.fr" class="hover:text-primary-600">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
        </body>
        </html>
        <?php
    }

    public static function footer(): void {
        ?>
        </body>
        </html>
        <?php
    }
}
