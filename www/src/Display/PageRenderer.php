<?php
namespace Top7\Display;

class PageRenderer {

    public static function header(string $pageClass = '', string $title = 'Top7', string $bodyAttributes = ''): void {
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
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
            
            <!-- Legacy JS -->
            <script src="common.js"></script>
        </head>
        <body class="<?= htmlspecialchars($pageClass) ?>" <?= $bodyAttributes ?>>
        <?php
    }

    public static function footer(): void {
        ?>
        </body>
        </html>
        <?php
    }
}
