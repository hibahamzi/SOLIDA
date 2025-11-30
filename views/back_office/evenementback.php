<?php
session_start();

// Vérifier si l'utilisateur est connecté et est admin
// Décommentez ces lignes lorsque vous avez le système d'authentification
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../front_office/sign-in.php');
//     exit();
// }

// Connexion à la base de données
require_once '../../config/config.php';
require_once '../../controllers/participationController.php';
require_once '../Model/Reclamation.php'; 
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
    
    // Récupérer toutes les participations
    $participationController = new participationController();
    $participations = $participationController->getAllParticipations();
    
    // Récupérer les statistiques
    $participationStats = $participationController->getStatistics();
    
    // Récupérer les événements et utilisateurs pour les formulaires
    $events = $participationController->getAllEvents();
    $users = $participationController->getAllUsers();
    
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
            
            <!-- Participation Statistics -->
            <?php if (isset($participationStats) && !empty($participationStats)): ?>
            <div class="stats-grid" style="margin-top: 30px;">
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Total Participations</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-hand-paper"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($participationStats['total']) ? $participationStats['total'] : '0'; ?></div>
                    <div class="stat-label">Toutes les participations</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Total Participants</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($participationStats['total_participants']) ? $participationStats['total_participants'] : '0'; ?></div>
                    <div class="stat-label">Personnes inscrites</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>Avec Déjeuner</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($participationStats['with_lunch']) ? $participationStats['with_lunch'] : '0'; ?></div>
                    <div class="stat-label">Participations avec déjeuner</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>En Groupe</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                    </div>
                    <div class="stat-number">
                        <?php 
                            $groupeCount = 0;
                            if (isset($participationStats['by_type'])) {
                                foreach ($participationStats['by_type'] as $type) {
                                    if ($type['type_participation'] === 'groupe') {
                                        $groupeCount = $type['count'];
                                        break;
                                    }
                                }
                            }
                            echo $groupeCount;
                        ?>
                    </div>
                    <div class="stat-label">Participations en groupe</div>
                </div>
            </div>
            <?php endif; ?>
            
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
                <div class="table-header">
                    <h2>Liste des Événements</h2>
                    <button type="button" class="btn btn-primary" onclick="openAddEventModal()">
                        <i class="fas fa-plus"></i> Ajouter un Événement
                    </button>
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
            
            <!-- Participations Table -->
            <div class="table-container" style="margin-top: 30px;">
                <div class="table-header">
                    <h2>Liste des Participations</h2>
                    <button type="button" class="btn btn-primary" onclick="openAddParticipationModal()">
                        <i class="fas fa-plus"></i> Ajouter une Participation
                    </button>
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
                        <label for="add_frais">
                            <i class="fas fa-money-bill-wave"></i> Frais de Participation (DT)
                        </label>
                        <input type="number" id="add_frais" name="frais_participation" placeholder="1.00" step="0.01" value="1">
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
                        <label for="edit_frais">
                            <i class="fas fa-money-bill-wave"></i> Frais de Participation (DT)
                        </label>
                        <input type="number" id="edit_frais" name="frais_participation" placeholder="1.00" step="0.01" value="1">
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
    
    <!-- Add Participation Modal -->
    <div id="addParticipationModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-plus"></i> Ajouter une Participation</h2>
                <button type="button" class="close-modal" onclick="closeAddParticipationModal()">&times;</button>
            </div>
            <form id="addParticipationForm" action="../../controllers/participationController.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-body" style="padding: 25px;">
                    <div class="form-group">
                        <label for="add_participation_event">
                            <i class="fas fa-calendar"></i> Événement *
                        </label>
                        <select id="add_participation_event" name="id_evenement">
                            <option value="">Sélectionner un événement</option>
                            <?php foreach ($events as $event): ?>
                                <option value="<?php echo $event['id_evenement']; ?>">
                                    <?php echo htmlspecialchars($event['titre_evenement']); ?> 
                                    (<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_participation_user">
                            <i class="fas fa-user"></i> Participant *
                        </label>
                        <select id="add_participation_user" name="id_user">
                            <option value="">Sélectionner un utilisateur</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['fullname']); ?> 
                                    (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_motivation">
                            <i class="fas fa-heart"></i> Motivation * 
                        </label>
                        <textarea id="add_motivation" name="motivation" placeholder="Expliquez ce qui motive cette participation... (minimum 10 mots)"></textarea>
                        <small><span id="add_motivationWordCount">0 mots</span> (minimum 10 mots)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_source_information">
                            <i class="fas fa-info-circle"></i> Source d'information *
                        </label>
                        <input type="text" id="add_source_information" name="source_information" placeholder="Ex: Réseaux sociaux, Amis, Site web...">
                    </div>
                    
                    <div class="form-group">
                        <label for="add_type_participation">
                            <i class="fas fa-users"></i> Type de Participation *
                        </label>
                        <select id="add_type_participation" name="type_participation">
                            <option value="seul">Seul(e)</option>
                            <option value="groupe">En Groupe</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="add_nombre_personnes_group" style="display: none;">
                        <label for="add_nombre_personnes">
                            <i class="fas fa-user-friends"></i> Nombre de Personnes *
                        </label>
                        <input type="number" id="add_nombre_personnes" name="nombre_personnes" placeholder="2" value="2">
                        <small>Minimum 2 personnes pour une participation en groupe</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_desir_dejeuner">
                            <i class="fas fa-utensils"></i> Souhaite déjeuner ? *
                        </label>
                        <select id="add_desir_dejeuner" name="desir_dejeuner">
                            <option value="non">Non</option>
                            <option value="oui">Oui</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddParticipationModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer la Participation
                    </button>
                </div>
            </form>
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
                        <input type="number" id="edit_nombre_personnes" name="nombre_personnes" placeholder="2" value="2">
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
            document.getElementById('edit_frais').value = event.frais_participation || 1;
            document.getElementById('edit_description').value = event.description || '';
            
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
        
        // Add Participation Modal Functions
        function openAddParticipationModal() {
            document.getElementById('addParticipationModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddParticipationModal() {
            document.getElementById('addParticipationModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            const form = document.getElementById('addParticipationForm');
            if (form) {
                form.reset();
            }
            
            // Reset word count
            const counter = document.getElementById('add_motivationWordCount');
            if (counter) {
                counter.textContent = '0 mots';
                counter.style.color = '#e74c3c';
            }
            
            // Hide nombre personnes group
            const nombreGroup = document.getElementById('add_nombre_personnes_group');
            if (nombreGroup) {
                nombreGroup.style.display = 'none';
            }
        }
        
        // Fermer les modals en cliquant en dehors
        window.onclick = function(event) {
            const addModal = document.getElementById('addEventModal');
            const editModal = document.getElementById('editEventModal');
            const addParticipationModal = document.getElementById('addParticipationModal');
            const editParticipationModal = document.getElementById('editParticipationModal');
            if (event.target == addModal) {
                closeAddEventModal();
            }
            if (event.target == editModal) {
                closeEditEventModal();
            }
            if (event.target == addParticipationModal) {
                closeAddParticipationModal();
            }
            if (event.target == editParticipationModal) {
                closeEditParticipationModal();
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
        
        // Add Participation Modal Functions
        function openAddParticipationModal() {
            document.getElementById('addParticipationModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddParticipationModal() {
            document.getElementById('addParticipationModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('addParticipationForm').reset();
            
            // Reset word count
            const counter = document.getElementById('add_motivationWordCount');
            if (counter) {
                counter.textContent = '0 mots';
                counter.style.color = '#e74c3c';
            }
            
            // Hide nombre personnes group
            const nombreGroup = document.getElementById('add_nombre_personnes_group');
            if (nombreGroup) {
                nombreGroup.style.display = 'none';
            }
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
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addEventModal');
            const editModal = document.getElementById('editEventModal');
            const editParticipationModal = document.getElementById('editParticipationModal');
            if (event.target == addModal) {
                closeAddEventModal();
            }
            if (event.target == editModal) {
                closeEditEventModal();
            }
            if (event.target == editParticipationModal) {
                closeEditParticipationModal();
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
    </script>
    
</body>
</html>

