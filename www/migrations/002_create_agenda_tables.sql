-- Migration pour créer les tables de l'agenda de disponibilités
-- Date: 2025-11-17

-- Table des événements
CREATE TABLE IF NOT EXISTS `event` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `team` SMALLINT(6) NOT NULL COMMENT 'Équipe Top7',
    `created_by` MEDIUMINT(9) NOT NULL COMMENT 'ID du joueur créateur',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `type` ENUM('match_amical', 'visionnage', 'reunion', 'autre') NOT NULL DEFAULT 'autre',
    `proposed_date` DATETIME NOT NULL COMMENT 'Date/heure proposée',
    `location` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('proposed', 'confirmed', 'cancelled') NOT NULL DEFAULT 'proposed',
    `min_players` TINYINT(4) DEFAULT 3 COMMENT 'Nombre minimum de joueurs pour confirmer',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_team` (`team`),
    KEY `idx_date` (`proposed_date`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des disponibilités
CREATE TABLE IF NOT EXISTS `event_availability` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) NOT NULL,
    `player_id` MEDIUMINT(9) NOT NULL,
    `status` ENUM('available', 'unavailable', 'maybe') NOT NULL,
    `comment` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_event_player` (`event_id`, `player_id`),
    KEY `idx_event` (`event_id`),
    KEY `idx_player` (`player_id`),
    CONSTRAINT `fk_availability_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
