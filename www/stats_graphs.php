<?php
/**
 * Page des graphiques de statistiques
 */

include("common.inc");
check_session();
print_header();
init_sql();

$season = $_SESSION['season'] ?? null;
$player_name = $_SESSION['pseudo'] ?? '';
$team = $_SESSION['team'] ?? '';
?>

<link rel="stylesheet" href="styles/output.css">

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-gray-900">📊 Statistiques et Graphiques</h1>
                <a href="stats.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Retour aux statistiques
                </a>
            </div>
            <?php put_player_link($_SESSION); ?>
        </div>

        <!-- Onglets -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('personal')" id="tab-personal" class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Ma progression
                </button>
                <button onclick="showTab('comparison')" id="tab-comparison" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Comparaison joueurs
                </button>
                <button onclick="showTab('team')" id="tab-team" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Évolution d'équipe
                </button>
            </nav>
        </div>

        <!-- Onglet Personnel -->
        <div id="content-personal" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Graphique Points Cumulés -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📈 Points Cumulés</h2>
                    <div class="relative" style="height: 300px;">
                        <canvas id="pointsChart"></canvas>
                    </div>
                </div>

                <!-- Graphique Évolution Classement -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🏆 Évolution du Classement</h2>
                    <div class="relative" style="height: 300px;">
                        <canvas id="rankChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Points Total</div>
                    <div class="text-2xl font-bold text-blue-600" id="stat-total-points">-</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Classement Actuel</div>
                    <div class="text-2xl font-bold text-yellow-600" id="stat-current-rank">-</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Meilleur Classement</div>
                    <div class="text-2xl font-bold text-green-600" id="stat-best-rank">-</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-500">Progression</div>
                    <div class="text-2xl font-bold text-purple-600" id="stat-progression">-</div>
                </div>
            </div>
        </div>

        <!-- Onglet Comparaison -->
        <div id="content-comparison" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionnez les joueurs à comparer :
                    </label>
                    <div id="players-checkboxes" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-4">
                        <!-- Chargé dynamiquement -->
                    </div>
                    <button onclick="loadComparisonChart()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Comparer
                    </button>
                </div>
                <div class="relative" style="height: 400px;">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Onglet Équipe -->
        <div id="content-team" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📊 Évolution des Points d'Équipe</h2>
                <div class="relative" style="height: 400px;">
                    <canvas id="teamChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Variables globales
let pointsChart = null;
let rankChart = null;
let comparisonChart = null;
let teamChart = null;
let playerEvolutionData = null;

// Couleurs
const COLORS = [
    '#3b82f6', '#ef4444', '#22c55e', '#f59e0b', '#8b5cf6',
    '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'
];

// Gestion des onglets
function showTab(tabName) {
    // Masquer tous les contenus
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-button').forEach(el => {
        el.classList.remove('active', 'border-blue-500', 'text-blue-600');
        el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });

    // Afficher le contenu sélectionné
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

    // Charger les données selon l'onglet
    if (tabName === 'personal' && !playerEvolutionData) {
        loadPersonalCharts();
    } else if (tabName === 'comparison' && !document.querySelector('#players-checkboxes .player-checkbox')) {
        loadPlayersList();
    } else if (tabName === 'team' && !teamChart) {
        loadTeamChart();
    }
}

// Charger les graphiques personnels
async function loadPersonalCharts() {
    try {
        const response = await fetch('stats_api.php?action=player_evolution');
        const data = await response.json();
        playerEvolutionData = data;

        // Graphique des points
        createPointsChart(data);

        // Graphique du classement
        createRankChart(data);

        // Mettre à jour les statistiques rapides
        updateQuickStats(data);

    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        alert('Erreur lors du chargement des données');
    }
}

// Créer le graphique des points
function createPointsChart(data) {
    const ctx = document.getElementById('pointsChart').getContext('2d');

    if (pointsChart) pointsChart.destroy();

    pointsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Points',
                data: data.points,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Points'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Journée'
                    }
                }
            }
        }
    });
}

// Créer le graphique du classement
function createRankChart(data) {
    const ctx = document.getElementById('rankChart').getContext('2d');

    if (rankChart) rankChart.destroy();

    rankChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Classement',
                data: data.rank,
                borderColor: '#fbbf24',
                backgroundColor: 'rgba(251, 191, 36, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Rang: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    reverse: true, // Le rang 1 en haut
                    beginAtZero: false,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Classement'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Journée'
                    }
                }
            }
        }
    });
}

// Mettre à jour les statistiques rapides
function updateQuickStats(data) {
    const points = data.points;
    const ranks = data.rank.filter(r => r !== null);

    document.getElementById('stat-total-points').textContent = points[points.length - 1] || 0;
    document.getElementById('stat-current-rank').textContent = ranks[ranks.length - 1] || '-';
    document.getElementById('stat-best-rank').textContent = Math.min(...ranks) || '-';

    // Progression (différence entre premier et dernier classement)
    const firstRank = ranks[0];
    const lastRank = ranks[ranks.length - 1];
    const progression = firstRank - lastRank;
    const progressionEl = document.getElementById('stat-progression');
    if (progression > 0) {
        progressionEl.textContent = '+' + progression;
        progressionEl.classList.add('text-green-600');
    } else if (progression < 0) {
        progressionEl.textContent = progression;
        progressionEl.classList.add('text-red-600');
    } else {
        progressionEl.textContent = '=';
    }
}

// Charger la liste des joueurs pour comparaison
async function loadPlayersList() {
    try {
        const response = await fetch('stats_api.php?action=players_list');
        const players = await response.json();

        const container = document.getElementById('players-checkboxes');
        container.innerHTML = '';

        players.forEach(player => {
            const div = document.createElement('div');
            div.className = 'flex items-center';
            div.innerHTML = `
                <input type="checkbox" id="player-${player.id}" value="${player.id}"
                       class="player-checkbox h-4 w-4 text-blue-600 rounded">
                <label for="player-${player.id}" class="ml-2 text-sm text-gray-700">
                    ${player.name} (${player.points} pts)
                </label>
            `;
            container.appendChild(div);
        });

    } catch (error) {
        console.error('Erreur lors du chargement des joueurs:', error);
    }
}

// Charger le graphique de comparaison
async function loadComparisonChart() {
    const checkboxes = document.querySelectorAll('.player-checkbox:checked');
    const playerIds = Array.from(checkboxes).map(cb => cb.value);

    if (playerIds.length === 0) {
        alert('Veuillez sélectionner au moins un joueur');
        return;
    }

    if (playerIds.length > 5) {
        alert('Veuillez sélectionner maximum 5 joueurs');
        return;
    }

    try {
        const response = await fetch(`stats_api.php?action=player_comparison&players=${playerIds.join(',')}`);
        const data = await response.json();

        const ctx = document.getElementById('comparisonChart').getContext('2d');

        if (comparisonChart) comparisonChart.destroy();

        // Assigner une couleur à chaque dataset
        data.datasets.forEach((dataset, index) => {
            dataset.borderColor = COLORS[index % COLORS.length];
            dataset.backgroundColor = COLORS[index % COLORS.length] + '20';
            dataset.tension = 0.4;
        });

        comparisonChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Points'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Journée'
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Erreur lors du chargement de la comparaison:', error);
        alert('Erreur lors du chargement des données');
    }
}

// Charger le graphique d'équipe
async function loadTeamChart() {
    try {
        const response = await fetch('stats_api.php?action=team_evolution&team=<?php echo $team; ?>');
        const data = await response.json();

        const ctx = document.getElementById('teamChart').getContext('2d');

        if (teamChart) teamChart.destroy();

        teamChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Points d\'équipe',
                    data: data.points,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Points'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Journée'
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error('Erreur lors du chargement des données d\'équipe:', error);
        alert('Erreur lors du chargement des données');
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    showTab('personal');
});
</script>

<style>
.tab-button.active {
    border-color: #3b82f6;
    color: #3b82f6;
}
</style>

</body>
</html>
