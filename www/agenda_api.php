<?php
/**
 * API Agenda - Gestion des événements et disponibilités
 */

include("common.inc");
check_session();
init_sql();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$player_id = $_SESSION['player']; // player, not player_idx in session
$team = $_SESSION['top7team']; // top7team, not team in session
$season = $_SESSION['season'];

try {
    switch ($action) {
        case 'list_events':
            $month = $_GET['month'] ?? date('Y-m');
            echo json_encode(list_events($team, $month));
            break;

        case 'get_event':
            $event_id = intval($_GET['event_id'] ?? 0);
            echo json_encode(get_event_details($event_id, $team));
            break;

        case 'create_event':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }
            echo json_encode(create_event($_POST, $player_id, $team));
            break;

        case 'update_event':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }
            $event_id = intval($_POST['event_id'] ?? 0);
            echo json_encode(update_event($event_id, $_POST, $player_id, $team));
            break;

        case 'delete_event':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }
            $event_id = intval($_POST['event_id'] ?? 0);
            echo json_encode(delete_event($event_id, $player_id, $team));
            break;

        case 'set_availability':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }
            $event_id = intval($_POST['event_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $comment = $_POST['comment'] ?? '';
            echo json_encode(set_availability($event_id, $player_id, $status, $comment));
            break;

        case 'get_availability_stats':
            $event_id = intval($_GET['event_id'] ?? 0);
            echo json_encode(get_availability_stats($event_id));
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
 * Liste les événements d'une équipe pour un mois donné
 */
function list_events($team, $month) {
    global $pdo;

    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));

    $sql = "SELECT e.*,
                   p.pseudo as creator_name,
                   (SELECT COUNT(*) FROM event_availability ea
                    WHERE ea.event_id = e.id AND ea.status = 'available') as available_count,
                   (SELECT COUNT(*) FROM event_availability ea
                    WHERE ea.event_id = e.id AND ea.status = 'unavailable') as unavailable_count,
                   (SELECT COUNT(*) FROM event_availability ea
                    WHERE ea.event_id = e.id AND ea.status = 'maybe') as maybe_count,
                   (SELECT COUNT(*) FROM event_availability ea
                    WHERE ea.event_id = e.id) as total_responses
            FROM event e
            INNER JOIN player p ON e.created_by = p.player_idx
            WHERE e.team = :team
            AND e.proposed_date BETWEEN :start_date AND :end_date
            AND e.status != 'cancelled'
            ORDER BY e.proposed_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':team' => $team,
        ':start_date' => $start_date,
        ':end_date' => $end_date . ' 23:59:59'
    ]);

    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = format_event($row);
    }

    return ['success' => true, 'events' => $events];
}

/**
 * Récupère les détails d'un événement avec les disponibilités
 */
function get_event_details($event_id, $team) {
    global $pdo, $player_id;

    // Vérifier que l'événement appartient à l'équipe
    $sql = "SELECT e.*,
                   p.pseudo as creator_name,
                   p.player_idx as creator_id
            FROM event e
            INNER JOIN player p ON e.created_by = p.player_idx
            WHERE e.id = :event_id AND e.team = :team";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':event_id' => $event_id, ':team' => $team]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    // Récupérer les disponibilités
    $sql = "SELECT ea.*,
                   p.pseudo as player_name,
                   p.player_idx
            FROM event_availability ea
            INNER JOIN player p ON ea.player_id = p.player_idx
            WHERE ea.event_id = :event_id
            ORDER BY
                CASE ea.status
                    WHEN 'available' THEN 1
                    WHEN 'maybe' THEN 2
                    WHEN 'unavailable' THEN 3
                END,
                p.pseudo ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':event_id' => $event_id]);

    $availabilities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $availabilities[] = [
            'player_id' => $row['player_idx'],
            'player_name' => $row['player_name'],
            'status' => $row['status'],
            'comment' => $row['comment'],
            'updated_at' => $row['updated_at']
        ];
    }

    $event['availabilities'] = $availabilities;

    return ['success' => true, 'event' => format_event($event)];
}

/**
 * Crée un nouvel événement
 */
function create_event($data, $player_id, $team) {
    global $pdo;

    // Validation
    if (empty($data['title'])) {
        throw new Exception('Le titre est obligatoire');
    }
    if (empty($data['proposed_date'])) {
        throw new Exception('La date est obligatoire');
    }

    $type = $data['type'] ?? 'autre';
    $valid_types = ['match_amical', 'visionnage', 'reunion', 'autre'];
    if (!in_array($type, $valid_types)) {
        $type = 'autre';
    }

    $min_players = intval($data['min_players'] ?? 3);
    if ($min_players < 1) $min_players = 1;
    if ($min_players > 7) $min_players = 7;

    $sql = "INSERT INTO event (team, created_by, title, description, type, proposed_date, location, min_players)
            VALUES (:team, :created_by, :title, :description, :type, :proposed_date, :location, :min_players)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':team' => $team,
        ':created_by' => $player_id,
        ':title' => $data['title'],
        ':description' => $data['description'] ?? '',
        ':type' => $type,
        ':proposed_date' => $data['proposed_date'],
        ':location' => $data['location'] ?? '',
        ':min_players' => $min_players
    ]);

    if (!$result) {
        throw new Exception('Erreur lors de la création de l\'événement');
    }

    $event_id = $pdo->lastInsertId();

    // Le créateur est automatiquement disponible
    set_availability($event_id, $player_id, 'available', 'Créateur de l\'événement');

    return ['success' => true, 'event_id' => $event_id, 'message' => 'Événement créé avec succès'];
}

/**
 * Met à jour un événement
 */
function update_event($event_id, $data, $player_id, $team) {
    global $pdo;

    // Vérifier que le joueur est le créateur
    $stmt = $pdo->prepare("SELECT created_by FROM event WHERE id = :id AND team = :team");
    $stmt->execute([':id' => $event_id, ':team' => $team]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    if ($event['created_by'] != $player_id) {
        throw new Exception('Seul le créateur peut modifier cet événement');
    }

    $fields = [];
    $params = [':id' => $event_id];

    if (isset($data['title'])) {
        $fields[] = "title = :title";
        $params[':title'] = $data['title'];
    }
    if (isset($data['description'])) {
        $fields[] = "description = :description";
        $params[':description'] = $data['description'];
    }
    if (isset($data['type'])) {
        $fields[] = "type = :type";
        $params[':type'] = $data['type'];
    }
    if (isset($data['proposed_date'])) {
        $fields[] = "proposed_date = :proposed_date";
        $params[':proposed_date'] = $data['proposed_date'];
    }
    if (isset($data['location'])) {
        $fields[] = "location = :location";
        $params[':location'] = $data['location'];
    }
    if (isset($data['status'])) {
        $fields[] = "status = :status";
        $params[':status'] = $data['status'];
    }
    if (isset($data['min_players'])) {
        $fields[] = "min_players = :min_players";
        $params[':min_players'] = intval($data['min_players']);
    }

    if (empty($fields)) {
        throw new Exception('Aucune donnée à mettre à jour');
    }

    $sql = "UPDATE event SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return ['success' => true, 'message' => 'Événement mis à jour'];
}

/**
 * Supprime un événement
 */
function delete_event($event_id, $player_id, $team) {
    global $pdo;

    // Vérifier que le joueur est le créateur
    $stmt = $pdo->prepare("SELECT created_by FROM event WHERE id = :id AND team = :team");
    $stmt->execute([':id' => $event_id, ':team' => $team]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception('Événement non trouvé');
    }

    if ($event['created_by'] != $player_id) {
        throw new Exception('Seul le créateur peut supprimer cet événement');
    }

    // Supprimer l'événement (les disponibilités seront supprimées via CASCADE)
    $stmt = $pdo->prepare("DELETE FROM event WHERE id = :id");
    $stmt->execute([':id' => $event_id]);

    return ['success' => true, 'message' => 'Événement supprimé'];
}

/**
 * Définit ou met à jour la disponibilité d'un joueur
 */
function set_availability($event_id, $player_id, $status, $comment = '') {
    global $pdo;

    // Vérifier que l'événement existe
    $stmt = $pdo->prepare("SELECT id FROM event WHERE id = :id");
    $stmt->execute([':id' => $event_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Événement non trouvé');
    }

    // Valider le statut
    $valid_statuses = ['available', 'unavailable', 'maybe'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Statut invalide');
    }

    // Insérer ou mettre à jour
    $sql = "INSERT INTO event_availability (event_id, player_id, status, comment)
            VALUES (:event_id, :player_id, :status, :comment)
            ON DUPLICATE KEY UPDATE status = :status, comment = :comment";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':event_id' => $event_id,
        ':player_id' => $player_id,
        ':status' => $status,
        ':comment' => $comment
    ]);

    // Vérifier si l'événement doit être confirmé automatiquement
    check_auto_confirm($event_id);

    return ['success' => true, 'message' => 'Disponibilité enregistrée'];
}

/**
 * Récupère les statistiques de disponibilité pour un événement
 */
function get_availability_stats($event_id) {
    global $pdo;

    $sql = "SELECT
                COUNT(CASE WHEN status = 'available' THEN 1 END) as available,
                COUNT(CASE WHEN status = 'unavailable' THEN 1 END) as unavailable,
                COUNT(CASE WHEN status = 'maybe' THEN 1 END) as maybe,
                COUNT(*) as total
            FROM event_availability
            WHERE event_id = :event_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':event_id' => $event_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    return ['success' => true, 'stats' => $stats];
}

/**
 * Vérifie si un événement doit être confirmé automatiquement
 */
function check_auto_confirm($event_id) {
    global $pdo;

    $sql = "SELECT e.min_players, e.status,
                   COUNT(CASE WHEN ea.status = 'available' THEN 1 END) as available_count
            FROM event e
            LEFT JOIN event_availability ea ON e.id = ea.event_id
            WHERE e.id = :event_id
            GROUP BY e.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':event_id' => $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event && $event['status'] == 'proposed' && $event['available_count'] >= $event['min_players']) {
        // Confirmer automatiquement
        $stmt = $pdo->prepare("UPDATE event SET status = 'confirmed' WHERE id = :event_id");
        $stmt->execute([':event_id' => $event_id]);
    }
}

/**
 * Formate un événement pour la réponse JSON
 */
function format_event($event) {
    return [
        'id' => $event['id'],
        'title' => $event['title'],
        'description' => $event['description'] ?? '',
        'type' => $event['type'],
        'type_label' => get_type_label($event['type']),
        'proposed_date' => $event['proposed_date'],
        'location' => $event['location'] ?? '',
        'status' => $event['status'],
        'status_label' => get_status_label($event['status']),
        'min_players' => $event['min_players'] ?? 3,
        'creator_name' => $event['creator_name'] ?? '',
        'creator_id' => $event['creator_id'] ?? $event['created_by'],
        'created_at' => $event['created_at'] ?? '',
        'available_count' => $event['available_count'] ?? 0,
        'unavailable_count' => $event['unavailable_count'] ?? 0,
        'maybe_count' => $event['maybe_count'] ?? 0,
        'total_responses' => $event['total_responses'] ?? 0,
        'availabilities' => $event['availabilities'] ?? []
    ];
}

/**
 * Retourne le label d'un type d'événement
 */
function get_type_label($type) {
    $labels = [
        'match_amical' => '🏉 Match amical',
        'visionnage' => '📺 Visionnage',
        'reunion' => '🤝 Réunion',
        'autre' => '📅 Autre'
    ];
    return $labels[$type] ?? $type;
}

/**
 * Retourne le label d'un statut d'événement
 */
function get_status_label($status) {
    $labels = [
        'proposed' => 'Proposé',
        'confirmed' => 'Confirmé',
        'cancelled' => 'Annulé'
    ];
    return $labels[$status] ?? $status;
}
