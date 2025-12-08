<?php
// Connexion à la base de données
require_once '../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}
require_once '../../controllers/participationController.php';

$success = '';
$error = '';
$evenements = [];
$participations = [];

// Récupérer les messages de session
if (isset($_SESSION['evenements_success'])) {
    $success = $_SESSION['evenements_success'];
    unset($_SESSION['evenements_success']);
}

if (isset($_SESSION['evenements_message'])) {
    $error = $_SESSION['evenements_message'];
    unset($_SESSION['evenements_message']);
}

// Récupérer les messages de participation
if (isset($_SESSION['participation_success'])) {
    $success = $_SESSION['participation_success'];
    unset($_SESSION['participation_success']);
}

if (isset($_SESSION['participation_message'])) {
    $error = $_SESSION['participation_message'];
    unset($_SESSION['participation_message']);
}

// Gestion des erreurs de formulaire
$formErrors = [];
if (isset($_SESSION['evenements_errors'])) {
    $formErrors = $_SESSION['evenements_errors'];
    unset($_SESSION['evenements_errors']);
}

// Récupération des paramètres de recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$organisateurFilter = isset($_GET['organisateur']) ? $_GET['organisateur'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Récupération des paramètres de recherche et tri pour les participations
$p_search = isset($_GET['p_search']) ? trim($_GET['p_search']) : '';
$p_typeFilter = isset($_GET['p_type']) ? $_GET['p_type'] : 'all';
$p_eventFilter = isset($_GET['p_event']) ? $_GET['p_event'] : 'all';
$p_sortBy = isset($_GET['p_sort']) ? $_GET['p_sort'] : 'date_inscription';
$p_order = isset($_GET['p_order']) ? $_GET['p_order'] : 'DESC';

// Construire la requête SQL
try {
    $sql = "SELECT * FROM evenements WHERE 1=1";
    $params = [];
    
    // Filtre de recherche
    if (!empty($search)) {
        $sql .= " AND (titre_evenement LIKE ? OR description LIKE ? OR organisateur LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Filtre par organisateur
    if ($organisateurFilter !== 'all') {
        $sql .= " AND organisateur = ?";
        $params[] = $organisateurFilter;
    }
    
    // Tri
    $allowedSorts = ['id_evenement', 'titre_evenement', 'date_evenement', 'organisateur', 'created_at'];
    if (in_array($sortBy, $allowedSorts)) {
        $sql .= " ORDER BY $sortBy " . ($order === 'ASC' ? 'ASC' : 'DESC');
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Compter les statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evenements");
    $totalEvenements = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evenements WHERE date_evenement >= CURDATE()");
    $totalEvenementsFuturs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evenements WHERE date_evenement < CURDATE()");
    $totalEvenementsPasses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Récupérer la liste des organisateurs uniques
    $stmt = $pdo->query("SELECT DISTINCT organisateur FROM evenements ORDER BY organisateur");
    $organisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Construire la requête SQL pour les participations
    $sql_part = "SELECT p.*, e.titre_evenement, e.date_evenement, e.organisateur, e.frais_participation,
                u.fullname, u.email
                FROM participations p 
                INNER JOIN evenements e ON p.id_evenement = e.id_evenement 
                INNER JOIN users u ON p.id_user = u.id
                WHERE 1=1";
    $params_part = [];
    
    // Filtre de recherche
    if (!empty($p_search)) {
        $sql_part .= " AND (p.motivation LIKE ? OR p.source_information LIKE ? OR u.fullname LIKE ? OR u.email LIKE ? OR e.titre_evenement LIKE ?)";
        $searchParam = "%$p_search%";
        $params_part[] = $searchParam;
        $params_part[] = $searchParam;
        $params_part[] = $searchParam;
        $params_part[] = $searchParam;
        $params_part[] = $searchParam;
    }
    
    // Filtre par type
    if ($p_typeFilter !== 'all') {
        $sql_part .= " AND p.type_participation = ?";
        $params_part[] = $p_typeFilter;
    }
    
    // Filtre par événement
    if ($p_eventFilter !== 'all') {
        $sql_part .= " AND e.titre_evenement = ?";
        $params_part[] = $p_eventFilter;
    }
    
    // Tri
    $allowedSorts_part = ['id_participation', 'date_inscription', 'type_participation', 'titre_evenement', 'fullname'];
    if (in_array($p_sortBy, $allowedSorts_part)) {
        $prefix = '';
        if ($p_sortBy === 'titre_evenement') $prefix = 'e.';
        elseif ($p_sortBy === 'fullname') $prefix = 'u.';
        else $prefix = 'p.';
        $sql_part .= " ORDER BY $prefix$p_sortBy " . ($p_order === 'ASC' ? 'ASC' : 'DESC');
    }
    
    $stmt_part = $pdo->prepare($sql_part);
    $stmt_part->execute($params_part);
    $participations = $stmt_part->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer la liste des événements uniques pour le filtre
    $stmt = $pdo->query("SELECT DISTINCT titre_evenement FROM evenements ORDER BY titre_evenement");
    $events_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Stats for participations
    $totalParticipations = count($participations);
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participations WHERE type_participation = 'seul'");
    $participationsSeul = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participations WHERE type_participation = 'groupe'");
    $participationsGroupe = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total participants (including group members)
    $stmt = $pdo->query("SELECT SUM(CASE WHEN type_participation = 'seul' THEN 1 ELSE nombre_personnes END) as total FROM participations");
    $totalParticipants = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Lunch preferences
    $stmt = $pdo->query("SELECT desir_dejeuner, COUNT(*) as count FROM participations GROUP BY desir_dejeuner");
    $lunchPrefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $lunchOui = 0;
    $lunchNon = 0;
    foreach ($lunchPrefs as $pref) {
        if ($pref['desir_dejeuner'] === 'oui') $lunchOui = $pref['count'];
        if ($pref['desir_dejeuner'] === 'non') $lunchNon = $pref['count'];
    }
    
    // Top events by participations
    $stmt = $pdo->query("SELECT e.titre_evenement, COUNT(p.id_participation) as count FROM evenements e LEFT JOIN participations p ON e.id_evenement = p.id_evenement GROUP BY e.id_evenement ORDER BY count DESC LIMIT 5");
    $topEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Événements - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
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
        
        .btn-info {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-info:hover {
            background: #138496;
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
                <a href="evenementback.php" class="active">
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
                <a href="claims.php">
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
            <h1>Gestion des Événements</h1>
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
        
        <!-- Content Area -->
        <div class="content-area">
            
            <?php if ($success): ?>
            <div style="background: #59ab6e; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i> 
                <span><?php echo $success; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i> 
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($formErrors)): ?>
            <div style="background: #f39c12; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> 
                <strong>Erreurs de validation:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($formErrors as $field => $errorMsg): ?>
                    <li><?php echo htmlspecialchars($errorMsg); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Total Événements</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalEvenements) ? $totalEvenements : '0'; ?></div>
                    <div class="stat-label">Tous les événements</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Événements à Venir</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalEvenementsFuturs) ? $totalEvenementsFuturs : '0'; ?></div>
                    <div class="stat-label">Événements futurs</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>Événements Passés</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalEvenementsPasses) ? $totalEvenementsPasses : '0'; ?></div>
                    <div class="stat-label">Événements terminés</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>Résultats</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo count($evenements); ?></div>
                    <div class="stat-label">Affichés actuellement</div>
                </div>
                
            </div>
            
            <!-- Filters Bar -->
            <div class="filter-bar">
                <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                    
                    <div style="flex: 1; min-width: 250px;">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Rechercher par titre, description ou organisateur..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                        >
                    </div>
                    
                    <select name="organisateur">
                        <option value="all" <?php echo $organisateurFilter === 'all' ? 'selected' : ''; ?>>Tous les organisateurs</option>
                        <?php if (isset($organisateurs) && !empty($organisateurs)): ?>
                            <?php foreach ($organisateurs as $org): ?>
                                <option value="<?php echo htmlspecialchars($org); ?>" <?php echo $organisateurFilter === $org ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($org); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <select name="sort">
                        <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Trier par date de création</option>
                        <option value="date_evenement" <?php echo $sortBy === 'date_evenement' ? 'selected' : ''; ?>>Trier par date d'événement</option>
                        <option value="titre_evenement" <?php echo $sortBy === 'titre_evenement' ? 'selected' : ''; ?>>Trier par titre</option>
                        <option value="organisateur" <?php echo $sortBy === 'organisateur' ? 'selected' : ''; ?>>Trier par organisateur</option>
                    </select>
                    
                    <select name="order">
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                    
                    <!-- Hidden inputs for participation filters -->
                    <input type="hidden" name="p_search" value="<?php echo htmlspecialchars($p_search); ?>">
                    <input type="hidden" name="p_type" value="<?php echo htmlspecialchars($p_typeFilter); ?>">
                    <input type="hidden" name="p_event" value="<?php echo htmlspecialchars($p_eventFilter); ?>">
                    <input type="hidden" name="p_sort" value="<?php echo htmlspecialchars($p_sortBy); ?>">
                    <input type="hidden" name="p_order" value="<?php echo htmlspecialchars($p_order); ?>">
                    
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <a href="evenementback.php" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                    
                </form>
            </div>
            
            <!-- Events Table -->
            <div class="table-container">
                <div class="table-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <h2 style="margin: 0;">Liste des Événements</h2>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="button" class="btn btn-info" onclick="toggleEventStats()">
                            <i class="fas fa-chart-pie"></i> Statistiques Événements
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openAddEventModal()">
                            <i class="fas fa-plus"></i> Ajouter un Événement
                        </button>
                    </div>
                </div>
                
                <!-- Event Stats -->
                <div id="eventStats" style="display: none; background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 20px; color: #212934;">Statistiques des Événements</h3>
                    
                    <!-- Key Metrics Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #59ab6e;">Total Événements</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #59ab6e;"><?php echo $totalEvenements; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #3498db;">Événements à Venir</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #3498db;"><?php echo $totalEvenementsFuturs; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #e74c3c;">Événements Passés</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #e74c3c;"><?php echo $totalEvenementsPasses; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #9b59b6;">Événements Actifs</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #9b59b6;"><?php echo count($evenements); ?></div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h4 style="text-align: center; margin-bottom: 15px;">Statut des Événements</h4>
                            <canvas id="eventStatusChart" width="300" height="300"></canvas>
                        </div>
                        <div style="display: flex; flex-direction: column; justify-content: center;">
                            <h4 style="margin-bottom: 15px;">Résumé</h4>
                            <div style="margin-bottom: 15px;">
                                <strong>Taux d'Événements Futurs:</strong> 
                                <?php echo $totalEvenements > 0 ? round(($totalEvenementsFuturs / $totalEvenements) * 100, 1) : 0; ?>%
                            </div>
                            <div style="margin-bottom: 15px;">
                                <strong>Taux d'Événements Passés:</strong> 
                                <?php echo $totalEvenements > 0 ? round(($totalEvenementsPasses / $totalEvenements) * 100, 1) : 0; ?>%
                            </div>
                            <div style="margin-bottom: 15px;">
                                <strong>Événements Affichés:</strong> <?php echo count($evenements); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (count($evenements) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Organisateur</th>
                                <th>Frais</th>
                                <th>Date de Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evenements as $event): ?>
                            <tr>
                                <td><strong>#<?php echo $event['id_evenement']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($event['titre_evenement']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php 
                                        $date = new DateTime($event['date_evenement']);
                                        echo $date->format('d/m/Y'); 
                                    ?>
                                </td>
                                <td>
                                    <i class="fas fa-building" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php echo htmlspecialchars($event['organisateur']); ?>
                                </td>
                                <td>
                                    <?php 
                                        $frais = floatval($event['frais_participation']);
                                        echo $frais > 0 ? number_format($frais, 2) . ' DT' : 'Gratuit';
                                    ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php 
                                        $date = new DateTime($event['created_at']);
                                        echo $date->format('d/m/Y'); 
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            type="button"
                                            class="btn btn-info btn-sm"
                                            title="Voir Détails"
                                            onclick="viewEventDetails(<?php echo $event['id_evenement']; ?>)"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button 
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            title="Modifier"
                                            onclick='openEditEventModal(<?php echo json_encode($event); ?>)'
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button 
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            title="Supprimer"
                                            onclick="deleteEvent(<?php echo $event['id_evenement']; ?>, this)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; padding: 60px 20px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 64px; display: block; margin-bottom: 20px; opacity: 0.5;"></i>
                        <strong style="display: block; font-size: 18px; margin-bottom: 10px;">Aucun événement trouvé</strong>
                        <?php if (!empty($search) || $organisateurFilter !== 'all'): ?>
                        <span style="font-size: 14px;">Essayez de modifier vos critères de recherche</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Filters Bar for Participations -->
            <div class="filter-bar" style="margin-top: 30px;">
                <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                    
                    <div style="flex: 1; min-width: 250px;">
                        <input 
                            type="text" 
                            name="p_search" 
                            placeholder="Rechercher par événement, participant, motivation..."
                            value="<?php echo htmlspecialchars($p_search); ?>"
                            style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                        >
                    </div>
                    
                    <select name="p_type">
                        <option value="all" <?php echo $p_typeFilter === 'all' ? 'selected' : ''; ?>>Tous les types</option>
                        <option value="seul" <?php echo $p_typeFilter === 'seul' ? 'selected' : ''; ?>>Seul</option>
                        <option value="groupe" <?php echo $p_typeFilter === 'groupe' ? 'selected' : ''; ?>>Groupe</option>
                    </select>
                    
                    <select name="p_event">
                        <option value="all" <?php echo $p_eventFilter === 'all' ? 'selected' : ''; ?>>Tous les événements</option>
                        <?php if (isset($events_list) && !empty($events_list)): ?>
                            <?php foreach ($events_list as $event_title): ?>
                                <option value="<?php echo htmlspecialchars($event_title); ?>" <?php echo $p_eventFilter === $event_title ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($event_title); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <select name="p_sort">
                        <option value="date_inscription" <?php echo $p_sortBy === 'date_inscription' ? 'selected' : ''; ?>>Trier par date d'inscription</option>
                        <option value="titre_evenement" <?php echo $p_sortBy === 'titre_evenement' ? 'selected' : ''; ?>>Trier par événement</option>
                        <option value="fullname" <?php echo $p_sortBy === 'fullname' ? 'selected' : ''; ?>>Trier par participant</option>
                        <option value="type_participation" <?php echo $p_sortBy === 'type_participation' ? 'selected' : ''; ?>>Trier par type</option>
                    </select>
                    
                    <select name="p_order">
                        <option value="DESC" <?php echo $p_order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        <option value="ASC" <?php echo $p_order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                    
                    <!-- Hidden inputs for event filters -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="organisateur" value="<?php echo htmlspecialchars($organisateurFilter); ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                    <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                    
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <a href="evenementback.php" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                    
                </form>
            </div>
            
            <!-- Participations Table -->
            <div class="table-container" style="margin-top: 30px;">
                <div class="table-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <h2 style="margin: 0;">Liste des Participations</h2>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="button" class="btn btn-info" onclick="toggleParticipationStats()">
                            <i class="fas fa-chart-pie"></i> Statistiques Participations
                        </button>
                    </div>
                </div>
                
                <!-- Participation Stats -->
                <div id="participationStats" style="display: none; background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 20px; color: #212934;">Statistiques des Participations</h3>
                    
                    <!-- Key Metrics Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #59ab6e;">Total Participations</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #59ab6e;"><?php echo $totalParticipations; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #3498db;">Total Participants</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #3498db;"><?php echo $totalParticipants; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #9b59b6;">Participations Seules</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #9b59b6;"><?php echo $participationsSeul; ?></div>
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                            <h4 style="margin: 0 0 10px 0; color: #e74c3c;">Participations Groupe</h4>
                            <div style="font-size: 24px; font-weight: bold; color: #e74c3c;"><?php echo $participationsGroupe; ?></div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div>
                            <h4 style="text-align: center; margin-bottom: 15px;">Type de Participation</h4>
                            <canvas id="participationTypeChart" width="300" height="300"></canvas>
                        </div>
                        <div>
                            <h4 style="text-align: center; margin-bottom: 15px;">Préférences Repas</h4>
                            <canvas id="lunchPreferenceChart" width="300" height="300"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Events Bar Chart -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="text-align: center; margin-bottom: 15px;">Événements les Plus Populaires</h4>
                        <canvas id="topEventsChart" width="600" height="300"></canvas>
                    </div>
                    
                    <!-- Additional Details -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0;">Détails Supplémentaires</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <strong>Taux de Participation Individuelle:</strong> 
                                <?php echo $totalParticipations > 0 ? round(($participationsSeul / $totalParticipations) * 100, 1) : 0; ?>%
                            </div>
                            <div>
                                <strong>Taux de Participation en Groupe:</strong> 
                                <?php echo $totalParticipations > 0 ? round(($participationsGroupe / $totalParticipations) * 100, 1) : 0; ?>%
                            </div>
                            <div>
                                <strong>Participants Demandant un Repas:</strong> 
                                <?php echo $lunchOui; ?> (<?php echo $totalParticipations > 0 ? round(($lunchOui / $totalParticipations) * 100, 1) : 0; ?>%)
                            </div>
                            <div>
                                <strong>Moyenne de Personnes par Groupe:</strong> 
                                <?php 
                                    $stmt = $pdo->query("SELECT AVG(nombre_personnes) as avg FROM participations WHERE type_participation = 'groupe'");
                                    $avgGroup = $stmt->fetch(PDO::FETCH_ASSOC)['avg'] ?? 0;
                                    echo round($avgGroup, 1);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (count($participations) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Événement</th>
                                <th>Participant</th>
                                <th>Type</th>
                                <th>Nombre</th>
                                <th>Date Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participations as $participation): ?>
                            <tr>
                                <td><strong>#<?php echo $participation['id_participation']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($participation['titre_evenement']); ?></div>
                                            <small style="color: #bcbcbc;">
                                                <i class="far fa-calendar"></i> 
                                                <?php 
                                                    $date = new DateTime($participation['date_evenement']);
                                                    echo $date->format('d/m/Y'); 
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($participation['fullname']); ?></div>
                                        <small style="color: #bcbcbc;">
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($participation['email']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span style="padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; 
                                        background: <?php echo $participation['type_participation'] === 'groupe' ? '#3498db' : '#9b59b6'; ?>; 
                                        color: white;">
                                        <?php echo ucfirst($participation['type_participation']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($participation['type_participation'] === 'groupe'): ?>
                                        <i class="fas fa-users" style="margin-right: 5px; color: #bcbcbc;"></i>
                                        <?php echo $participation['nombre_personnes']; ?> personnes
                                    <?php else: ?>
                                        <i class="fas fa-user" style="margin-right: 5px; color: #bcbcbc;"></i>
                                        Seul
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php 
                                        $date = new DateTime($participation['date_inscription']);
                                        echo $date->format('d/m/Y H:i'); 
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            title="Modifier"
                                            onclick='openEditParticipationModal(<?php echo htmlspecialchars(json_encode($participation), ENT_QUOTES); ?>)'
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button 
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            title="Supprimer"
                                            onclick="deleteParticipation(<?php echo $participation['id_participation']; ?>, this)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; padding: 60px 20px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 64px; display: block; margin-bottom: 20px; opacity: 0.5;"></i>
                        <strong style="display: block; font-size: 18px; margin-bottom: 10px;">Aucune participation trouvée</strong>
                        <?php if (!empty($p_search) || $p_typeFilter !== 'all' || $p_eventFilter !== 'all'): ?>
                        <span style="font-size: 14px;">Essayez de modifier vos critères de recherche</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Add Event Modal -->
    <div id="addEventModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-plus"></i> Ajouter un Événement</h2>
                <button type="button" class="close-modal" onclick="closeAddEventModal()">&times;</button>
            </div>
            <form id="addEventForm" action="../../controllers/evenementControllers.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_titre">
                            <i class="fas fa-heading"></i> Titre de l'Événement *
                        </label>
                        <input type="text" id="add_titre" name="titre_evenement" placeholder="Ex: Conférence sur l'Innovation">
                    </div>
                    
                    <div class="form-group">
                        <label for="add_date">
                            <i class="fas fa-calendar"></i> Date de l'Événement *
                        </label>
                        <input type="date" id="add_date" name="date_evenement">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_organisateur">
                            <i class="fas fa-building"></i> Organisateur *
                        </label>
                        <input type="text" id="add_organisateur" name="organisateur" placeholder="Ex: Club Informatique">
                    </div>
                    
                    <div class="form-group">
                        <label for="add_max_participants">
                            <i class="fas fa-users"></i> Nombre Maximum de Participants *
                        </label>
                        <input type="number" id="add_max_participants" name="max_participants" placeholder="50" min="1" max="1000" value="50">
                        <small>Entre 1 et 1000 participants</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_frais">
                            <i class="fas fa-money-bill-wave"></i> Frais de Participation (DT)
                        </label>
                        <input type="number" id="add_frais" name="frais_participation" placeholder="1.00" step="0.01" min="1" value="1">
                        <small>Minimum 1 DT</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add_description">
                        <i class="fas fa-info-circle"></i> Description *
                    </label>
                    <textarea id="add_description" name="description" placeholder="Décrivez l'événement en détail..."></textarea>
                    <small><span id="add_descriptionWordCount">0 mots</span> (minimum 10 mots)</small>
                </div>
                
                <div class="form-group">
                    <label for="add_adresse">
                        <i class="fas fa-map-marker-alt"></i> Adresse de l'Événement (optionnel)
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="add_adresse" name="adresse" placeholder="Ex: Avenue Habib Bourguiba, Tunis" readonly style="flex: 1;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearAddLocation()" title="Effacer l'emplacement">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" id="add_latitude" name="latitude">
                    <input type="hidden" id="add_longitude" name="longitude">
                    <small>Cliquez sur la carte pour sélectionner l'emplacement exact ou sur la corbeille pour l'effacer</small>
                </div>
                
                <!-- Map Container -->
                <div class="form-group">
                    <label><i class="fas fa-map"></i> Sélectionner l'emplacement sur la carte</label>
                    <div id="map" style="height: 300px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 10px;"></div>
                    <small class="text-muted">Cliquez sur la carte pour définir l'emplacement de votre événement</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddEventModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer l'Événement
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Modifier l'Événement</h2>
                <button type="button" class="close-modal" onclick="closeEditEventModal()">&times;</button>
            </div>
            <form id="editEventForm" action="../../controllers/evenementControllers.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_event_id" name="id_evenement" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_titre">
                            <i class="fas fa-heading"></i> Titre de l'Événement *
                        </label>
                        <input type="text" id="edit_titre" name="titre_evenement" placeholder="Ex: Conférence sur l'Innovation">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_date">
                            <i class="fas fa-calendar"></i> Date de l'Événement *
                        </label>
                        <input type="date" id="edit_date" name="date_evenement">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_organisateur">
                            <i class="fas fa-building"></i> Organisateur *
                        </label>
                        <input type="text" id="edit_organisateur" name="organisateur" placeholder="Ex: Club Informatique">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_max_participants">
                            <i class="fas fa-users"></i> Nombre Maximum de Participants *
                        </label>
                        <input type="number" id="edit_max_participants" name="max_participants" placeholder="50" min="1" max="1000" value="50">
                        <small>Entre 1 et 1000 participants</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_frais">
                            <i class="fas fa-money-bill-wave"></i> Frais de Participation (DT)
                        </label>
                        <input type="number" id="edit_frais" name="frais_participation" placeholder="1.00" step="0.01" min="1" value="1">
                        <small>Minimum 1 DT</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">
                        <i class="fas fa-info-circle"></i> Description *
                    </label>
                    <textarea id="edit_description" name="description" placeholder="Décrivez l'événement en détail..."></textarea>
                    <small><span id="edit_descriptionWordCount">0 mots</span> (minimum 10 mots)</small>
                </div>
                
                <div class="form-group">
                    <label for="edit_adresse">
                        <i class="fas fa-map-marker-alt"></i> Adresse de l'Événement (optionnel)
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="edit_adresse" name="adresse" placeholder="Ex: Avenue Habib Bourguiba, Tunis" readonly style="flex: 1;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearEditLocation()" title="Effacer l'emplacement">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" id="edit_latitude" name="latitude">
                    <input type="hidden" id="edit_longitude" name="longitude">
                    <small>Cliquez sur la carte pour modifier l'emplacement ou sur la corbeille pour l'effacer</small>
                </div>
                
                <!-- Map Container for Edit -->
                <div class="form-group">
                    <label><i class="fas fa-map"></i> Modifier l'emplacement sur la carte</label>
                    <div id="editMap" style="height: 300px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 10px;"></div>
                    <small class="text-muted">Cliquez sur la carte pour redéfinir l'emplacement</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditEventModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Styles -->
    <style>
        .modal {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 3% auto;
            padding: 0;
            border-radius: 12px;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9eef5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #212934;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            font-weight: bold;
            color: #bcbcbc;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: #e74c3c;
        }
        
        .modal form {
            padding: 25px;
        }
        
        .modal .form-group {
            margin-bottom: 20px;
        }
        
        .modal .form-group label {
            display: block;
            color: #212934;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .modal .form-group input,
        .modal .form-group textarea,
        .modal .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
        }
        
        .modal .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal .form-group small {
            color: #bcbcbc;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }
        
        .modal .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .modal-footer {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 1px solid #e9eef5;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .modal .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Détails de l'Événement</h2>
                <button type="button" class="close-modal" onclick="closeEventDetailsModal()">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <div id="eventDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEventDetailsModal()">
                    <i class="fas fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>
    
    <!-- Edit Participation Modal -->
    <div id="editParticipationModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Modifier la Participation</h2>
                <button type="button" class="close-modal" onclick="closeEditParticipationModal()">&times;</button>
            </div>
            <form id="editParticipationForm" action="../../controllers/participationController.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_participation_id" name="id_participation" value="">
                
                <div class="modal-body" style="padding: 25px;">
                    <div class="alert alert-info" style="background: #e3f2fd; color: #1976d2; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Événement:</strong> <span id="edit_participation_event_title"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_motivation">
                            <i class="fas fa-heart"></i> Motivation * 
                        </label>
                        <textarea id="edit_motivation" name="motivation" placeholder="Expliquez ce qui vous motive à participer à cet événement... (minimum 10 mots)"></textarea>
                        <small><span id="edit_motivationWordCount">0 mots</span> (minimum 10 mots)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_source_information">
                            <i class="fas fa-info-circle"></i> Comment avez-vous entendu parler de cet événement ? *
                        </label>
                        <input type="text" id="edit_source_information" name="source_information" placeholder="Ex: Réseaux sociaux, Amis, Site web...">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_type_participation">
                            <i class="fas fa-users"></i> Type de Participation *
                        </label>
                        <select id="edit_type_participation" name="type_participation">
                            <option value="seul">Seul(e)</option>
                            <option value="groupe">En Groupe</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="edit_nombre_personnes_group" style="display: none;">
                        <label for="edit_nombre_personnes">
                            <i class="fas fa-user-friends"></i> Nombre de Personnes *
                        </label>
                        <input type="number" id="edit_nombre_personnes" name="nombre_personnes" placeholder="2" value="2" min="2" max="50">
                        <small>Minimum 2 personnes pour une participation en groupe</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_desir_dejeuner">
                            <i class="fas fa-utensils"></i> Souhaitez-vous déjeuner ? *
                        </label>
                        <select id="edit_desir_dejeuner" name="desir_dejeuner">
                            <option value="non">Non</option>
                            <option value="oui">Oui</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditParticipationModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/js/evenement-back.js"></script>
    <script src="assets/js/participation-validation.js"></script>
    <script>
        // Fonctions pour gérer les modals
        function openAddEventModal() {
            document.getElementById('addEventModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddEventModal() {
            document.getElementById('addEventModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('addEventForm').reset();
            
            // Reset word count
            const counter = document.getElementById('add_descriptionWordCount');
            if (counter) {
                counter.textContent = '0 mots';
                counter.style.color = '#e74c3c';
            }
        }
        
        function openEditEventModal(event) {
            document.getElementById('edit_event_id').value = event.id_evenement;
            document.getElementById('edit_titre').value = event.titre_evenement;
            document.getElementById('edit_date').value = event.date_evenement;
            document.getElementById('edit_organisateur').value = event.organisateur;
            document.getElementById('edit_max_participants').value = event.max_participants || 50;
            document.getElementById('edit_frais').value = event.frais_participation || 1;
            document.getElementById('edit_description').value = event.description || '';
            
            // Populate address fields with existing data
            document.getElementById('edit_adresse').value = event.adresse || '';
            document.getElementById('edit_latitude').value = event.latitude || '';
            document.getElementById('edit_longitude').value = event.longitude || '';
            
            // Update word count for edit form
            const editDescription = document.getElementById('edit_description');
            if (editDescription) {
                const wordCount = editDescription.value.trim().split(/\s+/).filter(word => word.length > 0).length;
                const counter = document.getElementById('edit_descriptionWordCount');
                if (counter) {
                    counter.textContent = `${wordCount} mots`;
                    counter.style.color = wordCount >= 10 ? '#59ab6e' : '#e74c3c';
                }
            }
            
            document.getElementById('editEventModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditEventModal() {
            document.getElementById('editEventModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Fermer les modals en cliquant en dehors
        window.onclick = function(event) {
            const addModal = document.getElementById('addEventModal');
            const editModal = document.getElementById('editEventModal');
            if (event.target == addModal) {
                closeAddEventModal();
            }
            if (event.target == editModal) {
                closeEditEventModal();
            }
        }
        
        // Auto-hide success messages after 3 seconds
        const successAlert = document.querySelector('.content-area > div[style*="background: #59ab6e"]');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(function() {
                    successAlert.remove();
                }, 500);
            }, 3000);
        }
        
        // Edit Participation Modal Functions
        function openEditParticipationModal(participation) {
            document.getElementById('edit_participation_id').value = participation.id_participation;
            document.getElementById('edit_participation_event_title').textContent = participation.titre_evenement;
            document.getElementById('edit_motivation').value = participation.motivation || '';
            document.getElementById('edit_source_information').value = participation.source_information || '';
            document.getElementById('edit_type_participation').value = participation.type_participation || 'seul';
            document.getElementById('edit_nombre_personnes').value = participation.nombre_personnes || 1;
            document.getElementById('edit_desir_dejeuner').value = participation.desir_dejeuner || 'non';
            
            // Toggle nombre personnes group based on type
            const nombreGroup = document.getElementById('edit_nombre_personnes_group');
            const nombreInput = document.getElementById('edit_nombre_personnes');
            const typeParticipation = document.getElementById('edit_type_participation');
            
            // Set up type_participation change handler to toggle nombre group
            if (typeParticipation) {
                typeParticipation.addEventListener('change', function() {
                    if (this.value === 'groupe') {
                        nombreGroup.style.display = 'block';
                        nombreInput.removeAttribute('disabled');
                        nombreInput.setAttribute('name', 'nombre_personnes');
                    } else {
                        nombreGroup.style.display = 'none';
                        nombreInput.setAttribute('disabled', 'disabled');
                        nombreInput.removeAttribute('name');
                    }
                });
                
                // Set initial state
                if (participation.type_participation === 'groupe') {
                    nombreGroup.style.display = 'block';
                    nombreInput.removeAttribute('disabled');
                    nombreInput.setAttribute('name', 'nombre_personnes');
                } else {
                    nombreGroup.style.display = 'none';
                    nombreInput.setAttribute('disabled', 'disabled');
                    nombreInput.removeAttribute('name');
                }
            }
            
            // Update word count
            const editMotivation = document.getElementById('edit_motivation');
            if (editMotivation) {
                const wordCount = editMotivation.value.trim().split(/\s+/).filter(word => word.length > 0).length;
                const counter = document.getElementById('edit_motivationWordCount');
                if (counter) {
                    counter.textContent = `${wordCount} mots`;
                    counter.style.color = wordCount >= 10 ? '#59ab6e' : '#e74c3c';
                }
            }
            
            document.getElementById('editParticipationModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditParticipationModal() {
            document.getElementById('editParticipationModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Event Details Modal Functions
        function viewEventDetails(eventId) {
            document.getElementById('eventDetailsModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Show loading
            document.getElementById('eventDetailsContent').innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            `;
            
            // Fetch event details via AJAX
            fetch('../../controllers/evenementControllers.php?action=get_event_details&id=' + eventId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('eventDetailsContent').innerHTML = data.html;
                    } else {
                        document.getElementById('eventDetailsContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Erreur: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('eventDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Erreur de connexion
                        </div>
                    `;
                });
        }

        function closeEventDetailsModal() {
            document.getElementById('eventDetailsModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addEventModal');
            const editModal = document.getElementById('editEventModal');
            const editParticipationModal = document.getElementById('editParticipationModal');
            const eventDetailsModal = document.getElementById('eventDetailsModal');
            if (event.target == addModal) {
                closeAddEventModal();
            }
            if (event.target == editModal) {
                closeEditEventModal();
            }
            if (event.target == editParticipationModal) {
                closeEditParticipationModal();
            }
            if (event.target == eventDetailsModal) {
                closeEventDetailsModal();
            }
        }
        
        // Function to delete event via AJAX
        function deleteEvent(eventId, buttonElement) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet événement? Cette action est irréversible.')) {
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', eventId);
            
            // Find the delete button to show loading state
            const deleteBtn = buttonElement || document.querySelector(`button[onclick*="deleteEvent(${eventId}"]`);
            if (!deleteBtn) {
                // Try to find by event ID in the row
                const rows = document.querySelectorAll('tbody tr');
                for (let row of rows) {
                    const idCell = row.querySelector('td strong');
                    if (idCell && idCell.textContent.includes('#' + eventId)) {
                        deleteBtn = row.querySelector('.btn-danger');
                        break;
                    }
                }
            }
            
            if (deleteBtn) {
                const originalHTML = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                deleteBtn.disabled = true;
                
                // Send AJAX request
                fetch('../../controllers/evenementControllers.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage(data.message, 'success');
                        // Remove the row from table
                        const row = deleteBtn.closest('tr');
                        if (row) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                // Reload page to update statistics
                                window.location.reload();
                            }, 300);
                        }
                    } else {
                        // Show error message
                        showMessage(data.message, 'error');
                        deleteBtn.innerHTML = originalHTML;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Une erreur est survenue lors de la suppression.', 'error');
                    deleteBtn.innerHTML = originalHTML;
                    deleteBtn.disabled = false;
                });
            }
        }
        
        // Function to show messages
        function showMessage(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.ajax-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ajax-message';
            messageDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease;
            `;
            
            if (type === 'success') {
                messageDiv.style.background = '#59ab6e';
                messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            } else {
                messageDiv.style.background = '#e74c3c';
                messageDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            }
            
            document.body.appendChild(messageDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                messageDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => messageDiv.remove(), 300);
            }, 3000);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Function to delete participation via AJAX
        function deleteParticipation(participationId, buttonElement) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette participation? Cette action est irréversible.')) {
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', participationId);
            
            // Find the delete button to show loading state
            const deleteBtn = buttonElement;
            if (deleteBtn) {
                const originalHTML = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                deleteBtn.disabled = true;
                
                // Send AJAX request
                fetch('../../controllers/participationController.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage(data.message, 'success');
                        // Remove the row from table
                        const row = deleteBtn.closest('tr');
                        if (row) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                // Reload page to update statistics
                                window.location.reload();
                            }, 300);
                        }
                    } else {
                        // Show error message
                        showMessage(data.message, 'error');
                        deleteBtn.innerHTML = originalHTML;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Une erreur est survenue lors de la suppression.', 'error');
                    deleteBtn.innerHTML = originalHTML;
                    deleteBtn.disabled = false;
                });
            }
        }
        
        // Toggle event stats
        function toggleEventStats() {
            const statsDiv = document.getElementById('eventStats');
            if (statsDiv.style.display === 'none' || statsDiv.style.display === '') {
                statsDiv.style.display = 'block';
                renderEventChart();
            } else {
                statsDiv.style.display = 'none';
            }
        }
        
        // Toggle participation stats
        function toggleParticipationStats() {
            const statsDiv = document.getElementById('participationStats');
            if (statsDiv.style.display === 'none' || statsDiv.style.display === '') {
                statsDiv.style.display = 'block';
                renderParticipationChart();
            } else {
                statsDiv.style.display = 'none';
            }
        }
        
        // Render event status chart
        function renderEventChart() {
            const ctx = document.getElementById('eventStatusChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Événements à Venir', 'Événements Passés'],
                    datasets: [{
                        data: [<?php echo $totalEvenementsFuturs; ?>, <?php echo $totalEvenementsPasses; ?>],
                        backgroundColor: ['#59ab6e', '#e74c3c'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
        
        // Render participation type chart
        function renderParticipationChart() {
            const ctx = document.getElementById('participationTypeChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Participations Seules', 'Participations en Groupe'],
                    datasets: [{
                        data: [<?php echo $participationsSeul; ?>, <?php echo $participationsGroupe; ?>],
                        backgroundColor: ['#3498db', '#9b59b6'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
            
            // Lunch preference chart
            const lunchCtx = document.getElementById('lunchPreferenceChart').getContext('2d');
            new Chart(lunchCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Repas Demandé', 'Pas de Repas'],
                    datasets: [{
                        data: [<?php echo $lunchOui; ?>, <?php echo $lunchNon; ?>],
                        backgroundColor: ['#59ab6e', '#e74c3c'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
            
            // Top events bar chart
            const topEventsCtx = document.getElementById('topEventsChart').getContext('2d');
            const eventLabels = [<?php echo '"' . implode('","', array_column($topEvents, 'titre_evenement')) . '"'; ?>];
            const eventData = [<?php echo implode(',', array_column($topEvents, 'count')); ?>];
            
            new Chart(topEventsCtx, {
                type: 'bar',
                data: {
                    labels: eventLabels,
                    datasets: [{
                        label: 'Nombre de Participations',
                        data: eventData,
                        backgroundColor: '#59ab6e',
                        borderColor: '#59ab6e',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Map functionality
        let map = null;
        let editMap = null;
        let marker = null;
        let editMarker = null;
        
        // Initialize maps when modals are opened
        function initializeMap() {
            if (map === null) {
                // Center on Tunisia (approximately Tunis coordinates)
                map = L.map('map').setView([36.8065, 10.1815], 10);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                
                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    // Remove existing marker
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    // Add new marker
                    marker = L.marker([lat, lng]).addTo(map);
                    
                    // Update form fields
                    document.getElementById('add_latitude').value = lat;
                    document.getElementById('add_longitude').value = lng;
                    
                    // Reverse geocoding to get address
                    reverseGeocode(lat, lng, 'add_adresse');
                });
            }
        }
        
        function initializeEditMap(lat = 36.8065, lng = 10.1815, existingAddress = '') {
            if (editMap) {
                editMap.remove();
            }
            
            // Set zoom level based on whether we have existing coordinates
            const hasExistingLocation = lat !== 36.8065 || lng !== 10.1815;
            const zoomLevel = hasExistingLocation ? 15 : 10;
            
            editMap = L.map('editMap').setView([lat, lng], zoomLevel);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(editMap);
            
            // Add existing marker if coordinates exist
            if (hasExistingLocation) {
                editMarker = L.marker([lat, lng]).addTo(editMap);
                
                // Add popup showing existing address
                if (existingAddress) {
                    editMarker.bindPopup(`<b>Emplacement actuel:</b><br>${existingAddress}`).openPopup();
                }
            }
            
            editMap.on('click', function(e) {
                const newLat = e.latlng.lat;
                const newLng = e.latlng.lng;
                
                // Remove existing marker
                if (editMarker) {
                    editMap.removeLayer(editMarker);
                }
                
                // Add new marker
                editMarker = L.marker([newLat, newLng]).addTo(editMap);
                
                // Update form fields
                document.getElementById('edit_latitude').value = newLat;
                document.getElementById('edit_longitude').value = newLng;
                
                // Reverse geocoding to get address
                reverseGeocode(newLat, newLng, 'edit_adresse');
            });
        }
        
        // Reverse geocoding using Nominatim (free OpenStreetMap service)
        function reverseGeocode(lat, lng, targetFieldId) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById(targetFieldId).value = data.display_name;
                    }
                })
                .catch(error => {
                    console.log('Geocoding error:', error);
                    document.getElementById(targetFieldId).value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        }
        
        // Override the existing modal functions to initialize maps
        const originalOpenAddEventModal = window.openAddEventModal;
        window.openAddEventModal = function() {
            originalOpenAddEventModal();
            setTimeout(initializeMap, 100); // Small delay to ensure modal is visible
        };
        
        // Clear location functions
        function clearAddLocation() {
            document.getElementById('add_adresse').value = '';
            document.getElementById('add_latitude').value = '';
            document.getElementById('add_longitude').value = '';
            
            if (marker && map) {
                map.removeLayer(marker);
                marker = null;
            }
        }
        
        function clearEditLocation() {
            document.getElementById('edit_adresse').value = '';
            document.getElementById('edit_latitude').value = '';
            document.getElementById('edit_longitude').value = '';
            
            if (editMarker && editMap) {
                editMap.removeLayer(editMarker);
                editMarker = null;
            }
        }

        const originalOpenEditEventModal = window.openEditEventModal;
        window.openEditEventModal = function(event) {
            originalOpenEditEventModal(event);
            const lat = event.latitude && event.latitude !== '' ? parseFloat(event.latitude) : 36.8065;
            const lng = event.longitude && event.longitude !== '' ? parseFloat(event.longitude) : 10.1815;
            setTimeout(() => initializeEditMap(lat, lng, event.adresse || ''), 100);
        };
    </script>
    
</body>
</html>

