<?php
// Connexion à la base de données
require_once '../../config/config.php';
session_start();

$evenements = [];
$error = '';
$success = '';
$user = null;
$userId = $_SESSION['user_id'] ?? null;

// Récupérer les messages de session
if (isset($_SESSION['participation_success'])) {
    $success = $_SESSION['participation_success'];
    unset($_SESSION['participation_success']);
}

if (isset($_SESSION['participation_message'])) {
    $error = $_SESSION['participation_message'];
    unset($_SESSION['participation_message']);
}

// Récupération des paramètres de recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$organisateurFilter = isset($_GET['organisateur']) ? $_GET['organisateur'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date_evenement';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Récupérer les informations de l'utilisateur si connecté
if ($userId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Récupérer les événements (seulement les événements futurs ou aujourd'hui)
try {
    $sql = "SELECT * FROM evenements WHERE date_evenement >= CURDATE()";
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
    } else {
        $sql .= " ORDER BY date_evenement ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add participant count and availability info to each event
    $processedEvents = [];
    foreach ($evenements as $index => $event) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(nombre_personnes), 0) as current_participants FROM participations WHERE id_evenement = ?");
        $stmt->execute([$event['id_evenement']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $event['current_participants'] = $result['current_participants'];
        $event['available_spots'] = $event['max_participants'] - $event['current_participants'];
        $event['is_full'] = $event['current_participants'] >= $event['max_participants'];
        
        // Check if current user already participates (if logged in)
        $event['user_participating'] = false;
        if ($userId) {
            $stmt = $pdo->prepare("SELECT id_participation FROM participations WHERE id_evenement = ? AND id_user = ?");
            $stmt->execute([$event['id_evenement'], $userId]);
            $event['user_participating'] = $stmt->fetch() !== false;
        }
        
        $processedEvents[] = $event;
    }
    $evenements = $processedEvents;
    unset($processedEvents); // Clean up
    
    // Récupérer la liste des organisateurs uniques pour le filtre
    $stmt = $pdo->query("SELECT DISTINCT organisateur FROM evenements WHERE date_evenement >= CURDATE() ORDER BY organisateur");
    $organisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Récupérer l'événement pour le modal si spécifié
$selectedEvent = null;
if (isset($_GET['modal']) && $_GET['modal'] === 'participer' && isset($_GET['event_id'])) {
    $eventId = intval($_GET['event_id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id_evenement = ?");
        $stmt->execute([$eventId]);
        $selectedEvent = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silently fail
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>SOLIDA - Événements</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/templatemo.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/event.css?v=<?php echo time(); ?>">

    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css?v=<?php echo time(); ?>">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* Dropdown menu styles */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        
        .user-dropdown-toggle:hover {
            background-color: #f8f9fa;
        }
        
        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .user-dropdown.active .user-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown-header {
            padding: 15px;
            border-bottom: 1px solid #e9eef5;
        }
        
        .user-dropdown-header p {
            margin: 0;
            color: #212934;
            font-weight: 600;
            font-size: 15px;
        }
        
        .user-dropdown-header small {
            color: #bcbcbc;
            font-size: 13px;
        }
        
        .user-dropdown-item {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #212934;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        
        .user-dropdown-item:hover {
            background-color: #f8f9fa;
            color: #e74c3c;
        }
        
        .user-dropdown-item i {
            width: 20px;
            text-align: center;
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
        
        .modal .form-group input.is-invalid,
        .modal .form-group textarea.is-invalid {
            border-color: #e74c3c;
        }
        
        .modal .form-group input.is-valid,
        .modal .form-group textarea.is-valid {
            border-color: #59ab6e;
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
        
        .modal .invalid-feedback {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
    </style>

</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Modal -->
    <div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="w-100 pt-1 mb-5 text-right">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="get" class="modal-content modal-body border-0 p-0">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="inputModalSearch" name="q" placeholder="Search ...">
                    <button type="submit" class="input-group-text bg-success text-light">
                        <i class="fa fa-fw fa-search text-white"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row text-center pt-3">
            <div class="col-lg-6 m-auto">
                <h1 class="h1">Nos Événements</h1>
                <p>
                    Découvrez et participez aux événements organisés par la communauté SOLIDA
                </p>
            </div>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success mt-4" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger mt-4" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($formErrors)): ?>
        <div class="alert alert-warning mt-4" role="alert">
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Erreurs de validation:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($formErrors as $field => $errorMsg): ?>
                <li><?php echo htmlspecialchars($errorMsg); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Filter Bar -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="search" class="form-label">
                                    <i class="fas fa-search text-success"></i> Rechercher
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="search" 
                                    name="search" 
                                    placeholder="Titre, description ou organisateur..."
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >
                            </div>
                            
                            <div class="col-md-3">
                                <label for="organisateur" class="form-label">
                                    <i class="fas fa-building text-success"></i> Organisateur
                                </label>
                                <select class="form-select" id="organisateur" name="organisateur">
                                    <option value="all" <?php echo $organisateurFilter === 'all' ? 'selected' : ''; ?>>Tous les organisateurs</option>
                                    <?php if (isset($organisateurs) && !empty($organisateurs)): ?>
                                        <?php foreach ($organisateurs as $org): ?>
                                            <option value="<?php echo htmlspecialchars($org); ?>" <?php echo $organisateurFilter === $org ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($org); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="sort" class="form-label">
                                    <i class="fas fa-sort text-success"></i> Trier par
                                </label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="date_evenement" <?php echo $sortBy === 'date_evenement' ? 'selected' : ''; ?>>Date</option>
                                    <option value="titre_evenement" <?php echo $sortBy === 'titre_evenement' ? 'selected' : ''; ?>>Titre</option>
                                    <option value="organisateur" <?php echo $sortBy === 'organisateur' ? 'selected' : ''; ?>>Organisateur</option>
                                    <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date de création</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="order" class="form-label">
                                    <i class="fas fa-arrow-up text-success"></i> Ordre
                                </label>
                                <select class="form-select" id="order" name="order">
                                    <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                                    <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                                </select>
                            </div>
                            
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Count -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="text-success mb-0">
                        <?php if (!empty($search) || $organisateurFilter !== 'all'): ?>
                            <i class="fas fa-search"></i> Résultats de recherche
                        <?php else: ?>
                            <i class="fas fa-calendar-alt"></i> Nos Événements
                        <?php endif; ?>
                    </h3>
                    <span class="badge badge-success badge-lg">
                        <?php echo count($evenements); ?> événement<?php echo count($evenements) > 1 ? 's' : ''; ?> trouvé<?php echo count($evenements) > 1 ? 's' : ''; ?>
                    </span>
                </div>
                <!-- DEBUG INFO -->
                <small class="text-muted">
                    DEBUG: Array has <?php echo count($evenements); ?> events. IDs: 
                    <?php 
                    $ids = array_column($evenements, 'id_evenement');
                    echo implode(', ', $ids); 
                    ?>
                </small>
            </div>
        </div>

        <!-- Events Grid -->
        <div class="row mt-5">
            <?php if (count($evenements) > 0): ?>
                <?php 
                $eventCounter = 0;
                foreach ($evenements as $event): 
                    $eventCounter++;
                ?>
                    <?php 
                    $eventDate = new DateTime($event['date_evenement']);
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    $isToday = $eventDate->format('Y-m-d') === $today->format('Y-m-d');
                    $frais = floatval($event['frais_participation']);
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4" data-event-id="<?php echo $event['id_evenement']; ?>" data-counter="<?php echo $eventCounter; ?>">
                        <div class="card h-100 shadow-sm event-card-<?php echo $event['id_evenement']; ?>" style="border-radius: 10px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="card-header bg-success text-white text-center py-3" style="background: linear-gradient(135deg, #59ab6e, #69bb7e) !important;">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['titre_evenement']); ?></h5>
                                <small style="font-size: 10px; opacity: 0.7;">DEBUG: Event ID <?php echo $event['id_evenement']; ?> - Card #<?php echo $eventCounter; ?></small>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="text-muted mb-2">
                                        <i class="far fa-calendar text-success"></i> 
                                        <strong>Date:</strong> 
                                        <?php echo $eventDate->format('d/m/Y'); ?>
                                        <?php if ($isToday): ?>
                                            <span class="badge bg-warning text-dark ms-2">Aujourd'hui</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-building text-success"></i> 
                                        <strong>Organisateur:</strong> 
                                        <?php echo htmlspecialchars($event['organisateur']); ?>
                                    </p>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-money-bill-wave text-success"></i> 
                                        <strong>Frais:</strong> 
                                        <?php echo $frais > 0 ? number_format($frais, 2) . ' DT' : 'Gratuit'; ?>
                                    </p>
                                    <?php if (!empty($event['adresse'])): ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt text-success"></i> 
                                        <strong>Lieu:</strong> 
                                        <?php echo htmlspecialchars($event['adresse']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-users text-success"></i> 
                                        <strong>Disponibilité:</strong> 
                                        <?php if ($event['is_full']): ?>
                                            <span class="badge bg-danger">Complet (<?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $event['available_spots']; ?> place<?php echo $event['available_spots'] > 1 ? 's' : ''; ?> disponible<?php echo $event['available_spots'] > 1 ? 's' : ''; ?></span>
                                            <small class="text-muted">(<?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?>)</small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="card-text" style="font-size: 0.9em; color: #666; line-height: 1.6;">
                                        <?php 
                                        $description = htmlspecialchars($event['description']);
                                        echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <?php if (!empty($event['adresse']) && !empty($event['latitude']) && !empty($event['longitude'])): ?>
                                <!-- Location and Participation buttons -->
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" 
                                                class="btn btn-outline-success btn-lg w-100" 
                                                onclick="showLocationModal(<?php echo $event['latitude']; ?>, <?php echo $event['longitude']; ?>, '<?php echo htmlspecialchars($event['adresse'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($event['titre_evenement'], ENT_QUOTES); ?>')">
                                            <i class="fas fa-map-marker-alt"></i><br>
                                            <small>Voir Lieu</small>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($user): ?>
                                            <?php if ($event['user_participating']): ?>
                                                <button type="button" class="btn btn-warning btn-lg w-100" disabled>
                                                    <i class="fas fa-check-circle"></i><br>
                                                    <small>Déjà inscrit</small>
                                                </button>
                                            <?php elseif ($event['is_full']): ?>
                                                <button type="button" class="btn btn-danger btn-lg w-100" disabled>
                                                    <i class="fas fa-times-circle"></i><br>
                                                    <small>Complet</small>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                        class="btn btn-success btn-lg w-100" 
                                                        onclick="openParticipationModal(<?php echo $event['id_evenement']; ?>, '<?php echo htmlspecialchars($event['titre_evenement'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-hand-paper"></i><br>
                                                    <small>Participer</small>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                        <a href="sign-in.php" class="btn btn-success btn-lg w-100 d-flex flex-column align-items-center">
                                            <i class="fas fa-sign-in-alt"></i>
                                            <small>Se connecter</small>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- Only participation button when no location -->
                                <div class="d-grid gap-2">
                                    <?php if ($user): ?>
                                        <?php if ($event['user_participating']): ?>
                                            <button type="button" class="btn btn-warning btn-lg" disabled>
                                                <i class="fas fa-check-circle"></i> Déjà inscrit
                                            </button>
                                        <?php elseif ($event['is_full']): ?>
                                            <button type="button" class="btn btn-danger btn-lg" disabled>
                                                <i class="fas fa-times-circle"></i> Événement Complet
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-success btn-lg" 
                                                    onclick="openParticipationModal(<?php echo $event['id_evenement']; ?>, '<?php echo htmlspecialchars($event['titre_evenement'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-hand-paper"></i> Participer
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                    <a href="sign-in.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Connectez-vous pour participer
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert" style="border-radius: 10px;">
                        <i class="fas fa-calendar-times fa-3x mb-3" style="color: #59ab6e;"></i>
                        <h4>Aucun événement trouvé</h4>
                        <p class="mb-0">
                            <?php if (!empty($search) || $organisateurFilter !== 'all'): ?>
                                Aucun événement ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                            <?php else: ?>
                                Il n'y a actuellement aucun événement à venir. Revenez bientôt pour découvrir nos prochains événements !
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search) || $organisateurFilter !== 'all'): ?>
                        <div class="mt-3">
                            <a href="evenement.php" class="btn btn-success">
                                <i class="fas fa-redo"></i> Réinitialiser les filtres
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
        }
        .card-header {
            font-weight: 600;
        }
    </style>

    <?php include 'includes/footer.php'; ?>

    <!-- Location Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="locationModalLabel">
                        <i class="fas fa-map-marker-alt"></i> Emplacement de l'Événement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Événement:</strong> <span id="location_event_title"></span><br>
                        <strong>Adresse:</strong> <span id="location_event_address"></span>
                    </div>
                    
                    <!-- Map Container -->
                    <div id="eventLocationMap" style="height: 400px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 20px;"></div>
                    
                    <!-- Navigation Options -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary w-100" onclick="getDirectionsFromCurrentLocation()">
                                <i class="fas fa-route"></i> Itinéraire depuis ma position
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary w-100" id="openInGoogleMaps">
                                <i class="fab fa-google"></i> Ouvrir dans Google Maps
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Cliquez sur "Itinéraire depuis ma position" pour obtenir des directions depuis votre emplacement actuel.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Participation Modal -->
    <div class="modal fade" id="participationModal" tabindex="-1" role="dialog" aria-labelledby="participationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="participationModalLabel">
                        <i class="fas fa-hand-paper"></i> Participer à l'Événement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="participationForm" action="../../controllers/participationController.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" id="participation_event_id" name="id_evenement" value="">
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Événement:</strong> <span id="participation_event_title"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="motivation">
                                <i class="fas fa-heart"></i> Motivation * 
                            </label>
                            <textarea id="motivation" name="motivation" placeholder="Expliquez ce qui vous motive à participer à cet événement... (minimum 10 mots)"></textarea>
                            <small><span id="motivationWordCount">0 mots</span> (minimum 10 mots)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="source_information">
                                <i class="fas fa-info-circle"></i> Comment avez-vous entendu parler de cet événement ? *
                            </label>
                            <input type="text" id="source_information" name="source_information" placeholder="Ex: Réseaux sociaux, Amis, Site web...">
                        </div>
                        
                        <div class="form-group">
                            <label for="type_participation">
                                <i class="fas fa-users"></i> Type de Participation *
                            </label>
                            <select id="type_participation" name="type_participation">
                                <option value="seul">Seul(e)</option>
                                <option value="groupe">En Groupe</option>
                            </select>
                        </div>
                        
                        <div class="form-group" id="nombre_personnes_group" style="display: none;">
                            <label for="nombre_personnes">
                                <i class="fas fa-user-friends"></i> Nombre de Personnes *
                            </label>
                            <input type="number" id="nombre_personnes" name="nombre_personnes" placeholder="2" min="2" max="50" value="2">
                            <small>Minimum 2 personnes pour une participation en groupe</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="desir_dejeuner">
                                <i class="fas fa-utensils"></i> Souhaitez-vous déjeuner ? *
                            </label>
                            <select id="desir_dejeuner" name="desir_dejeuner">
                                <option value="non">Non</option>
                                <option value="oui">Oui</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Confirmer la Participation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Start Script -->
    <script src="assets/js/jquery-1.11.0.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/bootstrap.bundle.min.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/templatemo.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/custom.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/participation-validation.js?v=<?php echo time(); ?>"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Toggle user dropdown menu
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.classList.toggle('active');
            }
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Open participation modal
        function openParticipationModal(eventId, eventTitle) {
            document.getElementById('participation_event_id').value = eventId;
            document.getElementById('participation_event_title').textContent = eventTitle;
            
            // Reset form
            document.getElementById('participationForm').reset();
            document.getElementById('participation_event_id').value = eventId;
            document.getElementById('type_participation').value = 'seul';
            document.getElementById('nombre_personnes_group').style.display = 'none';
            document.getElementById('desir_dejeuner').value = 'non';
            
            // Reset word count
            const counter = document.getElementById('motivationWordCount');
            if (counter) {
                counter.textContent = '0 mots';
                counter.style.color = '#e74c3c';
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('participationModal'));
            modal.show();
        }
        
        // Auto-open modal if specified in URL
        <?php if ($selectedEvent): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openParticipationModal(<?php echo $selectedEvent['id_evenement']; ?>, '<?php echo htmlspecialchars($selectedEvent['titre_evenement'], ENT_QUOTES); ?>');
        });
        <?php endif; ?>
        
        // Location modal functionality
        let locationMap = null;
        let currentEventLocation = { lat: 0, lng: 0, address: '', title: '' };
        
        function showLocationModal(latitude, longitude, address, eventTitle) {
            currentEventLocation = { lat: latitude, lng: longitude, address: address, title: eventTitle };
            
            document.getElementById('location_event_title').textContent = eventTitle;
            document.getElementById('location_event_address').textContent = address;
            
            // Update Google Maps link
            document.getElementById('openInGoogleMaps').onclick = function() {
                window.open(`https://maps.google.com/maps?q=${latitude},${longitude}`, '_blank');
            };
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('locationModal'));
            modal.show();
            
            // Initialize map after modal is shown
            setTimeout(() => {
                initializeLocationMap(latitude, longitude, address);
            }, 300);
        }
        
        function initializeLocationMap(lat, lng, address) {
            // Remove existing map if any
            if (locationMap) {
                locationMap.remove();
            }
            
            // Create new map
            locationMap = L.map('eventLocationMap').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(locationMap);
            
            // Add marker for event location
            const marker = L.marker([lat, lng]).addTo(locationMap);
            marker.bindPopup(`<b>${currentEventLocation.title}</b><br>${address}`).openPopup();
        }
        
        function getDirectionsFromCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        
                        // Open Google Maps with directions
                        const directionsUrl = `https://maps.google.com/maps/dir/${userLat},${userLng}/${currentEventLocation.lat},${currentEventLocation.lng}`;
                        window.open(directionsUrl, '_blank');
                    },
                    function(error) {
                        let errorMessage = 'Impossible d\'obtenir votre position.';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Veuillez autoriser l\'accès à votre position.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Position indisponible.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Temps d\'attente dépassé.';
                                break;
                        }
                        
                        alert(errorMessage + ' Vous pouvez utiliser Google Maps directement.');
                        
                        // Fallback: open Google Maps without user location
                        window.open(`https://maps.google.com/maps?q=${currentEventLocation.lat},${currentEventLocation.lng}`, '_blank');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 600000
                    }
                );
            } else {
                alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                window.open(`https://maps.google.com/maps?q=${currentEventLocation.lat},${currentEventLocation.lng}`, '_blank');
            }
        }

        // Auto-hide success messages after 3 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(function() {
                    successAlert.remove();
                }, 500);
            }, 3000);
        }
    </script>
    <!-- End Script -->
</body>

</html>

