# Top7 Hybrid Modernization Plan

**Project**: Top7 Rugby Prediction Game
**Strategy**: Hybrid Approach - Keep PHP Backend + Modern Frontend
**Timeline**: 6 months (phased rollout)
**Last Updated**: 2025-11-09

---

## Executive Summary

This plan outlines a phased modernization of the Top7 application using a hybrid approach:
- **Keep existing PHP business logic** (battle-tested, 11+ years)
- **Modernize security** (password hashing, CSRF protection)
- **Refactor architecture** (split 9294-line common.inc into modules)
- **Build modern responsive UI** (Vue.js 3 + Tailwind CSS)
- **Create REST API layer** (enable future mobile apps)
- **Add real-time features** (live score updates, PWA)

**Risk Level**: Low-Medium (incremental migration, parallel deployment)
**Effort**: 250-350 hours across 6 months
**ROI**: High (better UX, mobile support, maintainability)

---

## Current State Analysis

### Technical Debt
```
✗ PHP 7.1 (should be 8.3)
✗ MD5 password hashing (vulnerable)
✗ 9294-line common.inc (poor maintainability)
✗ No responsive design (mobile unusable)
✗ Page reloads for all interactions
✗ No API (cannot build mobile apps)
✗ Inline styles mixed with CSS
✗ Deprecated HTML (<center> tags)
```

### What Works Well
```
✓ Game logic is solid and tested
✓ Database schema is well-designed
✓ Docker development environment
✓ Active maintenance and updates
✓ Clear separation of pages
```

---

## Architecture Overview

### Current Architecture
```
┌─────────────────────────────────────┐
│  Browser (desktop only)             │
└─────────────────┬───────────────────┘
                  │ HTTP (page reloads)
┌─────────────────▼───────────────────┐
│  Apache + PHP 7.1                   │
│  ┌─────────────────────────────┐   │
│  │ common.inc (9294 lines)     │   │
│  │ - DB + Auth + Logic + UI    │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ 40+ PHP pages               │   │
│  └─────────────────────────────┘   │
└─────────────────┬───────────────────┘
                  │
┌─────────────────▼───────────────────┐
│  MySQL 5.6                          │
└─────────────────────────────────────┘
```

### Target Architecture (Phase 2 Complete)
```
┌──────────────────┐  ┌──────────────────┐
│ Browser Desktop  │  │ Mobile / PWA     │
└────────┬─────────┘  └────────┬─────────┘
         │                     │
         │ HTTP/HTTPS          │
         │ JSON API            │
         │                     │
┌────────▼─────────────────────▼─────────┐
│  Vue.js 3 SPA (Vite)                   │
│  - Tailwind CSS (responsive)           │
│  - Alpine.js (lightweight reactivity)  │
│  - Pinia (state management)            │
└────────┬───────────────────────────────┘
         │ REST API (JSON)
┌────────▼───────────────────────────────┐
│  PHP 8.3 API Layer                     │
│  ┌─────────────────────────────────┐  │
│  │ /api/                           │  │
│  │  - auth.php                     │  │
│  │  - matches.php                  │  │
│  │  - predictions.php              │  │
│  │  - rankings.php                 │  │
│  └─────────────────────────────────┘  │
│  ┌─────────────────────────────────┐  │
│  │ /src/                           │  │
│  │  - Database/                    │  │
│  │  - Auth/                        │  │
│  │  - Game/                        │  │
│  │  - Display/                     │  │
│  └─────────────────────────────────┘  │
└────────┬───────────────────────────────┘
         │
┌────────▼───────────────────────────────┐
│  MySQL 8.0                             │
└────────────────────────────────────────┘
```

---

## Phase 1: Quick Wins (Weeks 1-4)

**Goal**: Improve security, code quality, and basic responsiveness
**Effort**: 80-100 hours
**Risk**: Low (backward compatible changes)

### 1.1 Security Fixes (Week 1)

#### Task 1.1.1: Update Password Hashing
**Priority**: CRITICAL
**Effort**: 6-8 hours
**Files**: `common.inc`, `login.php`, `register.php`, `new_password.php`

**Current Code**:
```php
// common.inc - INSECURE
function check_password($login, $password) {
    $md5_password = md5($password);
    // ... query against md5_password
}
```

**New Code**:
```php
// src/Auth/PasswordService.php - NEW FILE
class PasswordService {

    public function hash(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
```

**Migration Strategy**:
```php
// Update database schema
ALTER TABLE player ADD COLUMN password_new VARCHAR(255) NULL;

// Migration script (run once)
function migrate_passwords() {
    $pdo = init_sql();
    $players = pdo_fetch($pdo, "SELECT id, password FROM player");

    foreach ($players as $player) {
        // Leave old md5 password in place temporarily
        // New password will be set on next login
    }
}

// Update login function to handle both
function check_password($login, $password) {
    $player = get_player_by_login($login);

    // Try new password first
    if (!empty($player['password_new'])) {
        if (password_verify($password, $player['password_new'])) {
            return true;
        }
    }

    // Fallback to old md5 (for migration period)
    if (md5($password) === $player['password']) {
        // Auto-migrate to new hash
        $new_hash = password_hash($password, PASSWORD_ARGON2ID);
        pdo_exec($pdo, "UPDATE player SET password_new = ? WHERE id = ?",
                 [$new_hash, $player['id']]);
        return true;
    }

    return false;
}
```

**Testing**:
- [ ] Test login with existing accounts (md5)
- [ ] Test new registration (argon2id)
- [ ] Test password reset flow
- [ ] Verify auto-migration on login
- [ ] After 1 season, remove old password column

---

#### Task 1.1.2: Add CSRF Protection
**Priority**: HIGH
**Effort**: 4-6 hours
**Files**: All forms (login, register, prono, team, etc.)

**Current Code**:
```php
// index.php - NO CSRF PROTECTION
$token = bin2hex(random_bytes(32));
$_SESSION['token'] = $token;
// Token is generated but not validated!
```

**New Code**:
```php
// src/Security/CsrfToken.php - NEW FILE
class CsrfToken {

    public static function generate(): string {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$token] = time();

        // Keep only last 5 tokens (for multi-tab support)
        if (count($_SESSION['csrf_tokens']) > 5) {
            array_shift($_SESSION['csrf_tokens']);
        }

        return $token;
    }

    public static function validate(string $token): bool {
        if (!isset($_SESSION['csrf_tokens'][$token])) {
            return false;
        }

        // Token expires after 2 hours
        if (time() - $_SESSION['csrf_tokens'][$token] > 7200) {
            unset($_SESSION['csrf_tokens'][$token]);
            return false;
        }

        // One-time use
        unset($_SESSION['csrf_tokens'][$token]);
        return true;
    }

    public static function field(): string {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' .
               htmlspecialchars($token) . '">';
    }
}

// Update all forms
function put_login_form($token) {
    echo '<form method="POST" action="login">';
    echo CsrfToken::field(); // Add this
    echo '<input type="text" name="login" ...>';
    // ...
}

// Update all form handlers
function handle_login() {
    if (!CsrfToken::validate($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    // ... existing login logic
}
```

**Testing**:
- [ ] Test valid form submission
- [ ] Test submission with missing token (should fail)
- [ ] Test submission with expired token (should fail)
- [ ] Test submission with reused token (should fail)
- [ ] Test multi-tab scenario

---

### 1.2 Code Refactoring (Week 2)

#### Task 1.2.1: Split common.inc into Modules
**Priority**: HIGH
**Effort**: 20-24 hours
**Risk**: Medium (requires careful testing)

**New Directory Structure**:
```
/www/
├── src/
│   ├── Database/
│   │   ├── Connection.php      (init_sql, pdo_*)
│   │   └── QueryBuilder.php    (optional, future)
│   ├── Auth/
│   │   ├── SessionManager.php  (check_session, session mgmt)
│   │   ├── PasswordService.php (password hashing)
│   │   └── UserService.php     (get_player, check_player)
│   ├── Game/
│   │   ├── MatchService.php    (get_matchs_*, update_calendar_matchs)
│   │   ├── PronoService.php    (get_prono_*, prediction logic)
│   │   ├── ScoreService.php    (get_score_*, scoring logic)
│   │   ├── RankingService.php  (get_selection_order, rankings)
│   │   └── CalendarService.php (date/day logic, deadlines)
│   ├── Display/
│   │   ├── TableRenderer.php   (display functions)
│   │   ├── FormRenderer.php    (form generation)
│   │   └── NavRenderer.php     (navigation, status)
│   ├── Stats/
│   │   ├── StatsService.php    (get_stats_*)
│   │   └── RecordsService.php  (get_records_*)
│   ├── Utils/
│   │   ├── Logger.php          (print_log, printr_log)
│   │   └── EmailService.php    (send_email)
│   └── bootstrap.php           (autoloader, init)
├── common.inc                  (deprecated, includes bootstrap)
└── [existing PHP pages]
```

**Migration Strategy**:

**Step 1**: Create bootstrap with autoloader
```php
// src/bootstrap.php
<?php

spl_autoload_register(function ($class) {
    $prefix = 'Top7\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
require_once __DIR__ . '/../conf/conf.php';

// Legacy support - import old constants
define('c_enable', 0);
define('c_closed', 1);
// ... all other constants
```

**Step 2**: Extract Database layer (example)
```php
// src/Database/Connection.php
<?php
namespace Top7\Database;

class Connection {
    private static ?PDO $pdo = null;

    public static function getInstance(): PDO {
        if (self::$pdo === null) {
            self::$pdo = self::connect();
        }
        return self::$pdo;
    }

    private static function connect(): PDO {
        $db_host = c_db_host;
        $db_name = c_db_name;
        $db_user = c_db_user;
        $db_pwd = c_db_pwd;

        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

        $pdo = new PDO($dsn, $db_user, $db_pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;
    }

    // Keep legacy functions for backward compatibility
    public static function fetch(string $sql, array $params = []): array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function exec(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

// Legacy wrapper functions in common.inc
function init_sql(): PDO {
    return Top7\Database\Connection::getInstance();
}

function pdo_fetch(PDO $pdo, string $sql, array $params = []): array {
    return Top7\Database\Connection::fetch($sql, $params);
}
```

**Step 3**: Extract Auth layer (example)
```php
// src/Auth/SessionManager.php
<?php
namespace Top7\Auth;

use Top7\Database\Connection;

class SessionManager {

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        self::start();

        if (!isset($_SESSION['player_id']) ||
            !isset($_SESSION['last_activity'])) {
            self::redirectToLogin();
            return false;
        }

        // Session timeout (30 minutes)
        if (time() - $_SESSION['last_activity'] > 1800) {
            self::destroy();
            self::redirectToLogin();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function login(int $playerId, array $playerData): void {
        self::start();
        session_regenerate_id(true);

        $_SESSION['player_id'] = $playerId;
        $_SESSION['login'] = $playerData['login'];
        $_SESSION['mode'] = $playerData['mode'];
        $_SESSION['last_activity'] = time();
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    private static function redirectToLogin(): void {
        header('Location: /');
        exit;
    }
}

// Legacy wrapper in common.inc
function check_session(): bool {
    return Top7\Auth\SessionManager::check();
}
```

**Step 4**: Update common.inc to use new structure
```php
// common.inc (becomes a thin wrapper)
<?php

// Load new architecture
require_once __DIR__ . '/src/bootstrap.php';

// Import classes for easy access
use Top7\Database\Connection;
use Top7\Auth\SessionManager;
use Top7\Auth\PasswordService;
use Top7\Game\MatchService;
use Top7\Game\PronoService;
// ... etc

// Keep legacy function wrappers for backward compatibility
function init_sql(): PDO {
    return Connection::getInstance();
}

function check_session(): bool {
    return SessionManager::check();
}

function get_matchs_by_date($pdo, $season, $day) {
    return MatchService::getByDate($season, $day);
}

// ... gradually migrate all functions
```

**Migration Priority** (by module):
1. **Week 2, Day 1-2**: Database + Auth (critical path)
2. **Week 2, Day 3-4**: Game logic (matches, pronos, scores)
3. **Week 2, Day 5**: Utils (logger, email)
4. **Week 3**: Display functions (can wait for Phase 2)

**Testing Strategy**:
```bash
# Create test script
php test_migration.php

# Test each module independently
- Database connection
- Session management
- Password verification
- Match retrieval
- Score calculation

# Run full regression
- Login flow
- Registration
- Make prediction
- View rankings
- Admin updates
```

---

### 1.3 Responsive UI with Tailwind (Weeks 3-4)

#### Task 1.3.1: Setup Tailwind CSS
**Priority**: MEDIUM
**Effort**: 4 hours

**Installation**:
```bash
cd /mnt/c/dev/top7/www

# Create package.json
npm init -y

# Install Tailwind
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init
```

**Configuration**:
```javascript
// tailwind.config.js
module.exports = {
  content: [
    "./*.php",
    "./src/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        'top7-blue': '#0000CC',
        'top7-darkblue': '#00008B',
        'top7-orange': '#ff9900',
        'top7-red': '#cc0000',
      }
    },
  },
  plugins: [],
}
```

```css
/* assets/css/tailwind.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom components for Top7 */
@layer components {
  .btn-primary {
    @apply bg-top7-orange text-white px-6 py-2 rounded hover:bg-orange-600 transition;
  }

  .btn-blue {
    @apply bg-top7-blue text-white px-6 py-2 rounded hover:bg-blue-700 transition;
  }

  .card {
    @apply bg-white shadow-md rounded-lg p-6;
  }

  .table-top7 {
    @apply w-full border-collapse;
  }

  .table-top7 th {
    @apply bg-top7-blue text-white p-3 text-left;
  }

  .table-top7 td {
    @apply p-3 border-b border-gray-200;
  }

  .table-top7 tr:hover {
    @apply bg-gray-100;
  }
}
```

```json
// package.json - add scripts
{
  "scripts": {
    "dev": "tailwindcss -i ./assets/css/tailwind.css -o ./assets/css/output.css --watch",
    "build": "tailwindcss -i ./assets/css/tailwind.css -o ./assets/css/output.css --minify"
  }
}
```

**Docker update**:
```yaml
# test/docker-compose.yml - add node service
services:
  web:
    # ... existing config
    volumes:
      - ../www:/var/www/html
      - ./conf/conf.php:/var/www/html/conf/conf.php
      - ../www/assets:/var/www/html/assets  # NEW

  # Add node service for Tailwind
  tailwind:
    image: node:18-alpine
    working_dir: /app
    volumes:
      - ../www:/app
    command: npm run dev
    profiles:
      - dev
```

---

#### Task 1.3.2: Modernize Login Page
**Priority**: HIGH
**Effort**: 6-8 hours
**Files**: `index.php`, header function in common.inc

**Before** (current):
```php
// Old header
function print_header_login() {
    echo '<html class="login">';
    echo '<head>';
    echo '<link rel="stylesheet" type="text/css" href="common.css">';
    // ... more inline echo
}
```

**After** (responsive):
```php
// src/Display/PageRenderer.php
<?php
namespace Top7\Display;

class PageRenderer {

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
            <link rel="stylesheet" href="/assets/css/output.css">

            <!-- Favicons -->
            <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">

            <!-- Alpine.js -->
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

            <!-- Legacy CSS (for Phase 1) -->
            <link rel="stylesheet" href="/common.css">
        </head>
        <body class="<?= htmlspecialchars($pageClass) ?>">
        <?php
    }

    public static function footer(): void {
        ?>
        </body>
        </html>
        <?php
    }
}
```

**New Responsive Login Page**:
```php
// index.php - REWRITTEN
<?php
require_once 'src/bootstrap.php';

use Top7\Auth\SessionManager;
use Top7\Display\PageRenderer;
use Top7\Security\CsrfToken;

SessionManager::start();

PageRenderer::header('', 'Top7 - Connexion');
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
                Saison <?= TOP7_SEASON ?>
            </p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="login" class="space-y-6">
            <?= CsrfToken::field() ?>

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

            <!-- Remember me (future) -->
            <div class="flex items-center justify-between text-white text-sm">
                <a href="password" class="hover:text-top7-orange transition">
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
            <p class="text-white">
                Nouveau joueur ?
                <a href="register" class="text-top7-orange hover:text-orange-400 font-medium transition">
                    Créer une équipe
                </a>
            </p>
        </div>

        <!-- Links -->
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4 text-sm">
            <a href="rules.html" class="text-white hover:text-top7-orange transition text-center">
                Règles du jeu
            </a>
            <span class="hidden sm:inline text-white">•</span>
            <a href="lnr" class="text-white hover:text-top7-orange transition text-center">
                TOP 14
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="fixed bottom-4 right-4 text-white text-sm bg-black bg-opacity-50 px-4 py-2 rounded">
    <a href="https://github.com/pylscblt/top7" target="_blank" class="hover:text-top7-orange transition">
        v<?= APP_VERSION ?>
    </a>
</div>

<?php PageRenderer::footer(); ?>
```

**Testing Checklist**:
- [ ] Desktop (1920x1080) - Full width card
- [ ] Tablet (768x1024) - Medium card
- [ ] Mobile (375x667) - Full width with padding
- [ ] Mobile landscape (667x375) - Scrollable
- [ ] Form validation works
- [ ] Links are clickable
- [ ] CSRF token present

---

#### Task 1.3.3: Add Alpine.js for Interactivity
**Priority**: MEDIUM
**Effort**: 4 hours

**Example: Collapsible Match Details**
```php
// In display.php or display functions
<div x-data="{ open: false }" class="bg-white rounded-lg shadow mb-4">

    <!-- Match Summary (always visible) -->
    <div @click="open = !open" class="p-4 cursor-pointer hover:bg-gray-50">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="/images/teams/<?= $team1_id ?>.png" class="w-10 h-10">
                <span class="font-bold"><?= $team1_name ?></span>
            </div>
            <div class="text-2xl font-bold text-gray-600">vs</div>
            <div class="flex items-center gap-4">
                <span class="font-bold"><?= $team2_name ?></span>
                <img src="/images/teams/<?= $team2_id ?>.png" class="w-10 h-10">
            </div>
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
        </div>
    </div>

    <!-- Match Details (expandable) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="border-t p-4 bg-gray-50">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Score Details -->
            <div>
                <h4 class="font-bold mb-2">Score</h4>
                <p>Team 1: <?= $score1 ?> points</p>
                <p>Team 2: <?= $score2 ?> points</p>
            </div>

            <!-- Player Predictions -->
            <div>
                <h4 class="font-bold mb-2">Pronostics</h4>
                <?php foreach ($pronos as $prono): ?>
                    <p><?= $prono['player'] ?>: <?= $prono['team'] ?></p>
                <?php endforeach; ?>
            </div>

            <!-- Bonus Points -->
            <div>
                <h4 class="font-bold mb-2">Bonus</h4>
                <p>Offensif: <?= $bonus_off ? 'Oui' : 'Non' ?></p>
                <p>Défensif: <?= $bonus_def ? 'Oui' : 'Non' ?></p>
            </div>
        </div>
    </div>
</div>
```

**Example: Live Countdown Timer (improved)**
```php
<!-- Countdown with Alpine.js -->
<div x-data="countdown(<?= $deadline_seconds ?>)"
     x-init="start()"
     class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-yellow-700 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
        </svg>
        <span class="font-bold text-yellow-700">
            Temps restant:
            <span x-text="formatted"></span>
        </span>
    </div>
</div>

<script>
function countdown(seconds) {
    return {
        remaining: seconds,
        formatted: '',
        timer: null,

        start() {
            this.update();
            this.timer = setInterval(() => {
                this.remaining--;
                this.update();
                if (this.remaining <= 0) {
                    clearInterval(this.timer);
                    location.reload(); // Refresh when deadline hits
                }
            }, 1000);
        },

        update() {
            const days = Math.floor(this.remaining / 86400);
            const hours = Math.floor((this.remaining % 86400) / 3600);
            const minutes = Math.floor((this.remaining % 3600) / 60);
            const secs = this.remaining % 60;

            if (days > 0) {
                this.formatted = `${days}j ${hours}h ${minutes}m ${secs}s`;
            } else {
                this.formatted = `${hours}h ${minutes}m ${secs}s`;
            }
        }
    }
}
</script>
```

---

#### Task 1.3.4: Responsive Main Display (display.php)
**Priority**: HIGH
**Effort**: 12-16 hours

**Key Changes**:
1. Replace fixed-width tables with responsive grids
2. Stack elements vertically on mobile
3. Add hamburger menu for navigation
4. Make rankings scrollable on small screens

**Example: Responsive Ranking Table**
```php
<!-- Desktop: Full table, Mobile: Simplified cards -->
<div class="hidden md:block">
    <!-- Traditional table for desktop -->
    <table class="table-top7">
        <thead>
            <tr>
                <th>Rang</th>
                <th>Joueur</th>
                <th>Équipe</th>
                <th>V</th>
                <th>N</th>
                <th>D</th>
                <th>BO</th>
                <th>BD</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rankings as $rank => $player): ?>
            <tr class="rank_<?= $rank ?>">
                <td class="font-bold"><?= $rank ?></td>
                <td><?= $player['name'] ?></td>
                <td><?= $player['team'] ?></td>
                <td><?= $player['wins'] ?></td>
                <td><?= $player['draws'] ?></td>
                <td><?= $player['losses'] ?></td>
                <td><?= $player['bonus_off'] ?></td>
                <td><?= $player['bonus_def'] ?></td>
                <td class="font-bold text-top7-blue"><?= $player['points'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Mobile: Card layout -->
<div class="md:hidden space-y-2">
    <?php foreach ($rankings as $rank => $player): ?>
    <div class="card bg-white p-4 rank_<?= $rank ?>">
        <div class="flex justify-between items-center mb-2">
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold text-gray-400">#<?= $rank ?></span>
                <div>
                    <div class="font-bold"><?= $player['name'] ?></div>
                    <div class="text-sm text-gray-600"><?= $player['team'] ?></div>
                </div>
            </div>
            <div class="text-2xl font-bold text-top7-blue">
                <?= $player['points'] ?> pts
            </div>
        </div>
        <div class="flex justify-around text-sm text-gray-600 border-t pt-2">
            <div>V: <?= $player['wins'] ?></div>
            <div>N: <?= $player['draws'] ?></div>
            <div>D: <?= $player['losses'] ?></div>
            <div>BO: <?= $player['bonus_off'] ?></div>
            <div>BD: <?= $player['bonus_def'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
```

**Navigation for Mobile**:
```php
<!-- Mobile Navigation -->
<div x-data="{ mobileMenuOpen: false }" class="md:hidden">

    <!-- Hamburger Button -->
    <button @click="mobileMenuOpen = !mobileMenuOpen"
            class="fixed top-4 right-4 z-50 bg-top7-blue text-white p-3 rounded-lg shadow-lg">
        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-40">
    </div>

    <!-- Mobile Menu Panel -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         class="fixed top-0 right-0 bottom-0 w-64 bg-white shadow-xl z-50 p-6 overflow-y-auto">

        <nav class="space-y-4">
            <a href="display" class="block py-2 px-4 bg-top7-blue text-white rounded">
                Accueil
            </a>
            <a href="prono" class="block py-2 px-4 hover:bg-gray-100 rounded">
                Mes Pronos
            </a>
            <a href="rank" class="block py-2 px-4 hover:bg-gray-100 rounded">
                Classement TOP 14
            </a>
            <a href="rank7" class="block py-2 px-4 hover:bg-gray-100 rounded">
                Classement TOP 7
            </a>
            <a href="stats" class="block py-2 px-4 hover:bg-gray-100 rounded">
                Statistiques
            </a>
            <a href="team" class="block py-2 px-4 hover:bg-gray-100 rounded">
                Mon Équipe
            </a>
            <hr>
            <a href="logout" class="block py-2 px-4 text-red-600 hover:bg-red-50 rounded">
                Déconnexion
            </a>
        </nav>
    </div>
</div>
```

---

### Phase 1 Deliverables

**Week 1**:
- [ ] Password hashing updated to Argon2
- [ ] CSRF protection on all forms
- [ ] Security audit passed

**Week 2**:
- [ ] common.inc split into 8-10 modules
- [ ] Autoloader working
- [ ] All pages still functional
- [ ] Unit tests for core functions

**Week 3**:
- [ ] Tailwind CSS integrated
- [ ] Login page fully responsive
- [ ] Register page fully responsive
- [ ] Alpine.js examples working

**Week 4**:
- [ ] Main display responsive
- [ ] Rankings responsive
- [ ] Stats pages responsive
- [ ] Mobile navigation working
- [ ] Full regression test passed

**Metrics**:
- Mobile usability score: 90+ (Google Lighthouse)
- Security score: A+ (Mozilla Observatory)
- Code maintainability: B+ (CodeClimate)
- Page load: <2s (mobile 3G)

---

## Phase 2: API Layer + Vue Frontend (Months 2-4)

**Goal**: Build modern SPA with REST API
**Effort**: 120-150 hours
**Risk**: Medium (parallel deployment strategy)

### 2.1 API Design (Week 5)

#### Task 2.1.1: Define API Specification
**Priority**: CRITICAL
**Effort**: 8-12 hours

**API Structure**:
```
/api/v1/
├── auth/
│   ├── login          POST   - Authenticate user
│   ├── logout         POST   - End session
│   ├── register       POST   - Create new player/team
│   ├── me             GET    - Get current user info
│   └── password/reset POST   - Request password reset
├── matches/
│   ├── /              GET    - List all matches
│   ├── /:id           GET    - Get match details
│   ├── /day/:day      GET    - Get matches by day
│   └── /calendar      GET    - Get full calendar
├── predictions/
│   ├── /              GET    - Get my predictions
│   ├── /              POST   - Submit prediction
│   ├── /:id           PUT    - Update prediction
│   └── /:id           DELETE - Cancel prediction
├── rankings/
│   ├── /top7          GET    - TOP 7 player rankings
│   ├── /top14         GET    - TOP 14 team rankings
│   └── /history       GET    - Historical rankings
├── stats/
│   ├── /players       GET    - Player statistics
│   ├── /teams         GET    - Team statistics
│   └── /records       GET    - All-time records
├── teams/
│   ├── /:id           GET    - Get team info
│   └── /:id           PUT    - Update team info
└── admin/
    ├── /matches/:id   PUT    - Update match scores
    └── /calendar      POST   - Update calendar
```

**Response Format** (JSON API standard):
```json
{
  "data": {
    "id": 1,
    "type": "player",
    "attributes": {
      "login": "player1",
      "team_name": "Les Guerriers",
      "points": 42,
      "wins": 8,
      "draws": 2,
      "losses": 3
    },
    "relationships": {
      "predictions": {
        "data": [
          { "id": 1, "type": "prediction" },
          { "id": 2, "type": "prediction" }
        ]
      }
    }
  },
  "included": [
    {
      "id": 1,
      "type": "prediction",
      "attributes": {
        "match_id": 15,
        "team_id": 3,
        "points_earned": 4
      }
    }
  ],
  "meta": {
    "timestamp": "2025-11-09T10:30:00Z",
    "version": "1.0"
  }
}
```

**Error Format**:
```json
{
  "errors": [
    {
      "status": "400",
      "code": "INVALID_PREDICTION",
      "title": "Invalid prediction",
      "detail": "Cannot select this match - deadline has passed",
      "source": {
        "parameter": "match_id"
      }
    }
  ]
}
```

---

#### Task 2.1.2: Create API Router
**Priority**: HIGH
**Effort**: 6 hours

```php
// api/v1/index.php - API Router
<?php
require_once '../../src/bootstrap.php';

use Top7\Api\Router;
use Top7\Api\Middleware\AuthMiddleware;
use Top7\Api\Middleware\CorsMiddleware;

// Set JSON content type
header('Content-Type: application/json');

// CORS for development (restrict in production)
CorsMiddleware::handle();

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/v1', '', $path);

// Route dispatcher
$router = new Router();

// Public routes
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/password/reset', 'AuthController@resetPassword');

// Protected routes (require authentication)
$router->group(['middleware' => 'auth'], function($router) {

    // Auth
    $router->get('/auth/me', 'AuthController@me');
    $router->post('/auth/logout', 'AuthController@logout');

    // Matches
    $router->get('/matches', 'MatchController@index');
    $router->get('/matches/:id', 'MatchController@show');
    $router->get('/matches/day/:day', 'MatchController@byDay');
    $router->get('/matches/calendar', 'MatchController@calendar');

    // Predictions
    $router->get('/predictions', 'PredictionController@index');
    $router->post('/predictions', 'PredictionController@store');
    $router->put('/predictions/:id', 'PredictionController@update');
    $router->delete('/predictions/:id', 'PredictionController@destroy');

    // Rankings
    $router->get('/rankings/top7', 'RankingController@top7');
    $router->get('/rankings/top14', 'RankingController@top14');
    $router->get('/rankings/history', 'RankingController@history');

    // Stats
    $router->get('/stats/players', 'StatsController@players');
    $router->get('/stats/teams', 'StatsController@teams');
    $router->get('/stats/records', 'StatsController@records');

    // Teams
    $router->get('/teams/:id', 'TeamController@show');
    $router->put('/teams/:id', 'TeamController@update');

    // Admin routes
    $router->group(['middleware' => 'admin'], function($router) {
        $router->put('/admin/matches/:id', 'AdminController@updateMatch');
        $router->post('/admin/calendar', 'AdminController@updateCalendar');
    });
});

// Dispatch
try {
    $router->dispatch($method, $path);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'errors' => [
            [
                'status' => (string)($e->getCode() ?: 500),
                'title' => 'Server Error',
                'detail' => $e->getMessage(),
            ]
        ]
    ]);
}
```

---

#### Task 2.1.3: Implement API Controllers
**Priority**: HIGH
**Effort**: 24-32 hours

**Example: Auth Controller**
```php
// src/Api/Controllers/AuthController.php
<?php
namespace Top7\Api\Controllers;

use Top7\Auth\SessionManager;
use Top7\Auth\PasswordService;
use Top7\Database\Connection;
use Top7\Api\Response;

class AuthController extends BaseController {

    private PasswordService $passwordService;

    public function __construct() {
        $this->passwordService = new PasswordService();
    }

    public function login(): void {
        $data = $this->getJsonInput();

        // Validate input
        $this->validate($data, [
            'login' => 'required|string|min:8',
            'password' => 'required|string',
            'recaptcha_token' => 'required|string',
        ]);

        // Verify reCAPTCHA
        if (!$this->verifyRecaptcha($data['recaptcha_token'])) {
            Response::error('Invalid reCAPTCHA', 400, 'RECAPTCHA_FAILED');
        }

        // Find user
        $pdo = Connection::getInstance();
        $sql = "SELECT * FROM player WHERE login = ?";
        $users = pdo_fetch($pdo, $sql, [$data['login']]);

        if (empty($users)) {
            Response::error('Invalid credentials', 401, 'INVALID_CREDENTIALS');
        }

        $user = $users[0];

        // Verify password
        if (!$this->passwordService->verify($data['password'], $user['password_new'])) {
            Response::error('Invalid credentials', 401, 'INVALID_CREDENTIALS');
        }

        // Create session
        SessionManager::login($user['id'], $user);

        // Return user data
        Response::success([
            'id' => $user['id'],
            'type' => 'player',
            'attributes' => [
                'login' => $user['login'],
                'team_id' => $user['team_id'],
                'mode' => $user['mode'],
                'season' => $user['season'],
            ]
        ], 200);
    }

    public function logout(): void {
        SessionManager::destroy();
        Response::success(['message' => 'Logged out successfully'], 200);
    }

    public function me(): void {
        $userId = $_SESSION['player_id'];

        $pdo = Connection::getInstance();
        $sql = "SELECT * FROM player WHERE id = ?";
        $users = pdo_fetch($pdo, $sql, [$userId]);

        if (empty($users)) {
            Response::error('User not found', 404, 'USER_NOT_FOUND');
        }

        $user = $users[0];

        Response::success([
            'id' => $user['id'],
            'type' => 'player',
            'attributes' => [
                'login' => $user['login'],
                'email' => $user['email'],
                'team_id' => $user['team_id'],
                'points' => $user['points'],
                'wins' => $user['win'],
                'draws' => $user['draw'],
                'losses' => $user['lose'],
                'bonus_off' => $user['boff'],
                'bonus_def' => $user['bdef'],
                'ranking' => $user['ranking'],
            ]
        ], 200);
    }

    public function register(): void {
        $data = $this->getJsonInput();

        // Validate input
        $this->validate($data, [
            'login' => 'required|string|min:8|max:40',
            'password' => 'required|string|min:6|max:30',
            'email' => 'required|email|max:255',
            'team_name' => 'required|string|min:8|max:50',
            'recaptcha_token' => 'required|string',
        ]);

        // Verify reCAPTCHA
        if (!$this->verifyRecaptcha($data['recaptcha_token'])) {
            Response::error('Invalid reCAPTCHA', 400, 'RECAPTCHA_FAILED');
        }

        // Check if login exists
        $pdo = Connection::getInstance();
        $existing = pdo_fetch($pdo, "SELECT id FROM player WHERE login = ?", [$data['login']]);
        if (!empty($existing)) {
            Response::error('Login already exists', 409, 'LOGIN_EXISTS');
        }

        // Hash password
        $hashedPassword = $this->passwordService->hash($data['password']);

        // Create team
        $teamId = $this->createTeam($data['team_name']);

        // Create player
        $sql = "INSERT INTO player (login, password_new, email, team_id, season, mode)
                VALUES (?, ?, ?, ?, ?, ?)";
        $playerId = pdo_insert($pdo, $sql, [
            $data['login'],
            $hashedPassword,
            $data['email'],
            $teamId,
            TOP7_SEASON,
            c_player
        ]);

        // Auto-login
        SessionManager::login($playerId, [
            'login' => $data['login'],
            'mode' => c_player,
            'season' => TOP7_SEASON,
        ]);

        Response::success([
            'id' => $playerId,
            'type' => 'player',
            'attributes' => [
                'login' => $data['login'],
                'team_id' => $teamId,
            ]
        ], 201);
    }

    private function createTeam(string $name): int {
        $pdo = Connection::getInstance();
        $sql = "INSERT INTO top7team (name) VALUES (?)";
        return pdo_insert($pdo, $sql, [$name]);
    }

    private function verifyRecaptcha(string $token): bool {
        $secret = RECAPTCHA_SECRET_KEY;
        $response = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token"
        );
        $data = json_decode($response);
        return $data->success && $data->score >= 0.5;
    }
}
```

**Example: Match Controller**
```php
// src/Api/Controllers/MatchController.php
<?php
namespace Top7\Api\Controllers;

use Top7\Game\MatchService;
use Top7\Api\Response;

class MatchController extends BaseController {

    private MatchService $matchService;

    public function __construct() {
        $this->matchService = new MatchService();
    }

    public function index(): void {
        $season = $_GET['season'] ?? TOP7_SEASON;
        $day = $_GET['day'] ?? null;

        if ($day) {
            $matches = $this->matchService->getByDay($season, $day);
        } else {
            $matches = $this->matchService->getAll($season);
        }

        Response::collection($matches, 'match', function($match) {
            return [
                'id' => $match['id'],
                'type' => 'match',
                'attributes' => [
                    'day' => $match['day'],
                    'date' => $match['date'],
                    'time' => $match['time'],
                    'team1_id' => $match['team1_id'],
                    'team1_name' => $match['team1_name'],
                    'team2_id' => $match['team2_id'],
                    'team2_name' => $match['team2_name'],
                    'score1' => $match['score1'],
                    'score2' => $match['score2'],
                    'winner' => $match['winner'],
                    'status' => $this->getMatchStatus($match),
                ]
            ];
        });
    }

    public function show(int $id): void {
        $match = $this->matchService->getById($id);

        if (!$match) {
            Response::error('Match not found', 404, 'MATCH_NOT_FOUND');
        }

        // Include predictions for this match
        $predictions = $this->matchService->getPredictions($id);

        Response::success([
            'id' => $match['id'],
            'type' => 'match',
            'attributes' => [
                'day' => $match['day'],
                'date' => $match['date'],
                'time' => $match['time'],
                'team1_id' => $match['team1_id'],
                'team1_name' => $match['team1_name'],
                'team2_id' => $match['team2_id'],
                'team2_name' => $match['team2_name'],
                'score1' => $match['score1'],
                'score2' => $match['score2'],
                'winner' => $match['winner'],
                'bonus_off' => $match['bonus_off'],
                'bonus_def' => $match['bonus_def'],
                'status' => $this->getMatchStatus($match),
            ],
            'relationships' => [
                'predictions' => [
                    'data' => array_map(function($p) {
                        return ['id' => $p['id'], 'type' => 'prediction'];
                    }, $predictions)
                ]
            ]
        ], 200, [
            'included' => array_map(function($p) {
                return [
                    'id' => $p['id'],
                    'type' => 'prediction',
                    'attributes' => [
                        'player_id' => $p['player_id'],
                        'player_name' => $p['player_name'],
                        'team_id' => $p['team_id'],
                        'team_name' => $p['team_name'],
                        'points_earned' => $p['points_earned'],
                    ]
                ];
            }, $predictions)
        ]);
    }

    private function getMatchStatus(array $match): string {
        $now = time();
        $matchTime = strtotime($match['date'] . ' ' . $match['time']);
        $deadline = $matchTime - (12 * 3600); // 12 hours before

        if ($now < $deadline) {
            return 'open';
        } elseif ($now < $matchTime) {
            return 'locked';
        } elseif ($match['score1'] === null) {
            return 'in_progress';
        } else {
            return 'finished';
        }
    }
}
```

**More Controllers** (abbreviated):
- `PredictionController.php` - Submit/update predictions
- `RankingController.php` - Get rankings (Top7, Top14)
- `StatsController.php` - Statistics and records
- `TeamController.php` - Team management
- `AdminController.php` - Admin operations

---

### 2.2 Vue.js Frontend (Weeks 6-10)

#### Task 2.2.1: Setup Vue.js Project
**Priority**: HIGH
**Effort**: 4 hours

```bash
cd /mnt/c/dev/top7

# Create Vue project
npm create vite@latest frontend -- --template vue

cd frontend

# Install dependencies
npm install
npm install -D tailwindcss postcss autoprefixer
npm install vue-router@4 pinia axios

# Initialize Tailwind
npx tailwindcss init -p
```

**Project Structure**:
```
frontend/
├── src/
│   ├── api/
│   │   ├── client.js          # Axios instance
│   │   ├── auth.js            # Auth API calls
│   │   ├── matches.js         # Match API calls
│   │   ├── predictions.js     # Prediction API calls
│   │   └── rankings.js        # Ranking API calls
│   ├── components/
│   │   ├── common/
│   │   │   ├── AppHeader.vue
│   │   │   ├── AppFooter.vue
│   │   │   ├── LoadingSpinner.vue
│   │   │   └── ErrorMessage.vue
│   │   ├── match/
│   │   │   ├── MatchCard.vue
│   │   │   ├── MatchList.vue
│   │   │   └── MatchDetails.vue
│   │   ├── ranking/
│   │   │   ├── RankingTable.vue
│   │   │   ├── PlayerCard.vue
│   │   │   └── TeamCard.vue
│   │   └── prediction/
│   │       ├── PredictionForm.vue
│   │       └── PredictionList.vue
│   ├── layouts/
│   │   ├── DefaultLayout.vue
│   │   └── AuthLayout.vue
│   ├── pages/
│   │   ├── LoginPage.vue
│   │   ├── RegisterPage.vue
│   │   ├── DashboardPage.vue
│   │   ├── PredictionsPage.vue
│   │   ├── RankingsPage.vue
│   │   ├── StatsPage.vue
│   │   └── TeamPage.vue
│   ├── router/
│   │   └── index.js
│   ├── stores/
│   │   ├── auth.js
│   │   ├── matches.js
│   │   ├── predictions.js
│   │   └── rankings.js
│   ├── utils/
│   │   ├── validators.js
│   │   └── formatters.js
│   ├── App.vue
│   └── main.js
├── public/
│   └── images/              # Copy from www/
├── index.html
├── vite.config.js
├── tailwind.config.js
└── package.json
```

**Vite Configuration**:
```javascript
// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:80',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: '../www/dist',
    emptyOutDir: true,
  }
})
```

---

#### Task 2.2.2: Implement Auth Store (Pinia)
**Priority**: HIGH
**Effort**: 4 hours

```javascript
// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import * as authApi from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => user.value !== null)
  const isAdmin = computed(() => user.value?.attributes.mode === 1)

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.login(credentials)
      user.value = response.data
      return true
    } catch (err) {
      error.value = err.response?.data?.errors[0]?.detail || 'Login failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(data) {
    loading.value = true
    error.value = null

    try {
      const response = await authApi.register(data)
      user.value = response.data
      return true
    } catch (err) {
      error.value = err.response?.data?.errors[0]?.detail || 'Registration failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true

    try {
      await authApi.logout()
      user.value = null
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      loading.value = false
    }
  }

  async function fetchCurrentUser() {
    loading.value = true

    try {
      const response = await authApi.me()
      user.value = response.data
    } catch (err) {
      user.value = null
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchCurrentUser,
  }
})
```

---

#### Task 2.2.3: Build Vue Components
**Priority**: HIGH
**Effort**: 40-50 hours

**Example: Login Page**
```vue
<!-- src/pages/LoginPage.vue -->
<template>
  <div class="min-h-screen bg-cover bg-center flex items-center justify-center p-4"
       :style="{ backgroundImage: `url('/images/top7_login.jpeg')` }">

    <div class="w-full max-w-md bg-gray-800 bg-opacity-90 rounded-lg shadow-2xl p-8">

      <!-- Logo/Title -->
      <div class="text-center mb-8">
        <h1 class="text-6xl font-bold text-white mb-4">TOP7</h1>
        <p class="text-2xl text-white">Saison {{ currentSeason }}</p>
      </div>

      <!-- Error Message -->
      <div v-if="authStore.error"
           class="mb-4 bg-red-500 text-white p-3 rounded">
        {{ authStore.error }}
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleSubmit" class="space-y-6">

        <!-- Username -->
        <div>
          <label for="login" class="block text-white text-sm font-medium mb-2">
            Nom d'utilisateur
          </label>
          <input
            v-model="form.login"
            type="text"
            id="login"
            required
            :disabled="authStore.loading"
            class="w-full px-4 py-3 rounded bg-white text-gray-900
                   focus:outline-none focus:ring-2 focus:ring-orange-500
                   disabled:opacity-50"
            placeholder="Entrez votre nom"
          >
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-white text-sm font-medium mb-2">
            Mot de passe
          </label>
          <input
            v-model="form.password"
            type="password"
            id="password"
            required
            :disabled="authStore.loading"
            class="w-full px-4 py-3 rounded bg-white text-gray-900
                   focus:outline-none focus:ring-2 focus:ring-orange-500
                   disabled:opacity-50"
            placeholder="Entrez votre mot de passe"
          >
        </div>

        <!-- Forgot Password Link -->
        <div class="flex justify-end">
          <router-link to="/password"
                       class="text-white text-sm hover:text-orange-400 transition">
            Mot de passe oublié ?
          </router-link>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold
                 py-3 rounded transition duration-200 transform hover:scale-105
                 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
          <span v-if="!authStore.loading">SE CONNECTER</span>
          <span v-else>CONNEXION...</span>
        </button>
      </form>

      <!-- Register Link -->
      <div class="mt-6 text-center">
        <p class="text-white">
          Nouveau joueur ?
          <router-link to="/register"
                       class="text-orange-500 hover:text-orange-400 font-medium transition">
            Créer une équipe
          </router-link>
        </p>
      </div>

      <!-- Links -->
      <div class="mt-8 flex justify-center gap-4 text-sm">
        <a href="/rules.html" target="_blank"
           class="text-white hover:text-orange-400 transition">
          Règles du jeu
        </a>
        <span class="text-white">•</span>
        <router-link to="/lnr"
                     class="text-white hover:text-orange-400 transition">
          TOP 14
        </router-link>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const currentSeason = '2025-2026'

const form = ref({
  login: '',
  password: '',
  recaptcha_token: '' // TODO: Implement reCAPTCHA v3
})

async function handleSubmit() {
  // TODO: Get reCAPTCHA token
  // form.value.recaptcha_token = await executeRecaptcha('login')

  const success = await authStore.login(form.value)

  if (success) {
    router.push('/dashboard')
  }
}
</script>
```

**Example: Match Card Component**
```vue
<!-- src/components/match/MatchCard.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow">

    <!-- Match Header -->
    <div class="bg-blue-600 text-white p-3 rounded-t-lg flex justify-between items-center">
      <span class="font-bold">Journée {{ match.attributes.day }}</span>
      <span class="text-sm">{{ formatDate(match.attributes.date) }}</span>
      <span :class="statusClass">{{ statusText }}</span>
    </div>

    <!-- Teams -->
    <div class="p-4">
      <div class="flex items-center justify-between mb-4">

        <!-- Team 1 -->
        <div class="flex-1 flex items-center gap-3">
          <img :src="`/images/teams/${match.attributes.team1_id}.png`"
               :alt="match.attributes.team1_name"
               class="w-12 h-12 object-contain">
          <div>
            <div class="font-bold text-lg">{{ match.attributes.team1_name }}</div>
            <div v-if="match.attributes.score1 !== null"
                 class="text-2xl font-bold text-blue-600">
              {{ match.attributes.score1 }}
            </div>
          </div>
        </div>

        <!-- VS -->
        <div class="px-4 text-gray-400 font-bold text-xl">VS</div>

        <!-- Team 2 -->
        <div class="flex-1 flex items-center justify-end gap-3">
          <div class="text-right">
            <div class="font-bold text-lg">{{ match.attributes.team2_name }}</div>
            <div v-if="match.attributes.score2 !== null"
                 class="text-2xl font-bold text-blue-600">
              {{ match.attributes.score2 }}
            </div>
          </div>
          <img :src="`/images/teams/${match.attributes.team2_id}.png`"
               :alt="match.attributes.team2_name"
               class="w-12 h-12 object-contain">
        </div>
      </div>

      <!-- Predictions -->
      <div v-if="predictions.length" class="border-t pt-3 mt-3">
        <div class="text-sm text-gray-600 mb-2 font-semibold">Pronostics :</div>
        <div class="flex flex-wrap gap-2">
          <div v-for="pred in predictions"
               :key="pred.player_id"
               class="text-xs bg-gray-100 px-2 py-1 rounded">
            <span class="font-semibold">{{ pred.player_name }}</span> → {{ pred.team_name }}
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div v-if="match.attributes.status === 'open'" class="mt-4">
        <button @click="$emit('predict', match)"
                class="w-full bg-orange-500 hover:bg-orange-600 text-white
                       py-2 rounded transition">
          Faire mon pronostic
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  match: {
    type: Object,
    required: true
  },
  predictions: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['predict'])

const statusText = computed(() => {
  const status = props.match.attributes.status
  const map = {
    open: 'Ouvert',
    locked: 'Fermé',
    in_progress: 'En cours',
    finished: 'Terminé'
  }
  return map[status] || status
})

const statusClass = computed(() => {
  const status = props.match.attributes.status
  const classes = {
    open: 'bg-green-500 text-white px-2 py-1 rounded text-xs',
    locked: 'bg-red-500 text-white px-2 py-1 rounded text-xs',
    in_progress: 'bg-yellow-500 text-white px-2 py-1 rounded text-xs',
    finished: 'bg-gray-500 text-white px-2 py-1 rounded text-xs'
  }
  return classes[status] || ''
})

function formatDate(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short'
  })
}
</script>
```

**Example: Dashboard Page**
```vue
<!-- src/pages/DashboardPage.vue -->
<template>
  <div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-800 mb-2">
        Tableau de bord
      </h1>
      <p class="text-gray-600">
        Bienvenue {{ authStore.user?.attributes.login }} !
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm uppercase">Classement</div>
        <div class="text-3xl font-bold text-blue-600">
          #{{ authStore.user?.attributes.ranking || '-' }}
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm uppercase">Points</div>
        <div class="text-3xl font-bold text-blue-600">
          {{ authStore.user?.attributes.points || 0 }}
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm uppercase">Victoires</div>
        <div class="text-3xl font-bold text-green-600">
          {{ authStore.user?.attributes.wins || 0 }}
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm uppercase">Défaites</div>
        <div class="text-3xl font-bold text-red-600">
          {{ authStore.user?.attributes.losses || 0 }}
        </div>
      </div>
    </div>

    <!-- Current Day Matches -->
    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">
          Journée {{ currentDay }}
        </h2>
        <div class="flex gap-2">
          <button @click="prevDay"
                  :disabled="currentDay <= 1"
                  class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300
                         disabled:opacity-50 disabled:cursor-not-allowed">
            ← Précédent
          </button>
          <button @click="nextDay"
                  :disabled="currentDay >= 29"
                  class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300
                         disabled:opacity-50 disabled:cursor-not-allowed">
            Suivant →
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="matchesStore.loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Matches Grid -->
      <div v-else-if="matches.length"
           class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <MatchCard v-for="match in matches"
                   :key="match.id"
                   :match="match"
                   :predictions="getMatchPredictions(match.id)"
                   @predict="openPredictionModal" />
      </div>

      <!-- No Matches -->
      <div v-else class="bg-gray-100 rounded-lg p-8 text-center text-gray-600">
        Aucun match pour cette journée
      </div>
    </div>

    <!-- Mini Rankings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- TOP 7 Ranking -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-4">Classement TOP 7</h3>
        <!-- Mini ranking table -->
      </div>

      <!-- TOP 14 Ranking -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-xl font-bold mb-4">Classement TOP 14</h3>
        <!-- Mini ranking table -->
      </div>
    </div>

    <!-- Prediction Modal -->
    <PredictionModal v-if="showPredictionModal"
                     :match="selectedMatch"
                     @close="closePredictionModal"
                     @submit="submitPrediction" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useMatchesStore } from '@/stores/matches'
import MatchCard from '@/components/match/MatchCard.vue'
import PredictionModal from '@/components/prediction/PredictionModal.vue'

const authStore = useAuthStore()
const matchesStore = useMatchesStore()

const currentDay = ref(1)
const showPredictionModal = ref(false)
const selectedMatch = ref(null)

const matches = computed(() => matchesStore.matchesByDay(currentDay.value))

onMounted(async () => {
  // Fetch current day from server
  currentDay.value = await matchesStore.fetchCurrentDay()
  // Fetch matches
  await matchesStore.fetchMatches(currentDay.value)
})

function prevDay() {
  if (currentDay.value > 1) {
    currentDay.value--
    matchesStore.fetchMatches(currentDay.value)
  }
}

function nextDay() {
  if (currentDay.value < 29) {
    currentDay.value++
    matchesStore.fetchMatches(currentDay.value)
  }
}

function getMatchPredictions(matchId) {
  return [] // TODO: Fetch from predictions store
}

function openPredictionModal(match) {
  selectedMatch.value = match
  showPredictionModal.value = true
}

function closePredictionModal() {
  showPredictionModal.value = false
  selectedMatch.value = null
}

async function submitPrediction(data) {
  // TODO: Submit prediction via API
  console.log('Submit prediction:', data)
  closePredictionModal()
}
</script>
```

---

### 2.3 Parallel Deployment Strategy (Week 11)

#### Task 2.3.1: Configure Routing
**Priority**: HIGH
**Effort**: 4 hours

**Strategy**: Run both old PHP and new Vue app in parallel

```
URL Strategy:
- /              → Old PHP login (index.php)
- /app/*         → New Vue SPA
- /api/*         → API endpoints (JSON)
- /legacy/*      → Old PHP pages (fallback)
```

**Apache Configuration**:
```apache
# .htaccess
RewriteEngine On

# API routes (JSON)
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^api/(.*)$ api/v1/index.php [L,QSA]

# Vue SPA routes (serve index.html for all /app/* routes)
RewriteCond %{REQUEST_URI} ^/app/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^app/(.*)$ dist/index.html [L]

# Static assets (Vue build)
RewriteCond %{REQUEST_URI} ^/dist/
RewriteRule ^dist/(.*)$ dist/$1 [L]

# Legacy PHP pages (backward compatibility)
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/app/
RewriteCond %{REQUEST_URI} !^/dist/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ $1.php [L,QSA]
```

**User Migration**:
```php
// index.php - Login page with opt-in to new UI
<?php
// Check if user wants new UI
if (isset($_SESSION['use_new_ui']) && $_SESSION['use_new_ui']) {
    header('Location: /app/');
    exit;
}

// Show login with "Try new version" button
?>
<a href="/app/" class="text-white underline">
    Essayer la nouvelle version
</a>
```

---

### Phase 2 Deliverables

**Week 5**: API Design
- [ ] API specification complete
- [ ] API router implemented
- [ ] Auth endpoints working

**Week 6-7**: API Controllers
- [ ] Auth controller complete
- [ ] Match controller complete
- [ ] Prediction controller complete
- [ ] Ranking controller complete

**Week 8**: Vue Setup
- [ ] Vue project scaffolded
- [ ] Routing configured
- [ ] Pinia stores setup
- [ ] API client configured

**Week 9-10**: Vue Components
- [ ] Login/Register pages
- [ ] Dashboard page
- [ ] Predictions page
- [ ] Rankings page
- [ ] Stats page

**Week 11**: Integration
- [ ] Both versions running in parallel
- [ ] API fully functional
- [ ] User migration path clear
- [ ] Full integration tests

**Metrics**:
- API response time: <100ms (p95)
- Frontend load time: <1s (on 4G)
- Test coverage: >80%
- Zero breaking changes to legacy

---

## Phase 3: Polish & Advanced Features (Months 5-6)

**Goal**: Real-time updates, PWA, performance
**Effort**: 50-80 hours
**Risk**: Low (additive features)

### 3.1 Real-time Score Updates (Weeks 12-13)

#### Task 3.1.1: Implement Polling Strategy
**Priority**: HIGH
**Effort**: 8 hours

**Simple polling** (no WebSockets needed initially):

```javascript
// src/services/polling.js
export class PollingService {
  constructor(interval = 30000) { // 30 seconds
    this.interval = interval
    this.timers = new Map()
  }

  start(key, callback) {
    if (this.timers.has(key)) {
      return // Already polling
    }

    // Immediate call
    callback()

    // Set up interval
    const timer = setInterval(callback, this.interval)
    this.timers.set(key, timer)
  }

  stop(key) {
    const timer = this.timers.get(key)
    if (timer) {
      clearInterval(timer)
      this.timers.delete(key)
    }
  }

  stopAll() {
    this.timers.forEach(timer => clearInterval(timer))
    this.timers.clear()
  }
}

export const polling = new PollingService()
```

```javascript
// src/stores/matches.js - Add real-time updates
import { polling } from '@/services/polling'

export const useMatchesStore = defineStore('matches', () => {
  // ... existing state

  function startLiveUpdates() {
    polling.start('matches', async () => {
      // Only poll if there are matches in progress
      const now = new Date()
      const hasLiveMatches = matches.value.some(m => {
        const matchTime = new Date(m.attributes.date + ' ' + m.attributes.time)
        return m.attributes.status === 'in_progress' ||
               (now > matchTime && m.attributes.score1 === null)
      })

      if (hasLiveMatches) {
        await fetchMatches(currentDay.value, true) // silent refresh
      }
    })
  }

  function stopLiveUpdates() {
    polling.stop('matches')
  }

  return {
    // ... existing
    startLiveUpdates,
    stopLiveUpdates,
  }
})
```

**Usage in components**:
```vue
<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useMatchesStore } from '@/stores/matches'

const matchesStore = useMatchesStore()

onMounted(() => {
  matchesStore.startLiveUpdates()
})

onUnmounted(() => {
  matchesStore.stopLiveUpdates()
})
</script>
```

---

#### Task 3.1.2: Add WebSockets (Optional, Advanced)
**Priority**: MEDIUM
**Effort**: 16 hours

If you want true real-time (instead of polling):

```php
// Use Ratchet (PHP WebSocket library)
composer require cboden/ratchet

// ws/server.php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Top7\WebSocket\ScoreUpdateServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ScoreUpdateServer()
        )
    ),
    8080
);

$server->run();
```

```javascript
// src/services/websocket.js
export class WebSocketService {
  constructor(url) {
    this.url = url
    this.ws = null
    this.listeners = new Map()
  }

  connect() {
    this.ws = new WebSocket(this.url)

    this.ws.onopen = () => {
      console.log('WebSocket connected')
    }

    this.ws.onmessage = (event) => {
      const data = JSON.parse(event.data)
      this.emit(data.type, data.payload)
    }

    this.ws.onerror = (error) => {
      console.error('WebSocket error:', error)
    }

    this.ws.onclose = () => {
      console.log('WebSocket closed, reconnecting...')
      setTimeout(() => this.connect(), 5000)
    }
  }

  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, [])
    }
    this.listeners.get(event).push(callback)
  }

  emit(event, data) {
    const callbacks = this.listeners.get(event) || []
    callbacks.forEach(callback => callback(data))
  }

  disconnect() {
    if (this.ws) {
      this.ws.close()
    }
  }
}

export const ws = new WebSocketService('ws://localhost:8080')
```

---

### 3.2 Progressive Web App (Week 14)

#### Task 3.2.1: Add PWA Manifest
**Priority**: MEDIUM
**Effort**: 2 hours

```json
// public/manifest.json
{
  "name": "Top7 - Rugby Pronos",
  "short_name": "Top7",
  "description": "Jeu de pronostics rugby TOP 14",
  "start_url": "/app/",
  "display": "standalone",
  "background_color": "#0000CC",
  "theme_color": "#0000CC",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/favicons/android-chrome-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/favicons/android-chrome-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

```html
<!-- index.html -->
<head>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0000CC">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="apple-mobile-web-app-title" content="Top7">
  <link rel="apple-touch-icon" href="/favicons/apple-touch-icon.png">
</head>
```

---

#### Task 3.2.2: Add Service Worker
**Priority**: MEDIUM
**Effort**: 6 hours

```javascript
// public/sw.js
const CACHE_NAME = 'top7-v1'
const urlsToCache = [
  '/app/',
  '/dist/index.html',
  '/dist/assets/index.css',
  '/dist/assets/index.js',
  '/images/teams/',
  '/favicons/',
]

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  )
})

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response
        }

        // Clone request
        const fetchRequest = event.request.clone()

        return fetch(fetchRequest).then(response => {
          // Check if valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response
          }

          // Clone response
          const responseToCache = response.clone()

          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache)
            })

          return response
        })
      })
  )
})

self.addEventListener('activate', (event) => {
  const cacheWhitelist = [CACHE_NAME]

  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (!cacheWhitelist.includes(cacheName)) {
            return caches.delete(cacheName)
          }
        })
      )
    })
  )
})
```

```javascript
// src/main.js - Register service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.log('SW registered:', registration)
      })
      .catch(error => {
        console.log('SW registration failed:', error)
      })
  })
}
```

---

### 3.3 Performance Optimization (Weeks 15-16)

#### Task 3.3.1: Database Optimization
**Priority**: HIGH
**Effort**: 8 hours

**Add indexes**:
```sql
-- Optimize common queries
ALTER TABLE match ADD INDEX idx_season_day (season, day);
ALTER TABLE match ADD INDEX idx_date (date, time);
ALTER TABLE prono ADD INDEX idx_player_season (player_id, season);
ALTER TABLE prono ADD INDEX idx_match (match_id);
ALTER TABLE score ADD INDEX idx_match_day (match_id, day);
ALTER TABLE player ADD INDEX idx_season_ranking (season, ranking);

-- Full-text search for player names
ALTER TABLE player ADD FULLTEXT idx_login (login);
```

**Query optimization examples**:
```php
// BAD - N+1 query problem
foreach ($matches as $match) {
    $predictions = get_predictions($match['id']); // Query in loop!
}

// GOOD - Single query with JOIN
$sql = "
    SELECT m.*, p.player_id, p.team_id, pl.login
    FROM match m
    LEFT JOIN prono p ON m.id = p.match_id
    LEFT JOIN player pl ON p.player_id = pl.id
    WHERE m.season = ? AND m.day = ?
    ORDER BY m.date, m.time
";
```

---

#### Task 3.3.2: Frontend Optimization
**Priority**: MEDIUM
**Effort**: 6 hours

**Code splitting**:
```javascript
// router/index.js - Lazy load routes
const routes = [
  {
    path: '/dashboard',
    component: () => import('@/pages/DashboardPage.vue')
  },
  {
    path: '/stats',
    component: () => import('@/pages/StatsPage.vue')
  },
  // ... etc
]
```

**Image optimization**:
```bash
# Optimize team logos
npm install -g imagemin-cli imagemin-pngquant
imagemin www/images/teams/*.png --out-dir=www/images/teams/ --plugin=pngquant

# Convert to WebP (better compression)
for file in www/images/teams/*.png; do
  cwebp -q 80 "$file" -o "${file%.png}.webp"
done
```

**Component lazy loading**:
```vue
<script setup>
// Lazy load heavy components
const StatsChart = defineAsyncComponent(() =>
  import('@/components/stats/StatsChart.vue')
)
</script>
```

---

### 3.4 Testing & Quality (Week 17-18)

#### Task 3.4.1: Unit Tests
**Priority**: HIGH
**Effort**: 16 hours

**PHP Tests (PHPUnit)**:
```bash
composer require --dev phpunit/phpunit
```

```php
// tests/Unit/PasswordServiceTest.php
<?php
use PHPUnit\Framework\TestCase;
use Top7\Auth\PasswordService;

class PasswordServiceTest extends TestCase {
    private PasswordService $service;

    protected function setUp(): void {
        $this->service = new PasswordService();
    }

    public function testHashPassword(): void {
        $password = 'test123456';
        $hash = $this->service->hash($password);

        $this->assertNotEquals($password, $hash);
        $this->assertTrue($this->service->verify($password, $hash));
    }

    public function testVerifyInvalidPassword(): void {
        $hash = $this->service->hash('correct');
        $this->assertFalse($this->service->verify('wrong', $hash));
    }
}
```

**Vue Tests (Vitest)**:
```bash
npm install -D vitest @vue/test-utils
```

```javascript
// src/components/__tests__/MatchCard.test.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MatchCard from '../match/MatchCard.vue'

describe('MatchCard', () => {
  it('renders match teams', () => {
    const match = {
      id: 1,
      attributes: {
        team1_name: 'Toulouse',
        team2_name: 'Clermont',
        status: 'open'
      }
    }

    const wrapper = mount(MatchCard, {
      props: { match, predictions: [] }
    })

    expect(wrapper.text()).toContain('Toulouse')
    expect(wrapper.text()).toContain('Clermont')
  })

  it('emits predict event when button clicked', async () => {
    const match = {
      id: 1,
      attributes: { status: 'open' }
    }

    const wrapper = mount(MatchCard, {
      props: { match, predictions: [] }
    })

    await wrapper.find('button').trigger('click')
    expect(wrapper.emitted('predict')).toBeTruthy()
  })
})
```

---

#### Task 3.4.2: E2E Tests
**Priority**: MEDIUM
**Effort**: 12 hours

**Playwright E2E tests**:
```bash
npm install -D @playwright/test
```

```javascript
// e2e/login.spec.js
import { test, expect } from '@playwright/test'

test.describe('Login flow', () => {
  test('should login successfully', async ({ page }) => {
    await page.goto('http://localhost:3000/app/')

    await page.fill('input[type="text"]', 'testuser')
    await page.fill('input[type="password"]', 'password123')
    await page.click('button[type="submit"]')

    await expect(page).toHaveURL(/dashboard/)
    await expect(page.locator('h1')).toContainText('Tableau de bord')
  })

  test('should show error for invalid credentials', async ({ page }) => {
    await page.goto('http://localhost:3000/app/')

    await page.fill('input[type="text"]', 'wronguser')
    await page.fill('input[type="password"]', 'wrongpass')
    await page.click('button[type="submit"]')

    await expect(page.locator('.bg-red-500')).toBeVisible()
  })
})
```

---

### Phase 3 Deliverables

**Week 12-13**: Real-time Updates
- [ ] Polling mechanism working
- [ ] Live score updates
- [ ] Automatic UI refresh

**Week 14**: PWA
- [ ] PWA manifest configured
- [ ] Service worker caching assets
- [ ] Install prompt working
- [ ] Offline mode functional

**Week 15-16**: Performance
- [ ] Database indexed
- [ ] API response <100ms
- [ ] Frontend load <1s
- [ ] Lighthouse score >90

**Week 17-18**: Testing
- [ ] Unit test coverage >80%
- [ ] E2E tests for critical flows
- [ ] Load testing passed (100 concurrent users)
- [ ] Security audit passed

**Metrics**:
- Lighthouse Performance: 90+
- Lighthouse PWA: 100
- API p95 latency: <100ms
- Frontend FCP: <1s
- Test coverage: >80%

---

## Migration & Rollout Strategy

### User Migration Path

**Soft Launch (Month 4)**:
```
1. New UI available at /app/
2. Link from old UI: "Try new version"
3. User can switch back anytime
4. Both versions run in parallel
5. Collect feedback
```

**Beta Period (Month 5)**:
```
1. Select users auto-redirected to new UI
2. Feedback form in new UI
3. Bug fixes and improvements
4. Performance monitoring
```

**Full Migration (Month 6)**:
```
1. All users redirected to new UI
2. Old UI available at /legacy/
3. Monitor for issues
4. Remove old UI after 1 season
```

### Rollback Plan

If critical issues arise:
```
1. Switch .htaccess to redirect /app/ → /
2. Disable API endpoints
3. Investigate and fix
4. Re-enable gradually
```

### Data Migration

No database changes needed! API uses existing schema.

---

## Resource Requirements

### Development Time

| Phase | Tasks | Effort | Calendar Time |
|-------|-------|--------|---------------|
| Phase 1 | Security + Refactor + Responsive | 80-100h | 4 weeks |
| Phase 2 | API + Vue Frontend | 120-150h | 12 weeks |
| Phase 3 | PWA + Performance + Testing | 50-80h | 8 weeks |
| **Total** | | **250-330h** | **24 weeks** |

Assuming **10-15 hours/week** → **6 months**

### Infrastructure

**Development**:
- Docker (existing)
- Node.js 18+ (for Vue/Vite)
- PHP 8.3 (upgrade)

**Production** (no additional cost):
- Same hosting as current
- No additional services needed
- Maybe add Redis for sessions (optional)

---

## Success Metrics

### Technical Metrics

| Metric | Current | Target (Phase 1) | Target (Phase 3) |
|--------|---------|------------------|------------------|
| Mobile usability | Fail | 90+ | 95+ |
| Lighthouse Performance | 60 | 80 | 90+ |
| Page load (3G) | 5s+ | 3s | <1s |
| API response time | N/A | 200ms | <100ms |
| Code maintainability | C | B | A |
| Security score | B | A | A+ |
| Test coverage | 0% | 40% | 80% |

### User Experience Metrics

| Metric | Target |
|--------|--------|
| Mobile traffic | +50% |
| Session duration | +30% |
| Bounce rate | -20% |
| User satisfaction | 4.5/5 |
| PWA installs | 100+ |

---

## Risk Management

### Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Breaking existing functionality | Medium | High | Parallel deployment, extensive testing |
| Performance degradation | Low | Medium | Load testing, monitoring |
| Security vulnerabilities | Low | High | Security audit, penetration testing |
| Browser compatibility | Low | Medium | Cross-browser testing |
| API downtime | Low | High | Graceful degradation, caching |

### Business Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| User resistance to change | Medium | Medium | Gradual rollout, training, feedback |
| Timeline overrun | Medium | Low | Phased approach, can stop after Phase 1 |
| Budget overrun | Low | Low | Fixed scope per phase |

---

## Decision Points

### After Phase 1 (Month 1)
- ✓ Security improved
- ✓ Code maintainable
- ✓ Mobile responsive

**Decision**: Continue to Phase 2 OR stop here (still valuable improvements)

### After Phase 2 (Month 4)
- ✓ Modern UI available
- ✓ API functional
- ✓ Parallel deployment

**Decision**: Full migration to new UI OR keep both versions

### After Phase 3 (Month 6)
- ✓ PWA features
- ✓ Real-time updates
- ✓ Optimized

**Decision**: Sunset old UI OR keep legacy for 1 more season

---

## Appendix: Code Examples & Snippets

### A. Helper Functions

```php
// src/Utils/Helpers.php
<?php
namespace Top7\Utils;

class Helpers {
    public static function sanitize(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    public static function formatDate(string $date, string $format = 'Y-m-d'): string {
        return date($format, strtotime($date));
    }

    public static function jsonResponse($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
```

### B. API Response Class

```php
// src/Api/Response.php
<?php
namespace Top7\Api;

class Response {
    public static function success($data, int $status = 200, array $extra = []): void {
        http_response_code($status);
        echo json_encode(array_merge([
            'data' => $data,
            'meta' => [
                'timestamp' => date('c'),
                'version' => '1.0'
            ]
        ], $extra));
        exit;
    }

    public static function error(string $message, int $status = 400, string $code = 'ERROR'): void {
        http_response_code($status);
        echo json_encode([
            'errors' => [
                [
                    'status' => (string)$status,
                    'code' => $code,
                    'title' => 'Error',
                    'detail' => $message,
                ]
            ]
        ]);
        exit;
    }

    public static function collection(array $items, string $type, callable $transformer): void {
        $data = array_map($transformer, $items);
        self::success($data);
    }
}
```

### C. Docker Compose Update

```yaml
# test/docker-compose.yml
version: '3.8'

services:
  web:
    image: php:8.3-apache
    ports:
      - "80:80"
    volumes:
      - ../www:/var/www/html
      - ./conf/php.ini:/usr/local/etc/php/php.ini
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: topseven
    volumes:
      - ./mysql:/var/lib/mysql
    ports:
      - "3306:3306"

  myadmin:
    image: phpmyadmin:latest
    environment:
      PMA_HOST: db
      PMA_PORT: 3306
    ports:
      - "8080:80"
    depends_on:
      - db

  # Vue dev server (for development)
  frontend:
    image: node:18-alpine
    working_dir: /app
    volumes:
      - ../frontend:/app
    command: npm run dev
    ports:
      - "3000:3000"
    profiles:
      - dev
```

---

## Next Steps

1. **Review this plan** with stakeholders
2. **Set up development environment** (Phase 1 prerequisites)
3. **Create GitHub project board** to track tasks
4. **Start with Phase 1, Task 1.1.1** (Password hashing)
5. **Weekly progress reviews**

---

**Questions? Comments? Let's discuss!**

Contact: [GitHub Issues](https://github.com/pylscblt/top7/issues)
