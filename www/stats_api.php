<?php
/**
 * API Stats - Fournit les données JSON pour les graphiques d'évolution
 */

// Suppress PHP errors from being displayed in JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

include("common.inc");
check_session();
init_sql();

// Clear any buffered output (PHP warnings, etc.)
ob_end_clean();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$season = $_SESSION['season'] ?? get_current_season();

try {
    switch ($action) {
        case 'player_evolution':
            echo json_encode(get_player_evolution_data($season));
            break;

        case 'player_comparison':
            $players = $_GET['players'] ?? '';
            $player_ids = array_filter(array_map('intval', explode(',', $players)));
            echo json_encode(get_players_comparison_data($season, $player_ids));
            break;

        case 'team_evolution':
            $team = intval($_GET['team'] ?? 0);
            echo json_encode(get_team_evolution_data($season, $team));
            break;

        case 'players_list':
            echo json_encode(get_players_list($season));
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action invalide']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Récupère l'évolution du joueur connecté par journée
 */
function get_player_evolution_data($season) {
    global $pdo;

    $player_id = $_SESSION['player_idx'] ?? $_SESSION['player'] ?? null;
    $team = $_SESSION['team'] ?? $_SESSION['top7team'] ?? null;

    if (!$player_id) {
        return [
            'labels' => [],
            'points' => [],
            'rank' => [],
            'player_name' => $_SESSION['pseudo'] ?? 'Unknown',
            'error' => 'Player not found'
        ];
    }

    // Récupérer toutes les journées de la saison
    $max_day = get_last_day($season);

    $evolution = [
        'labels' => [],
        'points' => [],
        'rank' => [],
        'player_name' => $_SESSION['pseudo']
    ];

    // Pour chaque journée, calculer les points cumulés et le classement
    for ($day = 1; $day <= $max_day; $day++) {
        $evolution['labels'][] = "J" . $day;

        // Récupérer les points à cette journée en utilisant l'historique des pronostics
        $points = get_player_points_at_day($player_id, $season, $day);
        $evolution['points'][] = $points;

        // Récupérer le classement à cette journée
        $rank = get_player_rank_at_day($player_id, $season, $day, $team);
        $evolution['rank'][] = $rank;
    }

    return $evolution;
}

/**
 * Calcule les points d'un joueur jusqu'à une journée donnée
 */
function get_player_points_at_day($player_id, $season, $day) {
    global $pdo;

    // Compter les bons pronostics jusqu'à cette journée
    $sql = "SELECT COUNT(*) as correct_pronos
            FROM prono p
            INNER JOIN `match` m ON p.match = m.id
            INNER JOIN score s ON s.season = m.season AND s.day = m.day
            WHERE p.player = :player_id
            AND p.season = :season
            AND m.day <= :day
            AND s.team = p.team
            AND s.V = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':player_id' => $player_id,
        ':season' => $season,
        ':day' => $day
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Points = nombre de bons pronostics × 3 (ou selon votre système de points)
    // Note: ajuster selon le vrai système de calcul de points
    return intval($result['correct_pronos'] ?? 0) * 3;
}

/**
 * Récupère le classement d'un joueur à une journée donnée
 */
function get_player_rank_at_day($player_id, $season, $day, $team) {
    global $pdo;

    // Calculer le classement basé sur les points à cette journée
    $sql = "SELECT player_idx,
                   (SELECT COUNT(*)
                    FROM prono p2
                    INNER JOIN `match` m2 ON p2.match = m2.id
                    INNER JOIN score s2 ON s2.season = m2.season AND s2.day = m2.day AND s2.team = p2.team
                    WHERE p2.player = player.player_idx
                    AND p2.season = :season1
                    AND m2.day <= :day
                    AND s2.V = 1) as points
            FROM player
            WHERE season = :season2 AND team = :team AND status = 1
            ORDER BY points DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':season1' => $season,
        ':season2' => $season,
        ':day' => $day,
        ':team' => $team
    ]);

    $rank = 1;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['player_idx'] == $player_id) {
            return $rank;
        }
        $rank++;
    }

    return null;
}

/**
 * Récupère les données de comparaison de plusieurs joueurs
 */
function get_players_comparison_data($season, $player_ids) {
    global $pdo;

    if (empty($player_ids)) {
        return ['error' => 'Aucun joueur sélectionné'];
    }

    $max_day = get_last_day($season);

    $comparison = [
        'labels' => [],
        'datasets' => []
    ];

    // Créer les labels (journées)
    for ($day = 1; $day <= $max_day; $day++) {
        $comparison['labels'][] = "J" . $day;
    }

    // Pour chaque joueur
    foreach ($player_ids as $player_id) {
        // Récupérer les infos du joueur
        $stmt = $pdo->prepare("SELECT pseudo, team FROM player WHERE player_idx = :player_id AND season = :season");
        $stmt->execute([':player_id' => $player_id, ':season' => $season]);
        $player = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$player) continue;

        $data = [];
        for ($day = 1; $day <= $max_day; $day++) {
            $points = get_player_points_at_day($player_id, $season, $day);
            $data[] = $points;
        }

        $comparison['datasets'][] = [
            'label' => $player['pseudo'],
            'data' => $data,
            'player_id' => $player_id
        ];
    }

    return $comparison;
}

/**
 * Récupère l'évolution d'une équipe Top7
 */
function get_team_evolution_data($season, $team) {
    global $pdo;

    $max_day = get_last_day($season);

    $evolution = [
        'labels' => [],
        'points' => []
    ];

    for ($day = 1; $day <= $max_day; $day++) {
        $evolution['labels'][] = "J" . $day;

        // Somme des points de tous les joueurs de l'équipe
        $sql = "SELECT SUM(points) as team_points
                FROM (
                    SELECT player_idx,
                           (SELECT COUNT(*)
                            FROM prono p2
                            INNER JOIN `match` m2 ON p2.match = m2.id
                            INNER JOIN score s2 ON s2.season = m2.season AND s2.day = m2.day AND s2.team = p2.team
                            WHERE p2.player = player.player_idx
                            AND p2.season = :season
                            AND m2.day <= :day
                            AND s2.V = 1) * 3 as points
                    FROM player
                    WHERE season = :season AND team = :team AND status = 1
                ) as player_points";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':season' => $season,
            ':day' => $day,
            ':team' => $team
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $evolution['points'][] = intval($result['team_points'] ?? 0);
    }

    return $evolution;
}

/**
 * Récupère la liste des joueurs d'une équipe
 */
function get_players_list($season) {
    global $pdo;

    $team = $_SESSION['team'] ?? $_SESSION['top7team'] ?? null;

    if (!$team) {
        return [];
    }

    $sql = "SELECT player_idx, pseudo, point, `rank`
            FROM player
            WHERE season = :season AND team = :team AND status = 1
            ORDER BY `rank` ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':season' => $season, ':team' => $team]);

    $players = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $players[] = [
            'id' => $row['player_idx'],
            'name' => $row['pseudo'],
            'points' => $row['point'],
            'rank' => $row['rank']
        ];
    }

    return $players;
}
