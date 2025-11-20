<?php
/**
 * Page Agenda - Gestion des événements et disponibilités
 */

include("common.inc");
check_session();
print_header();
init_sql();

$season = $_SESSION['season'];
$player_name = $_SESSION['pseudo'];
$player_id = $_SESSION['player']; // player, not player_idx in session
$team = $_SESSION['top7team']; // top7team, not team in session
$is_captain = $_SESSION['captain'] ?? 0;
?>

<link rel="stylesheet" href="styles/output.css">

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-gray-900">📅 Agenda d'Équipe</h1>
                <button onclick="showCreateEventModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                    + Nouvel Événement
                </button>
            </div>
            <?php put_player_link($_SESSION); ?>
        </div>

        <!-- Navigation mois -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <div class="flex items-center justify-between">
                <button onclick="changeMonth(-1)" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    ← Mois précédent
                </button>
                <h2 id="current-month" class="text-xl font-semibold text-gray-900">
                    <!-- Rempli par JavaScript -->
                </h2>
                <button onclick="changeMonth(1)" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">
                    Mois suivant →
                </button>
            </div>
        </div>

        <!-- Liste des événements -->
        <div id="events-list" class="space-y-4">
            <!-- Rempli par JavaScript -->
        </div>

        <!-- Message si aucun événement -->
        <div id="no-events" class="hidden bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 text-6xl mb-4">📅</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun événement ce mois-ci</h3>
            <p class="text-gray-500 mb-4">Créez le premier événement pour votre équipe !</p>
            <button onclick="showCreateEventModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Créer un événement
            </button>
        </div>
    </div>
</div>

<!-- Modal Création d'événement -->
<div id="create-event-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Nouvel Événement</h3>
                <button onclick="hideCreateEventModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                    ×
                </button>
            </div>

            <form id="create-event-form" onsubmit="createEvent(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: Match amical, Visionnage du match...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'événement</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="match_amical">🏉 Match amical</option>
                        <option value="visionnage">📺 Visionnage</option>
                        <option value="reunion">🤝 Réunion</option>
                        <option value="autre">📅 Autre</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input type="date" name="date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heure *</label>
                        <input type="time" name="time" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                    <input type="text" name="location"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: Stade, Bar, Domicile...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Détails supplémentaires..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre minimum de joueurs pour confirmation
                    </label>
                    <input type="number" name="min_players" value="3" min="1" max="7"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">
                        L'événement sera automatiquement confirmé quand ce nombre de joueurs sera disponible
                    </p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        Créer l'événement
                    </button>
                    <button type="button" onclick="hideCreateEventModal()" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Détails de l'événement -->
<div id="event-details-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div class="flex-1">
                    <h3 id="event-title" class="text-2xl font-bold text-gray-900 mb-2"></h3>
                    <div id="event-meta" class="text-sm text-gray-600"></div>
                </div>
                <button onclick="hideEventDetailsModal()" class="text-gray-400 hover:text-gray-600 text-2xl ml-4">
                    ×
                </button>
            </div>

            <div id="event-details-content" class="space-y-6">
                <!-- Rempli par JavaScript -->
            </div>

            <!-- Section disponibilités -->
            <div class="mt-6 border-t pt-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Ma disponibilité</h4>
                <div class="flex gap-3">
                    <button onclick="setMyAvailability('available')" class="flex-1 py-3 px-4 rounded-lg border-2 border-green-500 text-green-700 hover:bg-green-50 font-medium availability-btn" data-status="available">
                        ✅ Disponible
                    </button>
                    <button onclick="setMyAvailability('maybe')" class="flex-1 py-3 px-4 rounded-lg border-2 border-yellow-500 text-yellow-700 hover:bg-yellow-50 font-medium availability-btn" data-status="maybe">
                        ⚠️ Peut-être
                    </button>
                    <button onclick="setMyAvailability('unavailable')" class="flex-1 py-3 px-4 rounded-lg border-2 border-red-500 text-red-700 hover:bg-red-50 font-medium availability-btn" data-status="unavailable">
                        ❌ Indisponible
                    </button>
                </div>
                <div class="mt-3">
                    <input type="text" id="availability-comment" placeholder="Commentaire (optionnel)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Liste des disponibilités -->
            <div class="mt-6 border-t pt-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Réponses de l'équipe</h4>
                <div id="availability-list" class="space-y-2">
                    <!-- Rempli par JavaScript -->
                </div>
            </div>

            <!-- Actions -->
            <div id="event-actions" class="mt-6 border-t pt-6 flex gap-3">
                <!-- Rempli par JavaScript si créateur -->
            </div>
        </div>
    </div>
</div>

<script>
let currentMonth = new Date();
let currentEventId = null;
const playerId = <?php echo $player_id; ?>;

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateMonthDisplay();
    loadEvents();
});

// Changer de mois
function changeMonth(delta) {
    currentMonth.setMonth(currentMonth.getMonth() + delta);
    updateMonthDisplay();
    loadEvents();
}

// Mettre à jour l'affichage du mois
function updateMonthDisplay() {
    const options = { year: 'numeric', month: 'long' };
    document.getElementById('current-month').textContent = currentMonth.toLocaleDateString('fr-FR', options);
}

// Charger les événements
async function loadEvents() {
    const month = currentMonth.toISOString().slice(0, 7);

    try {
        const response = await fetch(`agenda_api.php?action=list_events&month=${month}`);
        const data = await response.json();

        if (data.success) {
            displayEvents(data.events);
        } else {
            console.error('Erreur:', data.error);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des événements:', error);
    }
}

// Afficher les événements
function displayEvents(events) {
    const container = document.getElementById('events-list');
    const noEvents = document.getElementById('no-events');

    if (events.length === 0) {
        container.classList.add('hidden');
        noEvents.classList.remove('hidden');
        return;
    }

    container.classList.remove('hidden');
    noEvents.classList.add('hidden');

    container.innerHTML = events.map(event => createEventCard(event)).join('');
}

// Créer une carte d'événement
function createEventCard(event) {
    const date = new Date(event.proposed_date);
    const statusColors = {
        'proposed': 'bg-blue-100 text-blue-800',
        'confirmed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };

    const progress = event.total_responses > 0
        ? Math.round((event.available_count / event.total_responses) * 100)
        : 0;

    return `
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer" onclick="showEventDetails(${event.id})">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-semibold text-gray-900">${escapeHtml(event.title)}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-medium ${statusColors[event.status]}">
                                ${event.status_label}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span>${event.type_label}</span>
                            <span>📅 ${formatDate(date)}</span>
                            <span>🕐 ${formatTime(date)}</span>
                            ${event.location ? `<span>📍 ${escapeHtml(event.location)}</span>` : ''}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <div class="flex gap-2">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">✅ ${event.available_count}</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">⚠️ ${event.maybe_count}</span>
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">❌ ${event.unavailable_count}</span>
                    </div>
                    <div class="flex-1">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500" style="width: ${progress}%"></div>
                        </div>
                    </div>
                    <span class="text-gray-600">${event.available_count}/${event.min_players} requis</span>
                </div>
            </div>
        </div>
    `;
}

// Afficher les détails d'un événement
async function showEventDetails(eventId) {
    currentEventId = eventId;

    try {
        const response = await fetch(`agenda_api.php?action=get_event&event_id=${eventId}`);
        const data = await response.json();

        if (data.success) {
            const event = data.event;
            const date = new Date(event.proposed_date);

            document.getElementById('event-title').textContent = event.title;
            document.getElementById('event-meta').innerHTML = `
                ${event.type_label} • Créé par ${escapeHtml(event.creator_name)}
            `;

            let content = `
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div><strong>Date:</strong> ${formatDate(date)} à ${formatTime(date)}</div>
                    ${event.location ? `<div><strong>Lieu:</strong> ${escapeHtml(event.location)}</div>` : ''}
                    ${event.description ? `<div><strong>Description:</strong><br>${escapeHtml(event.description)}</div>` : ''}
                    <div><strong>Statut:</strong> <span class="font-semibold">${event.status_label}</span></div>
                    <div><strong>Joueurs requis:</strong> ${event.min_players}</div>
                </div>
            `;

            document.getElementById('event-details-content').innerHTML = content;

            // Afficher les disponibilités
            displayAvailabilities(event.availabilities);

            // Mettre en surbrillance la disponibilité actuelle du joueur
            highlightMyAvailability(event.availabilities);

            // Afficher les actions si créateur
            if (event.creator_id == playerId) {
                document.getElementById('event-actions').innerHTML = `
                    <button onclick="deleteEvent(${eventId})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Supprimer l'événement
                    </button>
                    ${event.status === 'proposed' ? `
                        <button onclick="confirmEvent(${eventId})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Confirmer manuellement
                        </button>
                    ` : ''}
                    ${event.status === 'proposed' || event.status === 'confirmed' ? `
                        <button onclick="cancelEvent(${eventId})" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Annuler l'événement
                        </button>
                    ` : ''}
                `;
            } else {
                document.getElementById('event-actions').innerHTML = '';
            }

            document.getElementById('event-details-modal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement de l\'événement');
    }
}

// Afficher les disponibilités
function displayAvailabilities(availabilities) {
    const container = document.getElementById('availability-list');

    if (availabilities.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">Aucune réponse pour le moment</p>';
        return;
    }

    const statusIcons = {
        'available': '✅',
        'maybe': '⚠️',
        'unavailable': '❌'
    };

    const statusColors = {
        'available': 'bg-green-50 border-green-200',
        'maybe': 'bg-yellow-50 border-yellow-200',
        'unavailable': 'bg-red-50 border-red-200'
    };

    container.innerHTML = availabilities.map(a => `
        <div class="flex items-center justify-between p-3 rounded-lg border ${statusColors[a.status]}">
            <div class="flex items-center gap-3">
                <span class="text-xl">${statusIcons[a.status]}</span>
                <div>
                    <div class="font-medium">${escapeHtml(a.player_name)}</div>
                    ${a.comment ? `<div class="text-sm text-gray-600">${escapeHtml(a.comment)}</div>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

// Mettre en surbrillance ma disponibilité
function highlightMyAvailability(availabilities) {
    // Réinitialiser
    document.querySelectorAll('.availability-btn').forEach(btn => {
        btn.classList.remove('ring-4', 'ring-offset-2');
    });

    const myAvailability = availabilities.find(a => a.player_id == playerId);
    if (myAvailability) {
        const btn = document.querySelector(`.availability-btn[data-status="${myAvailability.status}"]`);
        if (btn) {
            btn.classList.add('ring-4', 'ring-offset-2');
        }
        document.getElementById('availability-comment').value = myAvailability.comment || '';
    }
}

// Définir ma disponibilité
async function setMyAvailability(status) {
    const comment = document.getElementById('availability-comment').value;

    const formData = new FormData();
    formData.append('action', 'set_availability');
    formData.append('event_id', currentEventId);
    formData.append('status', status);
    formData.append('comment', comment);

    try {
        const response = await fetch('agenda_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            // Recharger les détails
            await showEventDetails(currentEventId);
            // Recharger la liste
            await loadEvents();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement');
    }
}

// Créer un événement
async function createEvent(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Combiner date et heure
    const date = formData.get('date');
    const time = formData.get('time');
    const proposedDate = `${date} ${time}:00`;

    const data = new FormData();
    data.append('action', 'create_event');
    data.append('title', formData.get('title'));
    data.append('type', formData.get('type'));
    data.append('proposed_date', proposedDate);
    data.append('location', formData.get('location'));
    data.append('description', formData.get('description'));
    data.append('min_players', formData.get('min_players'));

    try {
        const response = await fetch('agenda_api.php', {
            method: 'POST',
            body: data
        });
        const result = await response.json();

        if (result.success) {
            hideCreateEventModal();
            form.reset();
            await loadEvents();
            alert('Événement créé avec succès !');
        } else {
            alert('Erreur: ' + result.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la création');
    }
}

// Supprimer un événement
async function deleteEvent(eventId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_event');
    formData.append('event_id', eventId);

    try {
        const response = await fetch('agenda_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            hideEventDetailsModal();
            await loadEvents();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression');
    }
}

// Confirmer un événement
async function confirmEvent(eventId) {
    const formData = new FormData();
    formData.append('action', 'update_event');
    formData.append('event_id', eventId);
    formData.append('status', 'confirmed');

    try {
        const response = await fetch('agenda_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            await showEventDetails(eventId);
            await loadEvents();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Annuler un événement
async function cancelEvent(eventId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cet événement ?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_event');
    formData.append('event_id', eventId);
    formData.append('status', 'cancelled');

    try {
        const response = await fetch('agenda_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            hideEventDetailsModal();
            await loadEvents();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Gestion des modals
function showCreateEventModal() {
    document.getElementById('create-event-modal').classList.remove('hidden');
    // Définir la date par défaut à aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="date"]').value = today;
}

function hideCreateEventModal() {
    document.getElementById('create-event-modal').classList.add('hidden');
}

function hideEventDetailsModal() {
    document.getElementById('event-details-modal').classList.add('hidden');
    currentEventId = null;
}

// Utilitaires
function formatDate(date) {
    return date.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

function formatTime(date) {
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
