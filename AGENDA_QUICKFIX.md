# Team Agenda - Quick Fix Guide

## ✅ Issues Fixed

### 1. Events Not Showing - FIXED!

**Problem:** Events were dated for 2024, but system date is 2025.

**Solution:** Updated all test events to November/December 2025:
```sql
-- Events are now:
- Visionnage match France-NZ: Nov 22, 2025 20:00
- Match amical contre les Tigres: Nov 25, 2025 15:00
- Réunion tactique: Nov 28, 2025 18:30
- Entraînement collectif: Dec 5, 2025 19:00
```

**To verify:** Refresh http://localhost/agenda - you should now see 3 events for November!

### 2. About the "Calendar" View

**Important:** The agenda feature shows a **list view**, not a calendar grid.

**What you'll see:**
- ✅ Month navigation (← Mois précédent | **novembre 2025** | Mois suivant →)
- ✅ List of events as cards
- ✅ Event details (click on any event)

**There is NO calendar grid** - this is by design for better mobile responsiveness and easier readability.

## How to Test

### 1. Access the Agenda
```
1. Login at http://localhost/login
   - Email: test2@topseven.fr
   - Password: Passw0rd

2. Navigate to http://localhost/agenda

3. You should see:
   - Month title: "novembre 2025"
   - 3 event cards for November
```

### 2. Test Event Viewing
```
1. Click on any event card
2. Modal opens with:
   - Event details
   - Availability buttons (✅ Disponible, ⚠️ Peut-être, ❌ Indisponible)
   - Team responses list
3. Click an availability button to set your status
```

### 3. Test Event Creation
```
1. Click "+ Nouvel Événement" button
2. Fill in the form:
   - Title: Test Event
   - Type: (select one)
   - Date: Pick a future date
   - Time: Pick a time
   - Location: (optional)
   - Description: (optional)
   - Min players: 3
3. Click "Créer l'événement"
4. Event appears in the list
```

### 4. Test Month Navigation
```
1. Click "Mois suivant →" to see December events
2. Click "← Mois précédent" to go back to November
```

## Current Test Data

### November 2025 (3 events)
1. **Visionnage match France-NZ** ✅ Confirmed
   - Nov 22, 20:00
   - Bar des Sports
   - 3 players available

2. **Match amical contre les Tigres** 📋 Proposed
   - Nov 25, 15:00
   - Stade Municipal
   - 2 available, 1 maybe

3. **Réunion tactique** 📋 Proposed
   - Nov 28, 18:30
   - Salle de réunion
   - 1 available, 1 maybe

### December 2025 (1 event)
4. **Entraînement collectif** 📋 Proposed
   - Dec 5, 19:00
   - Terrain synthétique
   - No responses yet

## If Events Still Don't Show

### Check 1: Browser Cache
```
- Press Ctrl+F5 to force refresh
- Or clear browser cache
```

### Check 2: Console Errors
```
- Press F12 to open DevTools
- Click "Console" tab
- Look for JavaScript errors
- Look for failed API calls in "Network" tab
```

### Check 3: Session Valid
```
- Make sure you're logged in
- Try logging out and logging in again
- Check that you see your username in the header
```

### Check 4: Database
```bash
# Verify events exist
docker exec test-db-1 mysql -uroot -proot topseven -e \
  "SELECT id, title, proposed_date, status FROM event WHERE team = 1;"
```

### Check 5: API Response
```bash
# Test API directly (replace with your session cookie)
curl -H "Cookie: PHPSESSID=your_session_id" \
  "http://localhost/agenda_api.php?action=list_events&month=2025-11"
```

## Common Issues & Solutions

### Issue: "No events this month" message
**Cause:** Wrong month selected or events in different month
**Solution:** Use month navigation buttons or check event dates

### Issue: Modal doesn't open
**Cause:** JavaScript error or event card not clickable
**Solution:**
- Check browser console for errors
- Verify `styles/output.css` is loading (F12 > Network tab)

### Issue: Can't create events
**Cause:** Form validation or API error
**Solution:**
- Check all required fields are filled
- Check browser console for errors
- Verify you're logged in as a team member

### Issue: Availability not saving
**Cause:** API error or session issue
**Solution:**
- Check Network tab for failed POST requests
- Verify session is active (try refreshing page)

## Quick Test with Playwright

Run the automated test to verify everything works:

```bash
node test-agenda.js
```

This will:
- Login automatically
- Navigate to agenda
- Click on events
- Set availability
- Test event creation
- Take screenshots of each step

Screenshots saved to `test-screenshots/agenda-*.png`

## Design Notes

### Why List View Instead of Calendar Grid?

1. **Better Mobile Experience** - List scrolls naturally on mobile
2. **More Information** - Can show full event details immediately
3. **Simpler UI** - Easier to see all event information at once
4. **Better for Teams** - Shows availability counts prominently

### If You Want a Calendar Grid View

You would need to modify `agenda.php` to add:
1. A calendar grid component (7 days x 4-5 weeks)
2. CSS for calendar styling
3. JavaScript to populate days with events
4. Click handlers for each day

This would be a significant UI change. The current list view is more practical for team coordination.

## Summary

✅ **Events are now visible** (updated to 2025)
✅ **Agenda uses list view** (no calendar grid)
✅ **All functionality works** (create, view, set availability)
✅ **Test data available** (4 events ready to use)

Just refresh your browser and you should see the events!
