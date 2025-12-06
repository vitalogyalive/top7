<?php
namespace Top7\Display;

/**
 * Modern responsive navigation component using Tailwind CSS and Alpine.js
 */
class Navigation {

    /**
     * Render the main navigation bar
     */
    public static function render(array $session, string $currentPage = ''): void {
        $pseudo = $session['pseudo'] ?? 'Joueur';
        $mode = $session['mode'] ?? 0;
        $player = $session['player'] ?? 0;
        $status = $session['status'] ?? 0;
        $top7team = $session['top7team'] ?? 0;
        $season = $session['season'] ?? 0;

        // Get season title
        $seasonTitle = '';
        if (function_exists('get_top7_season_by_id')) {
            $seasonData = get_top7_season_by_id($season);
            $seasonTitle = $seasonData['title'] ?? '';
        }

        // Get team name
        $teamName = '';
        if ($top7team > 0 && function_exists('get_top7team_name')) {
            $teamName = get_top7team_name($top7team);
        }

        $isAdmin = ($mode == c_admin);
        $canPlay = ($status == c_can_play && $mode == c_player && $player > 0);
        ?>

        <!-- Navigation Bar -->
        <nav class="navbar sticky top-0 z-50" x-data="{ mobileMenuOpen: false, userMenuOpen: false }">
            <div class="container">
                <div class="flex items-center justify-between h-16">

                    <!-- Logo and Season -->
                    <div class="flex items-center space-x-4">
                        <a href="display.php" class="flex items-center space-x-2">
                            <img src="<?= defined('c_logo_file') ? c_logo_file : 'logo.png' ?>" alt="Top7" class="h-10 w-auto">
                            <span class="text-xl font-bold text-primary-600 hidden sm:block">TOP7</span>
                        </a>
                        <?php if ($seasonTitle): ?>
                        <span class="hidden md:inline-block badge badge-primary">
                            <?= htmlspecialchars($seasonTitle) ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center space-x-1">
                        <?php self::renderNavItems($session, $currentPage, $canPlay, $isAdmin); ?>
                    </div>

                    <!-- User Menu & Mobile Toggle -->
                    <div class="flex items-center space-x-4">

                        <!-- User Dropdown (Desktop) -->
                        <div class="hidden md:block relative">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-semibold text-sm">
                                        <?= strtoupper(substr($pseudo, 0, 1)) ?>
                                    </span>
                                </div>
                                <span class="text-gray-700 font-medium"><?= htmlspecialchars($pseudo) ?></span>
                                <?php if ($teamName): ?>
                                <span class="text-xs text-gray-500">(<?= htmlspecialchars($teamName) ?>)</span>
                                <?php endif; ?>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- User Dropdown Menu -->
                            <div
                                x-show="userMenuOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
                                style="display: none;"
                            >
                                <a href="params.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Paramètres
                                </a>
                                <?php if ($isAdmin): ?>
                                <a href="admin.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Administration
                                </a>
                                <?php endif; ?>
                                <hr class="my-1 border-gray-200">
                                <a href="logout.php" class="flex items-center px-4 py-2 text-danger-500 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Déconnexion
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <svg x-show="!mobileMenuOpen" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            <svg x-show="mobileMenuOpen" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div
                    x-show="mobileMenuOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="lg:hidden border-t border-gray-200 py-4"
                    style="display: none;"
                >
                    <div class="flex flex-col space-y-2">
                        <?php self::renderMobileNavItems($session, $currentPage, $canPlay, $isAdmin); ?>

                        <!-- Mobile User Section -->
                        <hr class="my-2 border-gray-200">
                        <div class="px-3 py-2 text-sm text-gray-500">
                            Connecté en tant que <strong><?= htmlspecialchars($pseudo) ?></strong>
                            <?php if ($teamName): ?>
                            <br><span class="text-xs"><?= htmlspecialchars($teamName) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="params.php" class="flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Paramètres
                        </a>
                        <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg text-danger-500 hover:bg-red-50">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <?php
    }

    /**
     * Render desktop navigation items
     */
    private static function renderNavItems(array $session, string $currentPage, bool $canPlay, bool $isAdmin): void {
        $navItems = self::getNavItems($session, $canPlay, $isAdmin);

        foreach ($navItems as $item) {
            $isActive = ($currentPage === $item['id']);
            $activeClass = $isActive ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100';
            ?>
            <form action="<?= htmlspecialchars($item['action']) ?>" method="post" class="inline">
                <?php if (isset($item['hidden'])): ?>
                    <?php foreach ($item['hidden'] as $name => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <button type="submit" class="px-3 py-2 rounded-lg font-medium text-sm transition-colors <?= $activeClass ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </button>
            </form>
            <?php
        }
    }

    /**
     * Render mobile navigation items
     */
    private static function renderMobileNavItems(array $session, string $currentPage, bool $canPlay, bool $isAdmin): void {
        $navItems = self::getNavItems($session, $canPlay, $isAdmin);

        foreach ($navItems as $item) {
            $isActive = ($currentPage === $item['id']);
            $activeClass = $isActive ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:bg-gray-100';
            ?>
            <form action="<?= htmlspecialchars($item['action']) ?>" method="post">
                <?php if (isset($item['hidden'])): ?>
                    <?php foreach ($item['hidden'] as $name => $value): ?>
                    <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg font-medium transition-colors <?= $activeClass ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </button>
            </form>
            <?php
        }
    }

    /**
     * Get navigation items based on user permissions
     */
    private static function getNavItems(array $session, bool $canPlay, bool $isAdmin): array {
        $items = [];

        // Play button (only if can play)
        if ($canPlay) {
            $items[] = [
                'id' => 'prono',
                'label' => 'Pronostics',
                'action' => 'display.php',
                'hidden' => [
                    'game' => defined('c_enable') ? c_enable : 1,
                    'display' => defined('c_top7_player') ? c_top7_player : 0
                ]
            ];
        }

        // Admin calendar (only if admin)
        if ($isAdmin) {
            $items[] = [
                'id' => 'calendar',
                'label' => 'Calendrier',
                'action' => 'calendar.php',
                'hidden' => ['display' => defined('c_top14_match') ? c_top14_match : 0]
            ];
        }

        // Results
        $items[] = [
            'id' => 'results',
            'label' => 'Résultats',
            'action' => $isAdmin ? 'update_day.php' : 'display.php',
            'hidden' => [
                'game' => defined('c_disable') ? c_disable : 0,
                'display' => defined('c_top7') ? c_top7 : 0
            ]
        ];

        // Rankings
        $items[] = [
            'id' => 'rank7',
            'label' => 'Classement',
            'action' => 'rank7.php',
            'hidden' => ['display' => defined('c_rank7') ? c_rank7 : 0]
        ];

        // Stats
        $items[] = [
            'id' => 'stats',
            'label' => 'Statistiques',
            'action' => 'stats.php',
            'hidden' => []
        ];

        // Records
        $items[] = [
            'id' => 'records',
            'label' => 'Records',
            'action' => 'records.php',
            'hidden' => []
        ];

        // Agenda
        $items[] = [
            'id' => 'agenda',
            'label' => 'Agenda',
            'action' => 'agenda.php',
            'hidden' => []
        ];

        return $items;
    }

    /**
     * Render breadcrumbs
     */
    public static function breadcrumbs(array $items): void {
        ?>
        <nav class="container py-3">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="display.php" class="text-gray-500 hover:text-primary-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </a>
                </li>
                <?php foreach ($items as $index => $item): ?>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <?php if (isset($item['url'])): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="text-gray-500 hover:text-primary-600">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                    <?php else: ?>
                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($item['label']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }

    /**
     * Render page header with title and optional subtitle
     */
    public static function pageHeader(string $title, string $subtitle = '', string $icon = ''): void {
        ?>
        <div class="page-header">
            <div class="container py-6">
                <div class="flex items-center space-x-4">
                    <?php if ($icon): ?>
                    <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                        <span class="text-2xl"><?= $icon ?></span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($title) ?></h1>
                        <?php if ($subtitle): ?>
                        <p class="text-gray-500 mt-1"><?= htmlspecialchars($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render status bar (game status, deadline info)
     */
    public static function statusBar(array $session): void {
        $game = $session['game'] ?? 0;
        $deadline = $session['deadline'] ?? '';
        $status = $session['status'] ?? 0;

        $statusConfig = self::getStatusConfig($game, $status);
        if (!$statusConfig) return;
        ?>
        <div class="container mb-4">
            <div class="rounded-lg p-4 flex items-center justify-between <?= $statusConfig['bgClass'] ?>">
                <div class="flex items-center space-x-3">
                    <span class="text-xl"><?= $statusConfig['icon'] ?></span>
                    <div>
                        <p class="font-medium <?= $statusConfig['textClass'] ?>"><?= $statusConfig['message'] ?></p>
                        <?php if ($deadline): ?>
                        <p class="text-sm <?= $statusConfig['subtextClass'] ?>">Deadline: <?= htmlspecialchars($deadline) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($statusConfig['showAction'] ?? false): ?>
                <form action="display.php" method="post">
                    <input type="hidden" name="game" value="<?= defined('c_enable') ? c_enable : 1 ?>">
                    <input type="hidden" name="display" value="<?= defined('c_top7_player') ? c_top7_player : 0 ?>">
                    <button type="submit" class="btn btn-primary">
                        Faire mes pronostics
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get status configuration based on game state
     */
    private static function getStatusConfig(int $game, int $status): ?array {
        if ($game == (defined('c_validated') ? c_validated : 0)) {
            return [
                'icon' => '🎮',
                'message' => 'Partie en cours',
                'bgClass' => 'bg-green-50 border border-green-200',
                'textClass' => 'text-green-800',
                'subtextClass' => 'text-green-600',
                'showAction' => false
            ];
        }

        if ($status == (defined('c_can_play') ? c_can_play : 0)) {
            return [
                'icon' => '⏰',
                'message' => 'Les pronostics sont ouverts !',
                'bgClass' => 'bg-primary-50 border border-primary-200',
                'textClass' => 'text-primary-800',
                'subtextClass' => 'text-primary-600',
                'showAction' => true
            ];
        }

        return null;
    }
}
