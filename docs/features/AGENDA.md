# 📅 Team Agenda Feature

## Overview

The Team Agenda feature is a collaborative event management system that allows Top7 teams to propose events (friendly matches, watch parties, meetings) and manage player availability.

**Status:** ✅ Fully functional and tested

---

## Quick Start

### Access the Agenda
1. Login at `http://localhost/login` with your credentials
2. Navigate to the agenda using the **📅 AGENDA** button in the navigation menu
3. You should see the current month's events displayed as cards

### Test Credentials
- Email: `test2@topseven.fr`
- Password: `Passw0rd`

---

## Database Setup

The agenda feature requires two tables: `event` and `event_availability`.

### Create Tables

```sql
-- Create event table
CREATE TABLE IF NOT EXISTS event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team INT NOT NULL,
    created_by INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('match_amical', 'visionnage', 'reunion', 'autre') DEFAULT 'autre',
    proposed_date DATETIME NOT NULL,
    location VARCHAR(255),
    description TEXT,
    status ENUM('proposed', 'confirmed', 'cancelled') DEFAULT 'proposed',
    min_players INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team (team),
    INDEX idx_date (proposed_date)
);

-- Create event_availability table
CREATE TABLE IF NOT EXISTS event_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    player_id INT NOT NULL,
    status ENUM('available', 'maybe', 'unavailable') NOT NULL,
    comment TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event_player (event_id, player_id),
    INDEX idx_event (event_id),
    INDEX idx_player (player_id),
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE
);
```

### Run Setup via Docker

```bash
# Create tables
docker exec test-db-1 mysql -uroot -proot topseven -e "$(cat setup-tables.sql)"

# Or run directly:
docker exec test-db-1 mysql -uroot -proot topseven << 'EOF'
-- Paste SQL here
EOF
```

### Add Test Data

```sql
-- Insert sample events for team 1 (November-December 2025)
INSERT INTO event (team, created_by, title, type, proposed_date, location, description, status, min_players) VALUES
(1, 2, 'Visionnage match France-NZ', 'visionnage', '2025-11-22 20:00:00', 'Bar des Sports', 'On se retrouve pour regarder le match ensemble', 'confirmed', 3),
(1, 2, 'Match amical contre les Tigres', 'match_amical', '2025-11-25 15:00:00', 'Stade Municipal', 'Match amical pour préparer le prochain match officiel', 'proposed', 4),
(1, 2, 'Réunion tactique', 'reunion', '2025-11-28 18:30:00', 'Salle de réunion', 'Discussion sur les stratégies pour la fin de saison', 'proposed', 5),
(1, 2, 'Entraînement collectif', 'autre', '2025-12-05 19:00:00', 'Terrain synthétique', 'Entraînement en équipe avant le match important', 'proposed', 6);

-- Add availability responses
INSERT INTO event_availability (event_id, player_id, status, comment) VALUES
(1, 2, 'available', 'Je serai là !'),
(1, 3, 'available', 'OK pour moi'),
(1, 4, 'maybe', 'Pas sûr, je confirme demain'),
(2, 2, 'available', NULL),
(2, 3, 'available', 'Super idée !'),
(2, 4, 'available', NULL),
(2, 5, 'unavailable', 'Désolé, pas dispo ce soir'),
(3, 2, 'available', NULL),
(3, 3, 'maybe', 'Ça dépend de mon boulot');
```

---

## Bug Fixes Applied

### 1. Session Variable Names (Fixed ✅)

**Issue:** The agenda was using wrong session variable names.

**Fix:**
```php
// Before (wrong)
$player_id = $_SESSION['player_idx'];
$team = $_SESSION['team'];

// After (correct)
$player_id = $_SESSION['player']; // player, not player_idx
$team = $_SESSION['top7team']; // top7team, not team
```

**Files Fixed:**
- `/www/agenda.php:13-14`
- `/www/agenda_api.php:13-14`

### 2. Event Dates Updated (Fixed ✅)

**Problem:** Events were dated for 2024, but system date is 2025.

**Solution:** All test events updated to November/December 2025.

---

## Features

### For All Players

#### 1. Event Listing
- View all events for the current month
- See event status (proposed/confirmed/cancelled)
- View availability counts (Available/Maybe/Unavailable)
- Progress bar showing confirmation status
- Navigate between months with **← Mois précédent** / **Mois suivant →** buttons

#### 2. Event Details
- Click on any event card to view full details
- See all team members' availability responses
- View comments from team members
- Check event location, description, and requirements

#### 3. Availability Management
- Set your availability: ✅ **Disponible**, ⚠️ **Peut-être**, ❌ **Indisponible**
- Add optional comments
- Updates in real-time
- Visual feedback with color coding

### For Event Creators

#### 4. Event Creation
- Click **"+ Nouvel Événement"** button
- Fill in the form:
  - **Title** (required)
  - **Type** (required): Match amical, Visionnage, Réunion, Autre
  - **Date and time** (required)
  - **Location** (optional)
  - **Description** (optional)
  - **Minimum players** (1-7, default: 3) for auto-confirmation

#### 5. Event Management
- Manually confirm events
- Cancel events
- Delete events (creator only)
- Automatically marked as "Available" when creating an event

---

## Event Types

| Type | Icon | Usage Example |
|------|------|---------------|
| **Match amical** | 🏉 | Friendly matches between Top7 teams |
| **Visionnage** | 📺 | Watch Top 14 matches together at a bar |
| **Réunion** | 🤝 | Team meetings, strategy discussions |
| **Autre** | 📅 | Any other social events |

---

## Event Statuses

| Status | Badge | Description |
|--------|-------|-------------|
| **Proposed** | 🔵 Blue | Event created, waiting for confirmations |
| **Confirmed** | 🟢 Green | Enough players available, event confirmed |
| **Cancelled** | 🔴 Red | Event cancelled by creator |

### Automatic Confirmation Logic

An event automatically changes from "Proposed" to "Confirmed" when:
- The number of **available** players (✅) reaches the `min_players` threshold
- Default threshold: 3 players minimum

**Example:**
- Event created with `min_players = 4`
- 1st player available → Remains "Proposed"
- 2nd player available → Remains "Proposed"
- 3rd player available → Remains "Proposed"
- 4th player available → **Changes to "Confirmed"** ✅

The creator can also manually confirm an event before reaching this threshold.

---

## Availability Statuses

| Status | Icon | Color | Meaning |
|--------|------|-------|---------|
| **Disponible** | ✅ | Green | I will attend |
| **Peut-être** | ⚠️ | Yellow | Not sure about my availability |
| **Indisponible** | ❌ | Red | I cannot attend |

---

## API Endpoints

The agenda uses `agenda_api.php` with these actions:

### GET Requests

- `?action=list_events&month=YYYY-MM` - List all events for a month
- `?action=get_event&event_id=123` - Get event details
- `?action=get_availability_stats&event_id=123` - Get availability statistics

### POST Requests

- `action=create_event` - Create a new event
  - Parameters: `title`, `type`, `proposed_date`, `location`, `description`, `min_players`

- `action=update_event` - Update event status
  - Parameters: `event_id`, `status` (confirmed/cancelled)

- `action=delete_event` - Delete an event
  - Parameters: `event_id`

- `action=set_availability` - Set player availability
  - Parameters: `event_id`, `status` (available/maybe/unavailable), `comment`

### Response Format

```json
{
  "success": true,
  "events": [...],
  "event": {...},
  "error": "Error message if failed"
}
```

---

## File Structure

```
www/
├── agenda.php                # Main agenda page with UI
├── agenda_api.php            # API endpoints for agenda
└── styles/
    └── output.css           # Tailwind CSS (required)

tests/playwright/
└── test-agenda*.js          # Playwright test scripts
```

---

## Testing

### Automated Tests

Run the comprehensive Playwright test:

```bash
cd tests/playwright
node test-agenda.js
```

**Test Coverage:**
1. Login
2. Navigate to agenda page
3. Wait for events to load
4. View event details
5. Set availability
6. Test event creation modal
7. Fill and submit event form
8. Test month navigation

**Screenshots Generated:**
- `agenda-1-main.png` - Main page view
- `agenda-2-events-list.png` - Events list
- `agenda-3-event-details.png` - Event details modal
- `agenda-4-set-available.png` - After setting availability
- `agenda-5-create-modal.png` - Create event modal
- `agenda-6-form-filled.png` - Filled event form
- `agenda-7-after-create.png` - After creating event
- `agenda-8-next-month.png` - Next month view

### Manual Testing

See [Testing Guide](../testing/PLAYWRIGHT_GUIDE.md) for manual testing procedures.

---

## Troubleshooting

### Events Not Loading

1. **Check database tables exist:**
   ```bash
   docker exec test-db-1 mysql -uroot -proot topseven -e "SHOW TABLES LIKE 'event%';"
   ```

2. **Check test data exists:**
   ```bash
   docker exec test-db-1 mysql -uroot -proot topseven -e "SELECT * FROM event;"
   ```

3. **Check API response:**
   - Open browser DevTools (F12)
   - Navigate to http://localhost/agenda
   - Check Network tab for `agenda_api.php` requests
   - Look for errors in response

4. **Check PHP errors:**
   ```bash
   docker exec test-web-1 tail -50 /tmp/log_$(date +%Y%m%d).txt
   ```

### Session Issues

If you get "undefined index" errors for session variables:

1. **Verify you're logged in:**
   - Check browser has valid session cookie
   - Try logging out and logging in again

2. **Check session variables:**
   Create `/www/debug_session.php`:
   ```php
   <?php
   include("common.inc");
   check_session();
   echo "<pre>";
   print_r($_SESSION);
   echo "</pre>";
   ```
   Visit http://localhost/debug_session to see session contents

### JavaScript Not Executing

1. **Check browser console:**
   - F12 > Console tab
   - Look for JavaScript errors

2. **Check CSS is loading:**
   - Verify `styles/output.css` exists
   - Check Network tab shows 200 OK for CSS

3. **Check AJAX requests:**
   - Network tab > XHR filter
   - Look for `agenda_api.php` calls
   - Check response data

### "No events this month" Message

**Cause:** Wrong month selected or events in different month

**Solution:**
- Use month navigation buttons
- Verify event dates in database match the selected month
- Ensure events are dated for the current year (2025, not 2024)

### Modal Doesn't Open

**Cause:** JavaScript error or event card not clickable

**Solution:**
- Check browser console for errors
- Verify `styles/output.css` is loading (F12 > Network tab)
- Clear browser cache (Ctrl+F5)

---

## Design Notes

### Why List View Instead of Calendar Grid?

1. **Better Mobile Experience** - List scrolls naturally on mobile devices
2. **More Information** - Can show full event details immediately without extra clicks
3. **Simpler UI** - Easier to see all event information at once
4. **Better for Teams** - Shows availability counts prominently
5. **Responsive** - Works well on all screen sizes

### UI Design
- **Responsive** - Adapts to mobile, tablet, and desktop
- **Cards** - White cards with shadow on hover
- **Color-coded badges** - Status-based coloring (blue/green/red)
- **Progress bars** - Green bars showing availability ratio
- **Modal dialogs** - Centered with scroll capability
- **Accessibility** - Universal icons (emojis), proper color contrast, clear labels

---

## Security

### Implemented Protections
- ✅ Session required for all actions (`check_session()`)
- ✅ Team verification (players can only see their team's events)
- ✅ Creator verification (only creators can modify/delete events)
- ✅ Type and status validation (using enums)
- ✅ SQL injection protection (parameterized PDO queries)
- ✅ HTML escaping in JavaScript output

### Not Yet Implemented (Future Enhancements)
- ⏳ CSRF protection (tokens)
- ⏳ Rate limiting
- ⏳ Audit logs

---

## Performance

- **Dynamic loading** - Only events for the displayed month are fetched
- **AJAX** - No page reloads, smooth interactions
- **Optimized SQL** - Indexes on `team`, `proposed_date`, `status`
- **Cascade DELETE** - Automatic cleanup of availabilities when events are deleted
- **Minimal queries** - Efficient data fetching

---

## Future Enhancements

### 1. Notifications
- In-app notifications for new events
- Badge showing unresponded events count
- Email notifications (excluded from initial version)

### 2. Calendar Integrations
- Export to iCal format
- Google Calendar sync
- External calendar synchronization

### 3. Advanced Features
- Multi-date polling (like Doodle)
- Automatic reminders (7 days, 1 day before)
- Past events history
- Player participation statistics
- Recurring events (weekly, monthly)

### 4. Social Features
- Event discussion threads
- Photo uploads for events
- Post-event feedback/ratings

### 5. Mobile App
- Progressive Web App (PWA) for offline access
- Native push notifications
- Calendar widget

---

## Maintenance

### Tables to Backup
- `event`
- `event_availability`

### Recommended Cleanup

Clean up old events periodically:

```sql
-- Delete cancelled events older than 6 months
DELETE FROM event
WHERE status = 'cancelled'
AND proposed_date < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Archive past events older than 1 year (optional)
-- Consider moving to archive table instead of deleting
```

---

## Compatibility

- **PHP:** 7.1+ (tested with PHP 8.3)
- **MySQL:** 5.6+ (InnoDB engine for foreign keys)
- **Browsers:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Devices:** Mobile iOS/Android, tablets, desktop

---

## Support

For issues or questions:
1. Check test screenshots in `tests/playwright/screenshots/`
2. Review PHP logs: `docker logs test-web-1`
3. Check database: `docker exec test-db-1 mysql ...`
4. Test API endpoints with curl or browser DevTools

---

## Changelog

### Version 1.0 (2025-11-17)
- ✨ Initial release of agenda system
- ✨ Event management (CRUD operations)
- ✨ Availability system (3 states)
- ✨ Automatic confirmation logic
- ✨ Modern responsive interface
- ✨ Month navigation
- ✨ Modal dialogs for creation/details
- ✨ Integration with main navigation

### Fixes (2025-11-20)
- 🐛 Fixed session variable names (player_idx → player, team → top7team)
- 🐛 Updated test event dates to 2025
- ✅ Comprehensive testing with Playwright
- 📝 Complete documentation

---

**Documentation:** See also [Testing Guide](../testing/PLAYWRIGHT_GUIDE.md) and [API Documentation](../development/API.md)
