<?php
require_once __DIR__ . '/../../controllers/ReclamationController.php';

// Créer une instance de ReclamationController
$reclamationController = new ReclamationController();

// Récupérer les statistiques
$stats = $reclamationController->getStatistics();
$processingTime = $reclamationController->getAverageProcessingTime();

// Récupérer les réclamations urgentes (avec gestion d'erreur)
try {
    $urgentReclamations = $reclamationController->getUrgentReclamations();
    if (!is_array($urgentReclamations)) {
        $urgentReclamations = [];
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des réclamations urgentes: " . $e->getMessage());
    $urgentReclamations = [];
}

$overdueReclamations = $reclamationController->getOverdueReclamations(7);
$resolutionByGovernorate = $reclamationController->getResolutionRateByGovernorate();

// Gestion de la recherche avancée
$filters = [];

// Traitement du champ nom_prenom (peut contenir nom seul ou nom + prénom séparés par espace)
if (isset($_GET['nom_prenom']) && trim($_GET['nom_prenom']) !== '') {
    $nom_prenom = trim($_GET['nom_prenom']);
    $parts = preg_split('/\s+/', $nom_prenom, 2); // Séparer par espace(s)
    
    if (count($parts) >= 2 && strtolower(trim($parts[0])) !== strtolower(trim($parts[1]))) {
        // Nom + Prénom différents (recherche précise : les deux doivent correspondre)
        $filters['nom'] = trim($parts[0]);
        $filters['prenom'] = trim($parts[1]);
    } else {
        // Un seul mot OU deux mots identiques : chercher dans nom OU prénom
        // On utilise un filtre spécial pour chercher dans les deux colonnes
        $filters['nom_ou_prenom'] = trim($parts[0]);
    }
}

if (isset($_GET['email']) && trim($_GET['email']) !== '') $filters['email'] = trim($_GET['email']);

// Récupérer les réclamations (avec filtres si présents)
$searchDebug = '';
if (!empty($filters)) {
    $reclamations = $reclamationController->searchReclamations($filters);
    // Debug: récupérer les logs
    $searchDebug = "Filtres appliqués: " . print_r($filters, true);
    if (empty($reclamations) && !empty($filters)) {
        error_log("Aucun résultat trouvé avec les filtres: " . print_r($filters, true));
    }
} else {
    $reclamations = $reclamationController->getReclamation();
}

// Construire le tableau des réponses à partir des résultats de la jointure
$responses = [];
foreach ($reclamations as $rec) {
    if (!empty($rec['reponse_id']) && !empty($rec['reponse_text'])) {
        $responses[$rec['id']] = [
            'id' => $rec['reponse_id'],
            'reponse' => $rec['reponse_text'],
            'date' => $rec['reponse_date'] ?? null,
            'statut' => $rec['reponse_statut'] ?? null
        ];
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back-Office - Gestion des Réclamations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #111010ff;
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #4CAF50;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        /* Messages d'alerte */
        .alert {
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        
        .alert-success {
            border-left-color: #4CAF50;
            color: #2E7D32;
        }
        
        .alert-warning {
            border-left-color: #FFC107;
            color: #F57C00;
        }
        
        .alert-info {
            border-left-color: #2196F3;
            color: #1565C0;
        }
        
        .alert-danger {
            border-left-color: #F44336;
            color: #C62828;
        }
        
        /* Cartes de statistiques */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card h3 {
            color: #4CAF50;
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .stat-card p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #4CAF50;
            padding: 20px 25px;
            color: white;
        }
        
        .table-header h2 {
            font-size: 20px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #303636ff;
        }
        
        th {
            color: #333;
            padding: 16px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        
        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        td {
            padding: 16px 15px;
            font-size: 14px;
            color: #555;
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
            box-shadow: 0 0 10px rgba(244, 67, 54, 0.5);
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
        
        .status-resolu, .status-Traitée {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-ferme, .status-Rejetée {
            background: #ffebee;
            color: #c62828;
        }
        
        /* Boutons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        
        .btn-respond {
            background: #4CAF50;
            color: white;
        }
        
        .btn-respond:hover {
            background: #45a049;
        }
        
        .btn-update {
            background: #2196F3;
            color: white;
        }
        
        .btn-update:hover {
            background: #1976D2;
        }
        
        .btn-delete:hover {
            background: #45a049;
        }
        
        /* Response Status */
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
        }
        
        .response-no {
            color: #999;
            font-size: 13px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h2 {
            margin-bottom: 10px;
            color: #333;
            font-size: 20px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            table {
                font-size: 13px;
            }
            
            th, td {
                padding: 12px 10px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 1000px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
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
                <a href="dashboard.php" class="active">
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
                <a href="BackofficeReclamations.php">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Réclamations</span>
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
            <h1>Tableau de Bord</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span>Administrateur</span>
                    <small>admin@solida.com</small>
                </div>
            </div>
        </div>
        
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Back-Office - Gestion des Réclamations</h1>
            <p>Gérez et répondez aux réclamations soumises par les utilisateurs</p>
        </div>
        
        <!-- Messages de succès -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert <?php 
                if ($_GET['success'] === 'responded_and_emailed') echo 'alert-success';
                elseif ($_GET['success'] === 'response_updated') echo 'alert-success';
                elseif ($_GET['success'] === 'deleted') echo 'alert-info';
                else echo 'alert-warning';
            ?>">
                <span style="font-size: 18px;">
                    <?php if ($_GET['success'] === 'responded_and_emailed'): ?>✅
                    <?php elseif ($_GET['success'] === 'response_updated'): ?>✅
                    <?php elseif ($_GET['success'] === 'deleted'): ?>✅
                    <?php else: ?>⚠️<?php endif; ?>
                </span>
                <span>
                    <?php if ($_GET['success'] === 'responded_and_emailed'): ?>
                        Réponse enregistrée avec succès et email envoyé au client !
                    <?php elseif ($_GET['success'] === 'response_updated'): ?>
                        Réponse modifiée avec succès !
                    <?php elseif ($_GET['success'] === 'responded_no_email'): ?>
                        Réponse enregistrée avec succès, mais l'email n'a pas pu être envoyé (email invalide ou erreur d'envoi).
                    <?php elseif ($_GET['success'] === 'deleted'): ?>
                        Réclamation et réponse supprimées avec succès !
                    <?php else: ?>
                        Réponse enregistrée avec succès !
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>
        
        <!-- Messages d'erreur -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <span style="font-size: 18px;">❌</span>
                <span>
                    <?php if ($_GET['error'] === 'delete_failed'): ?>
                        Erreur lors de la suppression de la réclamation. Veuillez réessayer.
                    <?php elseif ($_GET['error'] === 'invalid_id'): ?>
                        ID de réclamation invalide.
                    <?php elseif ($_GET['error'] === 'response_not_found'): ?>
                        Réponse non trouvée.
                    <?php else: ?>
                        Une erreur s'est produite.
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques Métier -->
        <?php if (isset($stats) && !empty($stats)): ?>
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo isset($stats['total']) ? $stats['total'] : '0'; ?></h3>
                <p> Réclamations totales</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($stats['resolved']) ? $stats['resolved'] : '0'; ?></h3>
                <p>Réclamations résolues</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($stats['unresolved']) ? $stats['unresolved'] : '0'; ?></h3>
                <p> Réclamations en cours</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($stats['resolution_rate']) ? $stats['resolution_rate'] : '0'; ?>%</h3>
                <p> Taux de résolution</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($stats['urgent']) ? $stats['urgent'] : '0'; ?></h3>
                <p> Réclamations urgentes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo isset($stats['this_week']) ? $stats['this_week'] : '0'; ?></h3>
                <p> Cette semaine</p>
            </div>
            
            <?php if (isset($processingTime) && $processingTime['average_days'] > 0): ?>
            <div class="stat-card">
                <h3><?php echo $processingTime['average_days']; ?>j</h3>
                <p> Temps moyen de traitement</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Alertes Urgentes -->
        <?php 
        // Vérifier les réclamations urgentes
        $urgent_count = 0;
        if (isset($urgentReclamations) && is_array($urgentReclamations)) {
            $urgent_count = count($urgentReclamations);
        }
        
        // Afficher l'alerte si on a des réclamations urgentes
        if ($urgent_count > 0): ?>
        <div class="alert alert-danger" style="background: #ffebee; border: 2px solid #F44336; padding: 20px; margin: 20px 0; border-radius: 8px; animation: pulse 2s infinite; position: relative; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 28px; color: #F44336; animation: blink 1s infinite;"></i>
                <div style="flex: 1;">
                    <strong style="font-size: 18px; display: block; margin-bottom: 8px;">🚨 ALERTE URGENTE !</strong>
                    <p style="margin: 0; font-size: 16px;">
                        <strong><?php echo $urgent_count; ?> réclamation(s) urgente(s)</strong> nécessitent une attention immédiate.
                    </p>
                    <div style="margin-top: 10px;">
                        <a href="#urgent-reclamations" style="background: #F44336; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; transition: background 0.3s;">
                            <i class="fas fa-arrow-down"></i> Voir les réclamations urgentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.95; transform: scale(1.01); }
            }
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
        </style>
        <?php endif; ?>
        
        <?php if (!empty($overdueReclamations)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-clock"></i>
            <strong>Retard !</strong> 
            <?php echo count($overdueReclamations); ?> réclamation(s) en retard (non résolues depuis plus de 7 jours).
        </div>
        <?php endif; ?>
        
       <!-- Filtres de Recherche Avancée -->
<div class="table-container" style="margin-bottom: 20px;">
    <div class="table-header">
        <h2>🔍 Recherche </h2>
    </div>
    <div style="padding: 25px;">
        <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 20px; align-items: end;">
            <!-- Champ Nom + Prénom -->
            <div style="display: flex; flex-direction: column;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px;">
                    <i class="fas fa-user"></i> Nom + Prénom
                </label>
                <input type="text" name="nom_prenom" placeholder="Ex: Dupont Marie ou Dupont seul..." 
                       value="<?php echo isset($_GET['nom_prenom']) ? htmlspecialchars($_GET['nom_prenom']) : ''; ?>"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
                
            </div>
            
            <!-- Champ Email -->
            <div style="display: flex; flex-direction: column;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px;">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="text" name="email" placeholder="Rechercher par email..." 
                       value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;">
            </div>
            
            <!-- Bouton Rechercher -->
            <div>
                <button type="submit" style="padding: 12px 24px; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; white-space: nowrap; font-size: 14px; height: 44px;">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
            
            <!-- Bouton Réinitialiser -->
            <div>
                <a href="BackofficeReclamations.php" style="padding: 12px 24px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-align: center; text-decoration: none; display: inline-block; white-space: nowrap; font-size: 14px; height: 44px; line-height: 20px;">
                    <i class="fas fa-redo"></i> Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>
        
        <!-- Statistiques de Base -->
        <div class="stats-container" style="display: none;">
            <div class="stat-card">
                <h3><?php echo count(array_filter($reclamations, fn($r) => ($r['statut'] ?? '') === 'Nouveau' || ($r['statut'] ?? '') === 'nouveau')); ?></h3>
                <p>🆕 Nouvelles réclamations</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($reclamations, fn($r) => ($r['statut'] ?? '') === 'En Cours' || ($r['statut'] ?? '') === 'en_cours')); ?></h3>
                <p>⚙️ En cours de traitement</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($reclamations, fn($r) => ($r['statut'] ?? '') === 'Traitée' || ($r['statut'] ?? '') === 'resolu')); ?></h3>
                <p>✅ Résolues</p>
            </div>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>📋 Liste des Réclamations</h2>
            </div>
            <?php if (empty($reclamations)): ?>
                <div class="empty-state">
                    <h2>📭 Aucune réclamation trouvée</h2>
                    <p>
                        <?php if (!empty($filters)): ?>
                            Aucune réclamation ne correspond à vos critères de recherche.
                            <?php if (isset($_GET['nom_prenom']) && !empty($_GET['nom_prenom'])): ?>
                                <br><small style="color: #999;">Recherche effectuée pour: "<?php echo htmlspecialchars($_GET['nom_prenom']); ?>"</small>
                            <?php endif; ?>
                            <?php if (isset($_GET['email']) && !empty($_GET['email'])): ?>
                                <br><small style="color: #999;">Email: "<?php echo htmlspecialchars($_GET['email']); ?>"</small>
                            <?php endif; ?>
                        <?php else: ?>
                            Il n'y a pas encore de réclamations à afficher.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($filters)): ?>
                        <a href="BackofficeReclamations.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 6px;">
                            <i class="fas fa-redo"></i> Afficher toutes les réclamations
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Gouvernorat</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Réponse</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="urgent-reclamations">
                        <?php foreach ($reclamations as $reclamation): 
                            // Vérifier si c'est une réclamation urgente
                            $is_urgent = false;
                            $priorite = strtolower(trim($reclamation['priorite'] ?? ''));
                            $statut = strtolower(trim($reclamation['statut'] ?? ''));
                            
                            // Vérifier si la priorité est urgente (gérer différentes variantes)
                            if ($priorite === 'urgente' || $priorite === 'urgent' || stripos($priorite, 'urgent') !== false) {
                                // Vérifier que le statut n'est pas résolu
                                $statuts_resolus = ['résolu', 'clôturé', 'resolu', 'cloture', 'traité', 'traite', 'traitée', 'traitee'];
                                if (!in_array($statut, $statuts_resolus) && !empty($statut)) {
                                    $is_urgent = true;
                                }
                            }
                        ?>
                            <tr <?php echo $is_urgent ? 'style="background: #ffebee; border-left: 4px solid #F44336;"' : ''; ?>>
                                <td><strong style="color: #4CAF50;">#<?php echo $reclamation['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($reclamation['nom'] ?? $reclamation['nom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['prenom'] ?? $reclamation['prenom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['email'] ?? $reclamation['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['telephone'] ?? $reclamation['telephone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['gouvernorat'] ?? ''); ?></td>
                                <td>
                                    <?php if ($is_urgent): ?>
                                        <span class="priority-badge priority-urgente" style="background: #F44336; color: white; font-weight: bold; animation: blink 1.5s infinite;">
                                            <i class="fas fa-exclamation-circle"></i> <?php echo ucfirst($reclamation['priorite'] ?? 'Urgente'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="priority-badge priority-<?php echo strtolower($reclamation['priorite'] ?? 'moyenne'); ?>">
                                            <?php echo ucfirst($reclamation['priorite'] ?? 'Moyenne'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo str_replace(' ', '_', strtolower($reclamation['statut'] ?? 'nouveau')); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $reclamation['statut'] ?? 'Nouveau')); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Utiliser les données de la jointure directement
                                    $has_response_col = !empty($reclamation['reponse_text']);
                                    $reponse_text = $reclamation['reponse_text'] ?? '';
                                    if ($has_response_col) {
                                        $response_preview = substr($reponse_text, 0, 50);
                                        $full_response = htmlspecialchars($reponse_text);
                                    }
                                    if ($has_response_col): 
                                    ?>
                                        <div class="response-status">
                                            <span class="response-yes">✓ Répondu</span>
                                            <span class="response-preview" title="<?php echo $full_response; ?>">
                                                <?php echo htmlspecialchars($response_preview) . (strlen($reponse_text) > 50 ? '...' : ''); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="response-no">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($reclamation['date'] ?? $reclamation['date'] ?? 'now')); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="rependreReclamation.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-respond">
                                            Répondre
                                        </a>
                                        <?php 
                                        // Utiliser les données de la jointure directement
                                        $has_response = !empty($reclamation['reponse_text']);
                                        $id_reponse = $reclamation['reponse_id'] ?? null;
                                        
                                        if ($has_response && $id_reponse): 
                                        ?>
                                            <a href="Updatereponce.php?id_reponse=<?php echo $id_reponse; ?>" class="btn btn-update">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
