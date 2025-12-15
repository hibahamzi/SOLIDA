<?php
// statistique_reclamations.php - Page des statistiques des réclamations

// ============================================
// CONFIGURATION D'ERREURS ET SÉCURITÉ
// ============================================

// Activer toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Démarrer la session avec sécurisation
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true
    ]);
}

// ============================================
// VÉRIFICATION D'AUTHENTIFICATION ET AUTORISATION
// ============================================

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../front_office/sign-in.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Vérifier si l'utilisateur est admin
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die("<div style='max-width: 600px; margin: 50px auto; padding: 30px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; text-align: center;'>
            <h2 style='color: #721c24;'>Accès Refusé</h2>
            <p style='color: #721c24;'>Cette page est réservée aux administrateurs.</p>
            <a href='../back_office/dashboard.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #721c24; color: white; text-decoration: none; border-radius: 4px;'>Retour au Tableau de Bord</a>
        </div>");
}

// ============================================
// CHARGEMENT DU CONTRÔLEUR
// ============================================

// Chemin du contrôleur
$controllerPath = __DIR__ . '/../../controllers/ReclamationController.php';

// Vérifier l'existence du fichier
if (!file_exists($controllerPath)) {
    die("<div style='max-width: 600px; margin: 50px auto; padding: 30px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;'>
            <h2 style='color: #721c24;'>Erreur de Configuration</h2>
            <p style='color: #721c24;'>Fichier contrôleur introuvable.</p>
        </div>");
}

// Charger le contrôleur
try {
    require_once $controllerPath;
    
    // Vérifier que la classe existe
    if (!class_exists('ReclamationController')) {
        throw new Exception("La classe 'ReclamationController' n'est pas définie");
    }
    
    // Instancier le contrôleur
    $reclamationController = new ReclamationController();
    
} catch (Exception $e) {
    die("<div style='max-width: 600px; margin: 50px auto; padding: 30px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;'>
            <h2 style='color: #721c24;'>Erreur d'Initialisation</h2>
            <p style='color: #721c24;'>" . htmlspecialchars($e->getMessage()) . "</p>
        </div>");
}

// ============================================
// RÉCUPÉRATION DES DONNÉES
// ============================================

$reclamations = [];
$stats = [];

try {
    // Récupérer toutes les réclamations
    $reclamations = $reclamationController->getReclamation();
    
    // Valider que c'est un tableau
    if (!is_array($reclamations)) {
        $reclamations = [];
    }
    
    // Récupérer les statistiques
    $stats = $reclamationController->getStatistics();
    
    // Valider les statistiques
    if (!is_array($stats)) {
        $stats = [
            'total' => 0,
            'resolved' => 0,
            'unresolved' => 0,
            'resolution_rate' => 0,
            'urgent' => 0,
            'this_week' => 0,
            'total_responses' => 0
        ];
    }
    
    // S'assurer que toutes les clés existent
    $stats = array_merge([
        'total' => 0,
        'resolved' => 0,
        'unresolved' => 0,
        'resolution_rate' => 0,
        'urgent' => 0,
        'this_week' => 0,
        'total_responses' => 0
    ], $stats);
    
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données: " . $e->getMessage());
    
    // Initialiser avec des valeurs par défaut en cas d'erreur
    $reclamations = [];
    $stats = [
        'total' => 0,
        'resolved' => 0,
        'unresolved' => 0,
        'resolution_rate' => 0,
        'urgent' => 0,
        'this_week' => 0,
        'total_responses' => 0
    ];
}

// ============================================
// CALCUL DES STATISTIQUES
// ============================================

// Initialiser les compteurs
$urgentCount = 0;
$overdueCount = 0;
$currentWeekCount = 0;
$withResponseCount = 0;

// Compter les statuts
$statusCounts = [
    'nouveau' => 0,
    'en_cours' => 0,
    'résolu' => 0,
    'fermé' => 0
];

// Dates pour les calculs
$oneWeekAgo = strtotime('-1 week');
$sevenDaysAgo = strtotime('-7 days');

// Traiter chaque réclamation
foreach ($reclamations as $reclamation) {
    if (!is_array($reclamation)) {
        continue;
    }
    
    // Compter par statut
    $status = strtolower(trim($reclamation['statut'] ?? $reclamation['status'] ?? 'nouveau'));
    
    if (in_array($status, ['nouveau', 'new', 'en_attente', 'pending'])) {
        $statusCounts['nouveau']++;
    } elseif (in_array($status, ['en_cours', 'en cours', 'in_progress', 'processing', 'en traitement'])) {
        $statusCounts['en_cours']++;
    } elseif (in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'solved', 'resolved'])) {
        $statusCounts['résolu']++;
    } elseif (in_array($status, ['fermé', 'ferme', 'closed', 'cloturé', 'cloture'])) {
        $statusCounts['fermé']++;
    } else {
        $statusCounts['nouveau']++;
    }
    
    // Compter les urgents
    $priority = strtolower(trim($reclamation['priorite'] ?? $reclamation['priority'] ?? 'normal'));
    if (in_array($priority, ['urgente', 'urgent', 'haute', 'high', 'critique', 'critical']) || 
        stripos($priority, 'urgent') !== false) {
        $urgentCount++;
    }
    
    // Vérifier les dates
    $dateField = $reclamation['date'] ?? $reclamation['date_creation'] ?? $reclamation['created_at'] ?? '';
    
    if (!empty($dateField)) {
        try {
            $reclamationDate = strtotime($dateField);
            
            if ($reclamationDate !== false) {
                // Compter pour cette semaine
                if ($reclamationDate >= $oneWeekAgo) {
                    $currentWeekCount++;
                }
                
                // Vérifier si en retard (non résolu depuis plus de 7 jours)
                $isResolved = in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'fermé', 'ferme', 'solved', 'resolved', 'closed']);
                
                if (!$isResolved && $reclamationDate < $sevenDaysAgo) {
                    $overdueCount++;
                }
            }
        } catch (Exception $e) {
            // Ignorer les erreurs de date
        }
    }
    
    // Compter les réclamations avec réponse
    if (isset($reclamation['reponses']) && !empty($reclamation['reponses'])) {
        $withResponseCount++;
    } elseif (isset($reclamation['nombre_reponses']) && $reclamation['nombre_reponses'] > 0) {
        $withResponseCount++;
    }
}

// Calculer les taux
$totalReclamations = max(1, $stats['total']);
$resolutionRate = round(($stats['resolved'] / $totalReclamations) * 100, 1);
$responseRate = round(($stats['total_responses'] / $totalReclamations) * 100, 1);

// Utiliser les valeurs calculées ou celles des stats
$urgentCount = max($urgentCount, $stats['urgent'] ?? 0);
$currentWeekCount = max($currentWeekCount, $stats['this_week'] ?? 0);

// ============================================
// HTML DE LA PAGE
// ============================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Réclamations - Administration</title>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            color: #212934;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .page-title i {
            color: #59ab6e;
            font-size: 32px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-refresh {
            background: #17a2b8;
            color: white;
        }
        
        .btn-refresh:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Main Content */
        .content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card.total::before { background: #59ab6e; }
        .stat-card.resolved::before { background: #3498db; }
        .stat-card.pending::before { background: #f39c12; }
        .stat-card.urgent::before { background: #e74c3c; }
        .stat-card.week::before { background: #9b59b6; }
        .stat-card.overdue::before { background: #d35400; }
        .stat-card.with-response::before { background: #1abc9c; }
        .stat-card.without-response::before { background: #7f8c8d; }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .stat-card.total .stat-icon { color: #59ab6e; }
        .stat-card.resolved .stat-icon { color: #3498db; }
        .stat-card.pending .stat-icon { color: #f39c12; }
        .stat-card.urgent .stat-icon { color: #e74c3c; }
        .stat-card.week .stat-icon { color: #9b59b6; }
        .stat-card.overdue .stat-icon { color: #d35400; }
        .stat-card.with-response .stat-icon { color: #1abc9c; }
        .stat-card.without-response .stat-icon { color: #7f8c8d; }
        
        .stat-title {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-card.total .stat-value { color: #59ab6e; }
        .stat-card.resolved .stat-value { color: #3498db; }
        .stat-card.pending .stat-value { color: #f39c12; }
        .stat-card.urgent .stat-value { color: #e74c3c; }
        .stat-card.week .stat-value { color: #9b59b6; }
        .stat-card.overdue .stat-value { color: #d35400; }
        .stat-card.with-response .stat-value { color: #1abc9c; }
        .stat-card.without-response .stat-value { color: #7f8c8d; }
        
        .stat-subtitle {
            font-size: 14px;
            color: #95a5a6;
        }
        
        /* Charts Section */
        .charts-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .section-title {
            color: #212934;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #59ab6e;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        @media (max-width: 992px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-box {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        
        .chart-title {
            text-align: center;
            margin-bottom: 20px;
            color: #212934;
            font-size: 18px;
            font-weight: 600;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Status Table */
        .status-table {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #212934;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-nouveau { background: #e3f2fd; color: #1976d2; }
        .badge-en_cours { background: #fff3e0; color: #f57c00; }
        .badge-résolu { background: #e8f5e9; color: #388e3c; }
        .badge-fermé { background: #f5f5f5; color: #616161; }
        
        .progress-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
        }
        
        .progress-nouveau { background: #1976d2; }
        .progress-en_cours { background: #f57c00; }
        .progress-résolu { background: #388e3c; }
        .progress-fermé { background: #616161; }
        
        /* Performance Summary */
        .performance-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .performance-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .performance-label {
            font-weight: 600;
            color: #495057;
        }
        
        .performance-value {
            font-weight: 700;
            font-size: 18px;
            color: #212934;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .last-update {
            font-style: italic;
            margin-top: 5px;
        }
        
        /* Loading */
        .loading {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #59ab6e;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="page-title">
                <i class="fas fa-chart-bar"></i>
                <h1>Statistiques des Réclamations</h1>
            </div>
            
            <div class="header-actions">
                <div class="user-info">
                    <i class="fas fa-user-shield"></i>
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
                    (<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?>)
                </div>
                
                <button class="btn btn-refresh" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
                
                <a href="../back_office/dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <?php if (empty($reclamations) && $stats['total'] == 0): ?>
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <h3>Aucune donnée disponible</h3>
                    <p>Il n'y a pas encore de réclamations dans le système.</p>
                </div>
            <?php else: ?>
               
                <!-- Charts Section -->
                <div class="charts-section">
                    <h2 class="section-title">
                        <i class="fas fa-chart-pie"></i> Visualisation des Données
                    </h2>
                    
                    <div class="charts-container">
                        <!-- Chart 1: Status Distribution -->
                        <div class="chart-box">
                            <h3 class="chart-title">Répartition par Statut</h3>
                            <div class="chart-wrapper">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Chart 2: Weekly Trend -->
                        <div class="chart-box">
                            <h3 class="chart-title">Évolution Hebdomadaire</h3>
                            <div class="chart-wrapper">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status Table -->
                <div class="status-table">
                    <h2 class="section-title">
                        <i class="fas fa-table"></i> Détail par Statut
                    </h2>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Statut</th>
                                    <th>Nombre</th>
                                    <th>Pourcentage</th>
                                    <th>Progression</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statusLabels = [
                                    'nouveau' => 'Nouveau',
                                    'en_cours' => 'En Cours',
                                    'résolu' => 'Résolu',
                                    'fermé' => 'Fermé'
                                ];
                                
                                $statusClasses = [
                                    'nouveau' => 'badge-nouveau',
                                    'en_cours' => 'badge-en_cours',
                                    'résolu' => 'badge-résolu',
                                    'fermé' => 'badge-fermé'
                                ];
                                
                                $progressClasses = [
                                    'nouveau' => 'progress-nouveau',
                                    'en_cours' => 'progress-en_cours',
                                    'résolu' => 'progress-résolu',
                                    'fermé' => 'progress-fermé'
                                ];
                                
                                foreach ($statusCounts as $status => $count):
                                    $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
                                    $label = $statusLabels[$status] ?? ucfirst($status);
                                    $statusClass = $statusClasses[$status] ?? '';
                                    $progressClass = $progressClasses[$status] ?? '';
                                ?>
                                <tr>
                                    <td>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($label); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo number_format($count); ?></strong></td>
                                    <td><strong><?php echo $percentage; ?>%</strong></td>
                                    <td style="width: 40%;">
                                        <div class="progress-bar">
                                            <div class="progress-fill <?php echo $progressClass; ?>" 
                                                 style="width: <?php echo min(100, $percentage); ?>%;"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Performance Summary -->
                    <div class="performance-summary">
                        <div class="performance-item">
                            <span class="performance-label">Taux de Résolution</span>
                            <span class="performance-value"><?php echo $resolutionRate; ?>%</span>
                        </div>
                        
                        <div class="performance-item">
                            <span class="performance-label">Taux de Réponse</span>
                            <span class="performance-value"><?php echo $responseRate; ?>%</span>
                        </div>
                        
                        <div class="performance-item">
                            <span class="performance-label">Temps Moyen de Résolution</span>
                            <span class="performance-value"><?php echo calculateAverageResolutionTime($reclamations); ?> jours</span>
                        </div>
                        
                        <div class="performance-item">
                            <span class="performance-label">Satisfaction Moyenne</span>
                            <span class="performance-value"><?php echo calculateAverageSatisfaction($reclamations); ?>/5</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Système de Gestion des Réclamations &copy; <?php echo date('Y'); ?></p>
            <p class="last-update">Dernière mise à jour: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Données pour les graphiques
        const statusData = {
            labels: ['Nouveau', 'En Cours', 'Résolu', 'Fermé'],
            datasets: [{
                data: [
                    <?php echo $statusCounts['nouveau']; ?>,
                    <?php echo $statusCounts['en_cours']; ?>,
                    <?php echo $statusCounts['résolu']; ?>,
                    <?php echo $statusCounts['fermé']; ?>
                ],
                backgroundColor: [
                    '#3498db', // Nouveau - Bleu
                    '#f39c12', // En cours - Orange
                    '#2ecc71', // Résolu - Vert
                    '#95a5a6'  // Fermé - Gris
                ],
                borderColor: [
                    '#2980b9',
                    '#e67e22',
                    '#27ae60',
                    '#7f8c8d'
                ],
                borderWidth: 2,
                hoverOffset: 15
            }]
        };
        
        // Données pour le graphique hebdomadaire (exemple)
        const weeklyData = {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Cette Semaine'],
            datasets: [{
                label: 'Nouvelles Réclamations',
                data: [12, 19, 8, 15, <?php echo $currentWeekCount; ?>],
                backgroundColor: 'rgba(89, 171, 110, 0.2)',
                borderColor: 'rgba(89, 171, 110, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }, {
                label: 'Réclamations Résolues',
                data: [8, 12, 6, 10, <?php echo $stats['resolved']; ?>],
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
        
        // Initialiser le graphique des statuts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            font: {
                                size: 12
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1000
                }
            }
        });
        
        // Initialiser le graphique hebdomadaire
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(weeklyCtx, {
            type: 'line',
            data: weeklyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
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
                            text: 'Nombre de réclamations'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Semaines'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });
        
        // Animation des cartes de statistiques
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease-out';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Rafraîchissement automatique toutes les 5 minutes
        setInterval(() => {
            const refreshBtn = document.querySelector('.btn-refresh');
            if (refreshBtn) {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Actualisation...';
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }, 300000); // 5 minutes
        
        // Exporter les données
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                alert('Utilisez le bouton d\'impression du navigateur pour exporter cette page.');
            }
        });
    });
    
    // Fonction pour exporter les données (exemple)
    function exportData(format) {
        if (format === 'pdf') {
            window.print();
        } else if (format === 'excel') {
            alert('Export Excel en cours de développement...');
        }
    }
    </script>
</body>
</html>

<?php
// Fonctions auxiliaires
function calculateAverageResolutionTime($reclamations) {
    $totalDays = 0;
    $count = 0;
    
    foreach ($reclamations as $reclamation) {
        if (isset($reclamation['date_creation']) && isset($reclamation['date_resolution'])) {
            $start = strtotime($reclamation['date_creation']);
            $end = strtotime($reclamation['date_resolution']);
            
            if ($start && $end && $end > $start) {
                $days = ceil(($end - $start) / (60 * 60 * 24));
                $totalDays += $days;
                $count++;
            }
        }
    }
    
    return $count > 0 ? round($totalDays / $count, 1) : 0;
}

function calculateAverageSatisfaction($reclamations) {
    $totalSatisfaction = 0;
    $count = 0;
    
    foreach ($reclamations as $reclamation) {
        if (isset($reclamation['satisfaction'])) {
            $satisfaction = intval($reclamation['satisfaction']);
            if ($satisfaction >= 1 && $satisfaction <= 5) {
                $totalSatisfaction += $satisfaction;
                $count++;
            }
        }
    }
    
    return $count > 0 ? round($totalSatisfaction / $count, 1) : 'N/A';
}
?>