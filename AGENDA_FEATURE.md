# 📅 Nouvelle Fonctionnalité : Agenda d'Équipe

## Vue d'ensemble

Cette fonctionnalité ajoute un système d'agenda collaboratif permettant aux équipes Top7 de proposer des événements (matchs amicaux, visionnages, réunions) et de gérer les disponibilités des joueurs.

## Fichiers Créés/Modifiés

### 1. `/www/migrations/002_create_agenda_tables.sql`
Migration SQL pour créer les tables nécessaires :

#### Table `event`
Stocke les événements proposés par les équipes.

**Colonnes :**
- `id` - Identifiant unique
- `team` - Équipe Top7 (référence à table team)
- `created_by` - Joueur créateur (référence à table player)
- `title` - Titre de l'événement
- `description` - Description détaillée
- `type` - Type : match_amical, visionnage, reunion, autre
- `proposed_date` - Date/heure proposée
- `location` - Lieu (optionnel)
- `status` - Statut : proposed, confirmed, cancelled
- `min_players` - Nombre minimum de joueurs pour confirmation automatique
- `created_at`, `updated_at` - Timestamps

#### Table `event_availability`
Stocke les réponses de disponibilité des joueurs.

**Colonnes :**
- `id` - Identifiant unique
- `event_id` - Référence à l'événement (CASCADE DELETE)
- `player_id` - Joueur concerné
- `status` - Statut : available, unavailable, maybe
- `comment` - Commentaire optionnel
- `updated_at` - Timestamp de mise à jour

**Contrainte unique** : Un joueur ne peut avoir qu'une seule réponse par événement.

### 2. `/www/agenda_api.php`
API RESTful pour gérer les événements et disponibilités.

**Endpoints disponibles :**

| Action | Méthode | Paramètres | Description |
|--------|---------|-----------|-------------|
| `list_events` | GET | `month` (YYYY-MM) | Liste les événements du mois |
| `get_event` | GET | `event_id` | Détails d'un événement avec disponibilités |
| `create_event` | POST | Form data | Crée un nouvel événement |
| `update_event` | POST | `event_id`, form data | Modifie un événement (créateur seulement) |
| `delete_event` | POST | `event_id` | Supprime un événement (créateur seulement) |
| `set_availability` | POST | `event_id`, `status`, `comment` | Définit sa disponibilité |
| `get_availability_stats` | GET | `event_id` | Statistiques de disponibilité |

**Sécurité :**
- Vérification de session obligatoire
- Seul le créateur peut modifier/supprimer un événement
- Validation des types et statuts
- Requêtes SQL préparées (PDO)

**Logique de confirmation automatique :**
Quand le nombre de joueurs disponibles atteint `min_players`, l'événement passe automatiquement de "Proposé" à "Confirmé".

### 3. `/www/agenda.php`
Page principale de l'agenda avec interface moderne.

**Fonctionnalités :**

#### Navigation Mensuelle
- Boutons "Mois précédent" / "Mois suivant"
- Affichage du mois en cours

#### Liste des Événements
Affichage en cartes avec :
- Titre et type d'événement (avec icône)
- Date, heure, lieu
- Statut (Proposé / Confirmé / Annulé)
- Compteurs de disponibilités (✅ / ⚠️ / ❌)
- Barre de progression visuelle
- Ratio joueurs disponibles / requis

#### Modal de Création d'Événement
Formulaire complet avec :
- **Titre** (requis)
- **Type** : Match amical 🏉, Visionnage 📺, Réunion 🤝, Autre 📅
- **Date et heure** (requis)
- **Lieu** (optionnel)
- **Description** (optionnelle)
- **Nombre minimum de joueurs** (1-7, défaut: 3)

#### Modal de Détails d'Événement
- Informations complètes de l'événement
- **Section "Ma disponibilité"** :
  - 3 boutons : ✅ Disponible / ⚠️ Peut-être / ❌ Indisponible
  - Champ commentaire optionnel
  - Mise en surbrillance du choix actuel
- **Liste des réponses de l'équipe** :
  - Joueurs disponibles en premier (fond vert)
  - Puis "peut-être" (fond jaune)
  - Puis indisponibles (fond rouge)
  - Affichage des commentaires
- **Actions du créateur** :
  - Supprimer l'événement
  - Confirmer manuellement
  - Annuler l'événement

### 4. `/www/common.inc` (modifié)
Ajout du bouton **"📅 AGENDA"** dans la navigation principale.

Le bouton apparaît sur :
- Page principale (display)
- Page statistiques
- Page records
- Toutes les autres pages

## Base de Données

### Migration à Exécuter

```bash
# Se connecter à MySQL
mysql -u [user] -p [database]

# Exécuter la migration
source /home/user/top7/www/migrations/002_create_agenda_tables.sql
```

Ou via l'interface d'administration PHP/MySQL.

## Utilisation

### Pour Tous les Joueurs

1. **Accéder à l'agenda**
   - Cliquer sur le bouton "📅 AGENDA" dans la navigation

2. **Consulter les événements**
   - Naviguer entre les mois
   - Cliquer sur un événement pour voir les détails

3. **Indiquer sa disponibilité**
   - Ouvrir un événement
   - Cliquer sur ✅ Disponible, ⚠️ Peut-être ou ❌ Indisponible
   - Ajouter un commentaire (optionnel)

### Pour Créateurs d'Événements

4. **Créer un événement**
   - Cliquer sur "+ Nouvel Événement"
   - Remplir le formulaire
   - Valider

5. **Gérer ses événements**
   - Le créateur est automatiquement marqué "Disponible"
   - Peut modifier, confirmer ou annuler l'événement
   - Peut supprimer l'événement

## Types d'Événements

| Type | Icône | Exemple d'usage |
|------|-------|-----------------|
| **Match amical** | 🏉 | Organiser un match entre équipes Top7 |
| **Visionnage** | 📺 | Regarder un match du Top 14 ensemble au bar |
| **Réunion** | 🤝 | Réunion d'équipe, stratégie |
| **Autre** | 📅 | Tout autre événement social |

## Statuts d'Événements

| Statut | Badge | Description |
|--------|-------|-------------|
| **Proposé** | 🔵 Bleu | Événement en attente de confirmations |
| **Confirmé** | 🟢 Vert | Assez de joueurs disponibles, événement confirmé |
| **Annulé** | 🔴 Rouge | Événement annulé par le créateur |

## Statuts de Disponibilité

| Statut | Icône | Couleur | Signification |
|--------|-------|---------|---------------|
| **Disponible** | ✅ | Vert | Je serai présent |
| **Peut-être** | ⚠️ | Jaune | Pas sûr de ma disponibilité |
| **Indisponible** | ❌ | Rouge | Je ne pourrai pas venir |

## Confirmation Automatique

L'événement passe automatiquement en statut "Confirmé" quand :
- Le nombre de joueurs **disponibles** (✅) atteint le `min_players` défini
- Par défaut : 3 joueurs minimum

Exemple :
- Événement créé avec `min_players = 4`
- 1er joueur disponible → Reste "Proposé"
- 2ème joueur disponible → Reste "Proposé"
- 3ème joueur disponible → Reste "Proposé"
- 4ème joueur disponible → **Passe en "Confirmé"** ✅

Le créateur peut aussi confirmer manuellement avant ce seuil.

## Design et Interface

### Responsive
- Adapté mobile, tablette, desktop
- Modals centrées avec scroll

### Couleurs et Visuels
- **Cartes blanches** avec ombre au survol
- **Badges colorés** selon le statut
- **Barre de progression verte** pour les disponibilités
- **Boutons bleus** pour les actions principales

### Accessibilité
- Icônes universelles (émojis)
- Contrastes de couleurs respectés
- Formulaires avec labels clairs

## Performances

- **Chargement dynamique** : Seuls les événements du mois affiché sont récupérés
- **AJAX** : Pas de rechargement de page
- **SQL optimisé** : Index sur `team`, `proposed_date`, `status`
- **Cascade DELETE** : Suppression automatique des disponibilités quand un événement est supprimé

## Sécurité

### Vérifications Implémentées
- ✅ Session obligatoire pour toutes les actions
- ✅ Vérification que l'événement appartient à l'équipe du joueur
- ✅ Seul le créateur peut modifier/supprimer
- ✅ Validation des types et statuts (enums)
- ✅ Protection SQL injection (requêtes préparées PDO)
- ✅ Échappement HTML dans l'affichage JavaScript

### Non Implémenté (Évolutions Futures)
- ⏳ Protection CSRF (tokens)
- ⏳ Rate limiting
- ⏳ Logs d'audit

## Évolutions Futures Possibles

### 1. Notifications
- ~~Email~~ (exclu de cette version)
- Notifications in-app
- Badge de notification (nombre d'événements non répondus)

### 2. Intégrations Calendrier
- Export iCal
- Export Google Calendar
- Synchronisation avec calendriers externes

### 3. Fonctionnalités Avancées
- Sondage de dates multiples (vrai Doodle)
- Rappels automatiques J-7, J-1
- Historique des événements passés
- Statistiques de participation par joueur
- Récurrence d'événements (hebdomadaire, mensuel)

### 4. Social
- Fil de discussion par événement
- Photos d'événements
- Notation/feedback post-événement

### 5. Mobile
- PWA pour accès hors-ligne
- Notifications push natives
- Widget calendrier

## Compatibilité

- **PHP** : 7.1+ (testé avec PHP 8.3)
- **MySQL** : 5.6+ (InnoDB pour foreign keys)
- **Navigateurs** : Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Responsive** : Mobile iOS/Android, tablettes, desktop

## Tests Manuels

### À Tester

1. **Création d'événement**
   ```
   - [ ] Créer un événement avec tous les champs remplis
   - [ ] Créer un événement avec champs minimaux (titre + date)
   - [ ] Vérifier que le créateur est automatiquement "Disponible"
   ```

2. **Disponibilités**
   ```
   - [ ] Marquer sa disponibilité sur plusieurs événements
   - [ ] Changer sa disponibilité (disponible → indisponible)
   - [ ] Ajouter un commentaire
   ```

3. **Confirmation automatique**
   ```
   - [ ] Créer événement avec min_players = 3
   - [ ] 2 joueurs se marquent disponibles → Reste "Proposé"
   - [ ] 3ème joueur se marque disponible → Passe "Confirmé"
   ```

4. **Permissions**
   ```
   - [ ] Essayer de supprimer l'événement d'un autre joueur (devrait échouer)
   - [ ] Supprimer son propre événement
   - [ ] Confirmer/annuler manuellement son événement
   ```

5. **Navigation**
   ```
   - [ ] Changer de mois (précédent/suivant)
   - [ ] Vérifier affichage quand aucun événement
   - [ ] Vérifier bouton "Agenda" dans toutes les pages
   ```

6. **Responsive**
   ```
   - [ ] Tester sur mobile (portrait/paysage)
   - [ ] Tester sur tablette
   - [ ] Tester modals sur petit écran
   ```

## Maintenance

### Tables à Sauvegarder
- `event`
- `event_availability`

### Nettoyage Recommandé
Événements anciens (> 6 mois) peuvent être archivés ou supprimés :

```sql
-- Supprimer événements annulés de plus de 6 mois
DELETE FROM event
WHERE status = 'cancelled'
AND proposed_date < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Archiver événements passés de plus d'un an
-- (à adapter selon besoin)
```

## Support

Pour tout problème :
1. Vérifier que les tables sont créées (migration SQL)
2. Vérifier les permissions MySQL (FOREIGN KEYS)
3. Vérifier les logs PHP (`error_log`)
4. Consulter la console JavaScript du navigateur

## Changelog

### Version 1.0 (2025-11-17)
- ✨ Création du système d'agenda
- ✨ Gestion des événements (CRUD)
- ✨ Système de disponibilités (3 états)
- ✨ Confirmation automatique
- ✨ Interface moderne et responsive
- ✨ Navigation mensuelle
- ✨ Modal de création/détails
- ✨ Intégration dans la navigation principale
