# Team Agenda Feature - Setup & Testing Guide

This guide explains how to set up and test the Team Agenda feature.

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
    INDEX idx_player (player_id)
);
```

### Add Test Data

```sql
-- Insert sample events for team 1 (November-December 2025)
-- Note: Adjust dates if needed to match current system date
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

### Run Setup via Docker

```bash
# Create tables
docker exec test-db-1 mysql -uroot -proot topseven -e "$(cat setup-tables.sql)"

# Or run directly:
docker exec test-db-1 mysql -uroot -proot topseven << 'EOF'
-- Paste SQL here
EOF
```

## Bug Fixes Applied

### 1. Session Variable Names (Fixed)

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

## Feature Overview

### User Features

1. **Event Listing**
   - View all events for the current month
   - See event status (proposed/confirmed/cancelled)
   - View availability counts (Available/Maybe/Unavailable)
   - Progress bar showing confirmation status

2. **Event Details**
   - Click on any event to view full details
   - See all team members' availability responses
   - View comments from team members
   - Check event location, description, and requirements

3. **Availability Management**
   - Set your availability: ✅ Available, ⚠️ Maybe, ❌ Unavailable
   - Add optional comments
   - Updates in real-time
   - Visual feedback with color coding

4. **Event Creation (Captain/Creator)**
   - Create new events with full form
   - Set event type (Match amical, Visionnage, Réunion, Autre)
   - Choose date, time, and location
   - Set minimum players required for auto-confirmation
   - Add description

5. **Event Management (Creator Only)**
   - Manually confirm events
   - Cancel events
   - Delete events

6. **Month Navigation**
   - Navigate between months
   - Events filter automatically

### Event Types

- 🏉 **Match amical** - Friendly matches
- 📺 **Visionnage** - Watch parties
- 🤝 **Réunion** - Team meetings
- 📅 **Autre** - Other events

### Event Statuses

- **Proposed** (Blue) - Event created, waiting for responses
- **Confirmed** (Green) - Event confirmed (manually or auto-confirmed when min_players met)
- **Cancelled** (Red) - Event cancelled

## Testing with Playwright

### Comprehensive Test (`test-agenda.js`)

Tests all aspects of the agenda feature:

```bash
node test-agenda.js
```

**Test Steps:**
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

```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();

  // Login
  await page.goto('http://localhost/login');
  await page.fill('input[name="login"]', 'test2@topseven.fr');
  await page.fill('input[name="password"]', 'Passw0rd');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);

  // Go to agenda
  await page.goto('http://localhost/agenda');

  // Wait and observe
  await page.waitForTimeout(5000);

  // Click first event
  await page.locator('.bg-white.rounded-lg.shadow').first().click();
  await page.waitForTimeout(2000);

  // Set availability
  await page.locator('button:has-text("Disponible")').click();
  await page.waitForTimeout(2000);

  // Keep browser open for inspection
  await page.pause();
})();
```

## API Endpoints

The agenda uses `agenda_api.php` with these actions:

### GET Requests

- `?action=list_events&month=YYYY-MM` - List all events for a month
- `?action=get_event&event_id=123` - Get event details
- `?action=get_availability_stats&event_id=123` - Get availability statistics

### POST Requests

- `action=create_event` - Create a new event
  - `title`, `type`, `proposed_date`, `location`, `description`, `min_players`

- `action=update_event` - Update event status
  - `event_id`, `status` (confirmed/cancelled)

- `action=delete_event` - Delete an event
  - `event_id`

- `action=set_availability` - Set player availability
  - `event_id`, `status` (available/maybe/unavailable), `comment`

### Response Format

```json
{
  "success": true,
  "events": [...],
  "event": {...},
  "error": "Error message if failed"
}
```

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

## File Structure

```
www/
├── agenda.php                # Main agenda page with UI
├── agenda_api.php            # API endpoints for agenda
└── styles/
    └── output.css           # Tailwind CSS (required)

test-agenda.js               # Playwright test script
```

## Security Notes

- All API endpoints require authentication (`check_session()`)
- Player can only modify their own availability
- Only event creator can delete/update events
- Team members can only see events for their team
- SQL queries use parameterized statements

## Future Enhancements

Possible improvements:
- Email notifications for new events
- Automatic reminders 24h before events
- Export to calendar (iCal format)
- Recurring events
- Event comments/discussion thread
- File attachments (location maps, documents)
- Integration with match schedule

## Support

For issues or questions:
- Check screenshots in `test-screenshots/`
- Review PHP logs: `docker logs test-web-1`
- Check database: `docker exec test-db-1 mysql ...`
- Test API endpoints with curl or Postman

---

**Status:** ✅ Feature working, test data added, documentation complete
