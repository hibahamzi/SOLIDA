<?php
// BackofficeReclamations.php - Version avec forme semblable à users.php
require_once __DIR__ . '/../../controllers/ReclamationController.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

$success = '';
$error = '';
$reclamations = [];

// Récupérer les messages de session
if (isset($_SESSION['reclamations_success'])) {
    $success = $_SESSION['reclamations_success'];
    unset($_SESSION['reclamations_success']);
}

if (isset($_SESSION['reclamations_message'])) {
    $error = $_SESSION['reclamations_message'];
    unset($_SESSION['reclamations_message']);
}

// Créer une instance de ReclamationController
$reclamationController = new ReclamationController();

// Récupérer toutes les réclamations AVEC leurs réponses
$allReclamations = [];
try {
    // Récupérer toutes les réclamations
    $allReclamations = $reclamationController->getReclamation();
    
    // Pour chaque réclamation, chercher sa réponse
    foreach ($allReclamations as $reclamation) {
        $id = $reclamation['id'];
        
        // Récupérer la réponse si elle existe
        $response = $reclamationController->getResponseByReclamationId($id);
        
        // Fusionner les données
        if ($response) {
            $reclamation['id_reponse'] = $response['id_reponse'] ?? $response['id'];
            $reclamation['reponse'] = $response['reponse'] ?? '';
            $reclamation['reponse_date'] = $response['date_creation'] ?? '';
            $reclamation['reponse_statut'] = $response['statut'] ?? '';
            $reclamation['has_response'] = true;
        } else {
            $reclamation['id_reponse'] = null;
            $reclamation['reponse'] = '';
            $reclamation['reponse_date'] = '';
            $reclamation['reponse_statut'] = '';
            $reclamation['has_response'] = false;
        }
        
        // Ajouter le nom complet
        $reclamation['fullname'] = $reclamation['nom'] . ' ' . $reclamation['prenom'];
        
        // Vérifier si c'est urgent
        $priorite = strtolower(trim($reclamation['priorite'] ?? ''));
        $statut = strtolower(trim($reclamation['statut'] ?? ''));
        
        $reclamation['is_urgent'] = false;
        if (in_array($priorite, ['urgente', 'urgent', 'haute', 'high']) || stripos($priorite, 'urgent') !== false) {
            $statuts_resolus = ['résolu', 'resolu', 'traité', 'traite', 'traitée', 'traitee', 'resolved', 'closed'];
            if (!in_array($statut, $statuts_resolus) && !empty($statut)) {
                $reclamation['is_urgent'] = true;
            }
        }
        
        $reclamations[] = $reclamation;
    }
    
    if (!is_array($reclamations)) {
        $reclamations = [];
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des réclamations: " . $e->getMessage());
    $reclamations = [];
}

// Récupérer les réclamations urgentes
$urgentReclamations = [];
try {
    $urgentReclamations = array_filter($reclamations, function($reclamation) {
        return $reclamation['is_urgent'] === true;
    });
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des réclamations urgentes: " . $e->getMessage());
    $urgentReclamations = [];
}

// Récupérer les statistiques
try {
    $stats = $reclamationController->getStatistics();
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
    $stats = [
        'total' => 0, 'resolved' => 0, 'unresolved' => 0, 
        'resolution_rate' => 0, 'urgent' => 0, 'this_week' => 0,
        'total_responses' => 0
    ];
}

// Calculer les réclamations en retard
$overdueReclamations = [];
try {
    $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
    
    foreach ($reclamations as $reclamation) {
        $status = strtolower($reclamation['statut'] ?? '');
        $date = $reclamation['date'] ?? '';
        
        if (!in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'traitée', 'traitee', 'clôturé', 'cloture', 'fermé', 'ferme', 'resolved', 'closed'])) {
            if (!empty($date) && strtotime($date) < strtotime($sevenDaysAgo)) {
                $overdueReclamations[] = $reclamation;
            }
        }
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des réclamations en retard: " . $e->getMessage());
    $overdueReclamations = [];
}

// Traitement du temps de traitement moyen
$processingTime = ['average_days' => 0];
try {
    $processingTime = $reclamationController->getAverageProcessingTime();
} catch (Exception $e) {
    error_log("Erreur lors de la récupération du temps de traitement: " . $e->getMessage());
}

// Récupération des paramètres de recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$priorityFilter = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Filtrer les réclamations selon les critères de recherche
if (!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all' || $typeFilter !== 'all') {
    $filteredReclamations = array_filter($reclamations, function($reclamation) use ($search, $statusFilter, $priorityFilter, $typeFilter) {
        $match = true;
        
        // Filtre de recherche
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $match = $match && (
                stripos($reclamation['nom'], $search) !== false ||
                stripos($reclamation['prenom'], $search) !== false ||
                stripos($reclamation['email'], $search) !== false ||
                stripos($reclamation['telephone'], $search) !== false ||
                stripos($reclamation['sujet'] ?? '', $search) !== false ||
                stripos($reclamation['description'] ?? '', $search) !== false
            );
        }
        
        // Filtre par statut
        if ($statusFilter !== 'all') {
            $reclamationStatus = strtolower($reclamation['statut'] ?? '');
            $filterStatus = strtolower($statusFilter);
            $match = $match && ($reclamationStatus === $filterStatus);
        }
        
        // Filtre par priorité
        if ($priorityFilter !== 'all') {
            $reclamationPriority = strtolower($reclamation['priorite'] ?? '');
            $filterPriority = strtolower($priorityFilter);
            $match = $match && ($reclamationPriority === $filterPriority);
        }
        
        // Filtre par type (sujet)
        if ($typeFilter !== 'all') {
            $reclamationType = strtolower($reclamation['sujet'] ?? '');
            $filterType = strtolower($typeFilter);
            $match = $match && ($reclamationType === $filterType);
        }
        
        return $match;
    });
    
    $reclamations = array_values($filteredReclamations);
}

// Trier les réclamations
usort($reclamations, function($a, $b) use ($sortBy, $order) {
    $valueA = $a[$sortBy] ?? '';
    $valueB = $b[$sortBy] ?? '';
    
    if ($order === 'ASC') {
        return $valueA <=> $valueB;
    } else {
        return $valueB <=> $valueA;
    }
});

// Messages de succès et d'erreur
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'responded_and_emailed') {
        $success = "Réponse enregistrée avec succès et email envoyé au client !";
    } elseif ($_GET['success'] === 'response_updated') {
        $success = "Réponse modifiée avec succès !";
    } elseif ($_GET['success'] === 'deleted') {
        $success = "Réclamation et réponse supprimées avec succès !";
    } elseif ($_GET['success'] === 'responded_no_email') {
        $success = "Réponse enregistrée avec succès, mais l'email n'a pas pu être envoyé.";
    } else {
        $success = "Réponse enregistrée avec succès !";
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'delete_failed') {
        $error = "Erreur lors de la suppression de la réclamation. Veuillez réessayer.";
    } elseif ($_GET['error'] === 'invalid_id') {
        $error = "ID de réclamation invalide.";
    } elseif ($_GET['error'] === 'response_not_found') {
        $error = "Réponse non trouvée.";
    } else {
        $error = "Une erreur s'est produite.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réclamations - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <style>
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-bar select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .filter-bar select:focus {
            outline: none;
            border-color: var(--primary-green);
        }
        
        /* Badges */
        .priority-badge, .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-critique {
            background: #ffebee;
            color: #c62828;
        }
        
        .priority-haute {
            background: #fff3e0;
            color: #e65100;
        }
        
        .priority-moyenne {
            background: #fff9c4;
            color: #f57f17;
        }
        
        .priority-basse {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .priority-urgente {
            background: #F44336 !important;
            color: white !important;
            font-weight: bold !important;
            animation: blink 1.5s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .status-nouveau {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .status-en_cours {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .status-resolu, .status-traitée, .status-resolved, .status-closed {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-fermé, .status-rejetée, .status-rejected {
            background: #ffebee;
            color: #c62828;
        }
        
        .response-status {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .response-yes {
            color: #4CAF50;
            font-weight: 600;
            font-size: 12px;
        }
        
        .response-preview {
            color: #666;
            font-style: italic;
            font-size: 11px;
            line-height: 1.4;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .response-no {
            color: #999;
            font-size: 13px;
            font-style: italic;
        }
        
        /* Alertes urgentes */
        .alert-urgente {
            background: #ffebee;
            border: 2px solid #F44336;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .description-cell:hover {
            white-space: normal;
            overflow: visible;
            position: relative;
            background: white;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 10px;
            border-radius: 6px;
            max-width: 400px;
        }
        
        /* Bouton de statistiques */
        .stats-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 30px;
            font-size: 16px;
            background: linear-gradient(135deg, #59ab6e, #69bb7e);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 20px auto;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            width: fit-content;
        }
        
        .stats-toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(89, 171, 110, 0.3);
        }
        
        /* Styles supplémentaires */
        .success-message {
            background: #59ab6e; 
            color: white; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            gap: 10px;
        }
        
        .error-message {
            background: #e74c3c; 
            color: white; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            gap: 10px;
        }
        
        .urgent-alert-link {
            background: #F44336; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-weight: bold; 
            display: inline-block; 
            transition: background 0.3s;
        }
        
        .urgent-alert-link:hover {
            background: #d32f2f;
        }
        
        .btn-group-center {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .btn-stats {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            font-size: 16px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-stats:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .btn-stats i {
            font-size: 18px;
        }
        
        .btn-stats-success {
            background: linear-gradient(135deg, #59ab6e, #69bb7e);
        }
        
        .btn-stats-success:hover {
            box-shadow: 0 4px 12px rgba(89, 171, 110, 0.3);
        }
        
        .btn-stats-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }
        
        .btn-stats-warning:hover {
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }
        
        .btn-stats-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .btn-stats-danger:hover {
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }
    </style>
</head>
<body>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>SOLIDA</h2>
            <p>Panneau d'Administration</p>
        </div>
        
        <!-- Admin Info in Sidebar -->
        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                    <?php 
                        $adminName = $_SESSION['user_fullname'] ?? 'Admin';
                        $nameParts = explode(' ', $adminName);
                        $initials = strtoupper(substr($nameParts[0], 0, 1));
                        if (count($nameParts) > 1) {
                            $initials .= strtoupper(substr($nameParts[1], 0, 1));
                        }
                        echo $initials;
                    ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="color: white; font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? 'Administrateur'); ?>
                    </div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'admin@solida.com'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li>
                <a href="evenementback.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Événements</span>
                </a>
            </li>
            <li>
                <a href="donations.php">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Dons</span>
                </a>
            </li>
            <li>
                <a href="BackofficeReclamations.php" class="active">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Réclamations</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-handshake"></i>
                    <span>Sponsors</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tags"></i>
                    <span>Deals</span>
                </a>
            </li>
            <li>
                <a href="admin-profile.php">
                    <i class="fas fa-user-cog"></i>
                    <span>Mon Profil</span>
                </a>
            </li>
            <li>
                <a href="../front_office/index.php">
                    <i class="fas fa-globe"></i>
                    <span>Voir le Site</span>
                </a>
            </li>
            <li>
                <a href="../front_office/sign-in.php" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>Gestion des Réclamations</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Administrateur'); ?></span>
                    <small><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@solida.com'); ?></small>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            
            <!-- Messages de succès/erreur -->
            <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> 
                <span><?php echo $success; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> 
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Alertes Urgentes -->
            <?php if (!empty($urgentReclamations)): ?>
            <div class="alert-urgente" style="padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 28px; color: #F44336; animation: blink 1s infinite;"></i>
                    <div style="flex: 1;">
                        <strong style="font-size: 18px; display: block; margin-bottom: 8px;">🚨 ALERTE URGENTE !</strong>
                        <p style="margin: 0; font-size: 16px;">
                            <strong><?php echo count($urgentReclamations); ?> réclamation(s) urgente(s)</strong> nécessitent une attention immédiate.
                        </p>
                        <div style="margin-top: 10px;">
                            <a href="#urgent-reclamations" class="urgent-alert-link">
                                <i class="fas fa-arrow-down"></i> Voir les réclamations urgentes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Réclamations en Retard -->
            <?php if (!empty($overdueReclamations)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                <i class="fas fa-clock"></i>
                <div style="display: inline-block; margin-left: 10px;">
                    <strong>Retard !</strong> 
                    <?php echo count($overdueReclamations); ?> réclamation(s) en retard (non résolues depuis plus de 7 jours).
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistiques Principales -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Total Réclamations</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($stats['total']) ? $stats['total'] : '0'; ?></div>
                    <div class="stat-label">Toutes les réclamations</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Résolues</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($stats['resolved']) ? $stats['resolved'] : '0'; ?></div>
                    <div class="stat-label">Réclamations traitées</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>En Cours</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($stats['unresolved']) ? $stats['unresolved'] : '0'; ?></div>
                    <div class="stat-label">En traitement</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>Urgentes</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($stats['urgent']) ? $stats['urgent'] : '0'; ?></div>
                    <div class="stat-label">Priorité haute</div>
                </div>
            </div>
            
            <!-- Boutons d'actions - RENDU COMME UN LIEN -->
            <div class="btn-group-center">
                <!-- Bouton pour les Statistiques Détaillées -->
                <a href="statistiques_reclamations.php" class="btn-stats btn-stats-success">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques Détaillées</span>
                </a>
                
                <!-- Bouton Exporter les données -->
                <form action="export_reclamations.php" method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="export_type" value="csv">
                    <button type="submit" class="btn-stats" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-file-export"></i>
                        <span>Exporter les données</span>
                    </button>
                </form>
                
                <!-- Bouton Générer un rapport -->
                <form action="generer_rapport.php" method="POST" style="margin: 0; display: inline;">
                    <input type="hidden" name="report_type" value="pdf">
                    <button type="submit" class="btn-stats btn-stats-warning">
                        <i class="fas fa-file-alt"></i>
                        <span>Générer un rapport</span>
                    </button>
                </form>
            </div>
            
            <!-- Filters Bar -->
            <div class="filter-bar">
                <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                    
                    <div style="flex: 1; min-width: 250px;">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Rechercher par nom, prénom, email, téléphone..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                        >
                    </div>
                    
                    <select name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="nouveau" <?php echo $statusFilter === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                        <option value="en_cours" <?php echo $statusFilter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="résolu" <?php echo $statusFilter === 'résolu' ? 'selected' : ''; ?>>Résolu</option>
                        <option value="fermé" <?php echo $statusFilter === 'fermé' ? 'selected' : ''; ?>>Fermé</option>
                    </select>
                    
                    <select name="priority">
                        <option value="all" <?php echo $priorityFilter === 'all' ? 'selected' : ''; ?>>Toutes priorités</option>
                        <option value="haute" <?php echo $priorityFilter === 'haute' ? 'selected' : ''; ?>>Haute</option>
                        <option value="moyenne" <?php echo $priorityFilter === 'moyenne' ? 'selected' : ''; ?>>Moyenne</option>
                        <option value="basse" <?php echo $priorityFilter === 'basse' ? 'selected' : ''; ?>>Basse</option>
                    </select>
                    
                    <select name="type">
                        <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>Tous les sujets</option>
                        <option value="technique">Technique</option>
                        <option value="service">Service</option>
                        <option value="facturation">Facturation</option>
                        <option value="autre">Autre</option>
                    </select>
                    
                    <select name="sort">
                        <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Trier par date</option>
                        <option value="nom" <?php echo $sortBy === 'nom' ? 'selected' : ''; ?>>Trier par nom</option>
                        <option value="prenom" <?php echo $sortBy === 'prenom' ? 'selected' : ''; ?>>Trier par prénom</option>
                        <option value="priorite" <?php echo $sortBy === 'priorite' ? 'selected' : ''; ?>>Trier par priorité</option>
                        <option value="statut" <?php echo $sortBy === 'statut' ? 'selected' : ''; ?>>Trier par statut</option>
                    </select>
                    
                    <select name="order">
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <a href="BackofficeReclamations.php" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                    
                </form>
            </div>
            
            <!-- Table des Réclamations -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Liste des Réclamations</h2>
                    <div class="table-actions">
                        <span style="margin-right: 15px; color: #666; font-size: 14px;">
                            <i class="fas fa-filter"></i> 
                            <?php echo count($reclamations); ?> résultat(s)
                        </span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (count($reclamations) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Sujet</th>
                                <th>Description</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Réponse</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="urgent-reclamations">
                            <?php foreach ($reclamations as $reclamation): 
                                $is_urgent = $reclamation['is_urgent'] ?? false;
                                $has_response = $reclamation['has_response'] ?? false;
                                $reponse_text = $reclamation['reponse'] ?? '';
                                $id_reponse = $reclamation['id_reponse'] ?? null;
                            ?>
                                <tr <?php echo $is_urgent ? 'style="background: #ffebee;"' : ''; ?>>
                                    <td><strong>#<?php echo $reclamation['id']; ?></strong></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                                <?php echo strtoupper(substr($reclamation['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($reclamation['nom'] . ' ' . $reclamation['prenom']); ?></div>
                                                <div style="font-size: 12px; color: #bcbcbc;"><?php echo htmlspecialchars($reclamation['gouvernorat'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope" style="margin-right: 5px; color: #bcbcbc;"></i>
                                        <?php echo htmlspecialchars($reclamation['email']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone" style="margin-right: 5px; color: #bcbcbc;"></i>
                                        <?php echo htmlspecialchars($reclamation['telephone']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($reclamation['sujet'] ?? ''); ?>
                                    </td>
                                    <td class="description-cell">
                                        <?php echo htmlspecialchars($reclamation['description'] ?? ''); ?>
                                    </td>
                                    <td>
                                        <?php if ($is_urgent): ?>
                                            <span class="priority-badge priority-urgente">
                                                <i class="fas fa-exclamation-circle"></i> <?php echo ucfirst($reclamation['priorite'] ?? 'Urgente'); ?>
                                            </span>
                                        <?php else: ?>
                                            <?php 
                                            $priority = strtolower($reclamation['priorite'] ?? 'moyenne');
                                            if (in_array($priority, ['haute', 'high'])) $priority = 'haute';
                                            elseif (in_array($priority, ['moyenne', 'medium'])) $priority = 'moyenne';
                                            elseif (in_array($priority, ['basse', 'low'])) $priority = 'basse';
                                            ?>
                                            <span class="priority-badge priority-<?php echo $priority; ?>">
                                                <?php echo ucfirst($reclamation['priorite'] ?? 'Moyenne'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = strtolower($reclamation['statut'] ?? 'nouveau');
                                        if (in_array($status, ['nouveau', 'new'])) $status = 'nouveau';
                                        elseif (in_array($status, ['en_cours', 'en cours', 'in_progress', 'in progress'])) $status = 'en_cours';
                                        elseif (in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'traitée', 'traitee', 'resolved', 'closed'])) $status = 'resolu';
                                        elseif (in_array($status, ['rejetée', 'rejetee', 'rejected'])) $status = 'rejetée';
                                        
                                        $status_display = ucfirst(str_replace('_', ' ', $reclamation['statut'] ?? 'Nouveau'));
                                        ?>
                                        <span class="status-badge status-<?php echo $status; ?>">
                                            <?php echo $status_display; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($has_response && !empty($reponse_text)): 
                                            $response_preview = substr($reponse_text, 0, 50);
                                            $full_response = htmlspecialchars($reponse_text);
                                        ?>
                                            <div class="response-status">
                                                <span class="response-yes">✓ Répondu</span>
                                                <span class="response-preview" title="<?php echo $full_response; ?>">
                                                    <?php echo htmlspecialchars($response_preview) . (strlen($reponse_text) > 50 ? '...' : ''); ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <span class="response-no">- sans réponse -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="far fa-calendar" style="margin-right: 5px; color: #bcbcbc;"></i>
                                        <?php 
                                            $date = $reclamation['date'] ?? $reclamation['date_creation'] ?? '';
                                            echo !empty($date) ? date('d/m/Y', strtotime($date)) : '-'; 
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($has_response): ?>
                                                <a href="ListeReponses.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-info btn-sm" title="Voir réponse">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($id_reponse): ?>
                                                    <a href="Updatereponce.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-primary btn-sm" title="Modifier réponse">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="rependreReclamation.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-success btn-sm" title="Répondre">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a 
                                                href="suppreponce.php?id=<?php echo $reclamation['id']; ?>" 
                                                class="btn btn-danger btn-sm"
                                                title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation et sa réponse ?');"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; padding: 60px 20px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 64px; display: block; margin-bottom: 20px; opacity: 0.5;"></i>
                        <strong style="display: block; font-size: 18px; margin-bottom: 10px;">Aucune réclamation trouvée</strong>
                        <?php if (!empty($search) || $statusFilter !== 'all' || $priorityFilter !== 'all'): ?>
                        <span style="font-size: 14px;">Essayez de modifier vos critères de recherche</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success messages
            const successAlert = document.querySelector('.success-message');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.transition = 'opacity 0.5s ease';
                    successAlert.style.opacity = '0';
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
            
            // Animation pour les badges urgents
            const urgentBadges = document.querySelectorAll('.priority-urgente');
            urgentBadges.forEach(badge => {
                badge.style.animation = 'blink 1.5s infinite';
            });
            
            // Scroll vers les réclamations urgentes
            const alertLink = document.querySelector('.urgent-alert-link');
            if (alertLink) {
                alertLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.getElementById('urgent-reclamations');
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                        target.querySelectorAll('tr').forEach(row => {
                            if (row.style.background === 'rgb(255, 235, 238)') {
                                row.style.transition = 'background 0.5s';
                                row.style.background = '#fff0f0';
                                setTimeout(() => {
                                    row.style.background = '#ffebee';
                                }, 1000);
                            }
                        });
                    }
                });
            }
            
            // Gestion des descriptions au survol
            const descriptionCells = document.querySelectorAll('.description-cell');
            descriptionCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    this.style.position = 'absolute';
                    this.style.zIndex = '100';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                });
                
                cell.addEventListener('mouseleave', function() {
                    this.style.position = '';
                    this.style.zIndex = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
    
</body>
</html>