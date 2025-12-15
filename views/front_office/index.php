<?php
require_once '../../config/config.php';
session_start();

require_once '../../controllers/ReclamationController.php';
require_once '../../controllers/SponsorController.php';
require_once '../../controllers/DealController.php';
require_once '../../controllers/ForumController.php';
require_once '../../controllers/CommentaireController.php';
require_once '../../controllers/donc.php';
require_once '../../controllers/associationc.php';
$reclamationController = new ReclamationController($pdo);
$sponsorController = new SponsorController($pdo);
$dealController = new DealController($pdo);
$forumController = new ForumController($pdo);
$commentaireController = new CommentaireController($pdo);
$donC = new DonC();
$associationC = new AssociationC();

// Handle reclamation actions
$action = $_GET['action'] ?? '';
$section = $_GET['section'] ?? '';

// Handle dons section redirects BEFORE any output
// Note: We redirect because the PHP files have full HTML structures
if ($section === 'dons') {
    if (!isset($_GET['step']) || $_GET['step'] === 'type') {
        header('Location: ../php/step-type.php');
        exit();
    } elseif (isset($_GET['step']) && $_GET['step'] === '4') {
        header('Location: ../php/step4.php');
        exit();
    } elseif (isset($_GET['step']) && $_GET['step'] === '5') {
        header('Location: ../php/step5.php');
        exit();
    } elseif (isset($_GET['association_id'])) {
        $id_association = (int)$_GET['association_id'];
        header('Location: ../php/association2.php?id=' . $id_association);
        exit();
    }
}

// Get user's reclamations if logged in
$userReclamations = [];
if (isset($_SESSION['user_id'])) {
    $allReclamations = $reclamationController->getReclamation();
    foreach ($allReclamations as $rec) {
        if (isset($rec['id_user']) && $rec['id_user'] == $_SESSION['user_id']) {
            $id = $rec['id'];
            $response = $reclamationController->getResponseByReclamationId($id);
            if ($response) {
                $rec['has_response'] = true;
                $rec['reponse'] = $response['reponse'] ?? '';
                $rec['reponse_date'] = $response['date_creation'] ?? '';
            } else {
                $rec['has_response'] = false;
            }
            $userReclamations[] = $rec;
        }
    }
}

// Handle success messages
$successMessage = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == '1') {
        $successMessage = 'Réclamation ajoutée avec succès!';
    } elseif ($_GET['success'] == 'updated') {
        $successMessage = 'Réclamation mise à jour avec succès!';
    }
}

// Get sponsors and deals for front office display
$sponsors = [];
$deals = [];
$selectedDeal = null;
$dealAction = $_GET['action'] ?? '';

if ($section === 'sponsors') {
    $sort = $_GET['sort'] ?? '';
    
    // Get sponsors
    $sqlSponsors = "SELECT * FROM sponsors WHERE statut = 'Actif'";
    switch ($sort) {
        case 'nom_asc':
            $sqlSponsors .= " ORDER BY nomEntreprise ASC";
            break;
        case 'montant_desc':
            $sqlSponsors .= " ORDER BY montantEngage DESC";
            break;
        default:
            $sqlSponsors .= " ORDER BY id DESC";
            break;
    }
    $stmt = $pdo->query($sqlSponsors);
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get accepted deals
    $sqlDeals = "
        SELECT d.*, s.nomEntreprise
        FROM deals d
        JOIN sponsors s ON d.idSponsor = s.id
        WHERE d.statut = 'accepte'
    ";
    switch ($sort) {
        case 'deal_date_debut_recent':
            $sqlDeals .= " ORDER BY d.dateDebut DESC";
            break;
        case 'deal_periode_longue':
            $sqlDeals .= " ORDER BY d.periodeValidite DESC";
            break;
        case 'deal_montant_desc':
            $sqlDeals .= " ORDER BY d.prixInitial DESC";
            break;
        case 'deal_accept_date':
            $sqlDeals .= " ORDER BY d.dateAcceptation DESC";
            break;
        case 'deal_dispo':
            $sqlDeals .= " ORDER BY 
                           (CASE WHEN d.expire = 'NON' THEN 0 ELSE 1 END),
                           d.dateDebut DESC";
            break;
        case 'deal_expire':
            $sqlDeals .= " ORDER BY 
                           (CASE WHEN d.expire = 'OUI' THEN 0 ELSE 1 END),
                           d.dateDebut DESC";
            break;
        default:
            $sqlDeals .= " ORDER BY d.dateDebut DESC";
            break;
    }
    $stmtDeals = $pdo->query($sqlDeals);
    $deals = $stmtDeals->fetchAll(PDO::FETCH_ASSOC);
} elseif ($section === 'sponsor' && $dealAction === 'create') {
    // Handle sponsor creation - call controller
    // The controller will handle form submission and set $old/$fieldErrors variables
    // Then we continue to display the form with navbar/footer below
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'store') {
        // Form submission - controller will handle and redirect
        $sponsorController->create();
        exit; // Controller will redirect on success
    } else {
        // Show form - controller sets variables, we continue to display
        $sponsorController->create();
        // Controller returns, variables are set, continue to display form below
    }
} elseif ($section === 'sponsor' && $dealAction === 'assistantIA') {
    // Handle sponsor AI assistant
    
    $sponsorController->assistantIA();
    exit;
} elseif ($section === 'sponsor' && $dealAction === 'show' && isset($_GET['id'])) {
    // Handle sponsor show - call controller to get sponsor data
    // Mark that this is being included from front office
    $GLOBALS['isFrontOfficeInclude'] = true;
    $_GET['section'] = 'sponsor'; // Ensure section is set for context detection
    
    // Get sponsor data from controller (it will set $GLOBALS['sponsor'] and return)
    $sponsorController->show();
    // Continue to display sponsor show within index.php structure below
} elseif ($section === 'forum') {
    // Handle forum actions
    $forumAction = $_GET['action'] ?? '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'store') {
            // Store new forum topic
            $forumController->store();
            exit; // Controller will redirect
        } elseif ($_POST['action'] === 'store_comment') {
            // Handle comment submission
            $commentaireController->store();
            exit; // Controller will redirect
        }
    } elseif ($forumAction === 'create') {
        // Show create form
        $forumController->create();
        // Controller sets $GLOBALS, continue to display below
    } elseif ($forumAction === 'show' && isset($_GET['id'])) {
        // Show forum topic with comments
        $forumController->show();
        // Controller sets $GLOBALS, continue to display below
    } elseif ($forumAction === 'like' && isset($_GET['id'])) {
        // Like a forum topic
        $forumController->like();
        exit; // Controller will redirect
    } else {
        // List forums
        $forumController->index();
        // Controller sets $GLOBALS, continue to display below
    }
    // Store forumAction in GLOBALS for view sections
    $GLOBALS['forumAction'] = $forumAction;
} elseif ($section === 'deal') {
    // Handle deal display or actions
    if ($dealAction === 'create') {
        // Handle deal creation for front office
        // Get sponsors for the dropdown
        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors WHERE statut = 'Actif' ORDER BY nomEntreprise ASC";
        $stmtSponsors = $pdo->query($sqlSponsors);
        $sponsorsForDeal = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'store') {
            // Set statut to 'en_attente' for front office users
            $_POST['statut'] = 'en_attente';
            $dealController->store();
            exit;
        }
    } elseif (isset($_GET['idDeal'])) {
        $idDeal = (int)$_GET['idDeal'];
        
        // Update click count
        $sqlUpdate = "UPDATE deals SET click_count = click_count + 1 WHERE idDeal = :idDeal";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':idDeal' => $idDeal]);
        
        // Get deal details
        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id
                WHERE d.idDeal = :idDeal AND d.statut = 'accepte'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $selectedDeal = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>SOLIDA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/event.css">


    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    
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
    </style>
    
    <!-- reCAPTCHA script for forum creation -->
    <?php if ($section === 'forum' && isset($GLOBALS['forumAction']) && $GLOBALS['forumAction'] === 'create'): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer onerror="console.warn('reCAPTCHA could not be loaded');"></script>
    <?php endif; ?>

</head>

<body>
    <?php include 'includes/navbar.php'; ?>



    <!-- Start Banner Hero -->
    <div id="template-mo-zay-hero-carousel" class="carousel slide" data-bs-ride="carousel">
        <ol class="carousel-indicators">
            <li data-bs-target="#template-mo-zay-hero-carousel" data-bs-slide-to="0" class="active"></li>
            <li data-bs-target="#template-mo-zay-hero-carousel" data-bs-slide-to="1"></li>
            <li data-bs-target="#template-mo-zay-hero-carousel" data-bs-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="container">
                    <div class="row p-5">
                        <div class="mx-auto col-md-8 col-lg-6 order-lg-last">
                            <img class="img-fluid" src="./assets/img/event2.png" alt="">
                        </div>
                        <div class="col-lg-6 mb-0 d-flex align-items-center">
                            <div class="text-align-left align-self-center">
                                <h1 class="h1 text-success"><b>SOLIDA</b> </h1>
                                <h3 class="h2">Engagez-vous et faites la différence</h3>
                                <p>
                                    Explorez de nouvelles opportunités pour vous engager et enrichir votre quotidien. Participez à des expériences uniques, 
                                    connectez-vous avec d’autres membres et contribuez à des initiatives qui ont un réel impact grâce à votre soutien aux projets associatifs. Chaque action, petite ou grande, fait la différence et
                                     renforce le lien au sein de la communauté.<!--<a rel="sponsored" class="text-success" href="https://templatemo.com" target="_blank">TemplateMo</a> website. 
                                    Image credits go to <a rel="sponsored" class="text-success" href="https://stories.freepik.com/" target="_blank">Freepik Stories</a>,
                                    <a rel="sponsored" class="text-success" href="https://unsplash.com/" target="_blank">Unsplash</a> and
                                    <a rel="sponsored" class="text-success" href="https://icons8.com/" target="_blank">Icons 8</a>.-->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="container">
                    <div class="row p-5">
                        <div class="mx-auto col-md-8 col-lg-6 order-lg-last">
                            <img class="img-fluid" src="./assets/img/event4.png" alt="">
                        </div>
                        <div class="col-lg-6 mb-0 d-flex align-items-center">
                            <div class="text-align-left">
                                <h1 class="h1">Événements à Venir</h1>
                                <h3 class="h2">Ne manquez aucune opportunité</h3>
                                <p>
                                    Découvrez une variété d'événements : conférences, ateliers, soirées, et plus encore. Inscrivez-vous facilement et rejoignez la communauté 
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="container">
                    <div class="row p-5">
                        <div class="mx-auto col-md-8 col-lg-6 order-lg-last">
                            <img class="img-fluid" src="./assets/img/don1.png" alt="">
                        </div>
                        <div class="col-lg-6 mb-0 d-flex align-items-center">
                            <div class="text-align-left">
                                <h1 class="h1">Soutenez les Associations</h1>
                                <h3 class="h2">Chaque don fait la différence</h3>
                                <p>
                                    Faites un don aux associations directement depuis la plateforme. Votre générosité aide à financer des projets importants
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev text-decoration-none w-auto ps-3" href="#template-mo-zay-hero-carousel" role="button" data-bs-slide="prev">
            <i class="fas fa-chevron-left"></i>
        </a>
        <a class="carousel-control-next text-decoration-none w-auto pe-3" href="#template-mo-zay-hero-carousel" role="button" data-bs-slide="next">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
        <section class="grid grid-3" style="margin: 40px 0;">
            <div class="highlight-card">
                <h3>💝 Faire un Don</h3>
                <p>Soutenez directement les orphelins et les foyers. Chaque contribution compte et transforme des vies.</p>
                <a href="dons.html" class="btn btn-primary">Donner Maintenant</a>
            </div>
            <div class="highlight-card">
                <h3>📅 Rejoindre un Événement</h3>
                <p>Participez à nos événements de sensibilisation et de solidarité. Ensemble, créons le changement.</p>
                <a href="evenements.html" class="btn btn-secondary">Voir les Événements</a>
            </div>
            <div class="highlight-card">
                <h3>🎓 Événements Étudiants</h3>
                <p>Espace dédié aux étudiants pour s'engager, interagir et combattre la solitude par la solidarité.</p>
                <a href="evenements-etudiants.html" class="btn btn-success">Explorer</a>
            </div>
        </section>
    <!-- End Banner Hero -->

    <!-- Reclamations Section -->
    <?php if ($section === 'reclamations'): ?>
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1 class="h1">Mes Réclamations</h1>
                    <p>Gérez vos réclamations et suivez leurs statuts</p>
                </div>

                <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="mb-4 text-end">
                    <a href="AddReclamation.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Ajouter une réclamation
                    </a>
                </div>

                <?php if (empty($userReclamations)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Aucune réclamation trouvée. <a href="AddReclamation.php">Ajoutez-en une maintenant</a></p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Réponse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userReclamations as $rec): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rec['id']); ?></td>
                                <td>
                                    <?php 
                                    $desc = htmlspecialchars($rec['description_detaillee'] ?? '');
                                    echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($rec['priorite'] ?? 'normale') === 'urgente' ? 'bg-danger' : 'bg-warning'; ?>">
                                        <?php echo htmlspecialchars($rec['priorite'] ?? 'Normale'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($rec['statut'] ?? 'nouveau') === 'nouveau' ? 'bg-secondary' : 'bg-success'; ?>">
                                        <?php echo htmlspecialchars($rec['statut'] ?? 'Nouveau'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($rec['date'])) {
                                        $date = new DateTime($rec['date']);
                                        echo $date->format('d/m/Y H:i');
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($rec['has_response'] ?? false): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Répondu
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> En attente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="UpdateReclamation.php?id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($rec['has_response'] ?? false): ?>
                                        <a href="index.php?section=reclamations&action=view_response&id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Voir la réponse">
                                            <i class="fas fa-comment"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="suppreclamation.php?id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- View Response Modal/Section -->
    <?php 
    if ($action === 'view_response' && isset($_GET['id'])) {
        $responseId = (int)$_GET['id'];
        $selectedRec = null;
        foreach ($userReclamations as $rec) {
            if ($rec['id'] == $responseId) {
                $selectedRec = $rec;
                break;
            }
        }
        if ($selectedRec && ($selectedRec['has_response'] ?? false)):
    ?>
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-comment"></i> Réponse à votre réclamation #<?php echo htmlspecialchars($selectedRec['id']); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Date de réponse:</strong> 
                            <?php 
                            if (!empty($selectedRec['reponse_date'])) {
                                $date = new DateTime($selectedRec['reponse_date']);
                                echo $date->format('d/m/Y H:i');
                            }
                            ?>
                        </p>
                        <p><strong>Statut:</strong> 
                            <span class="badge bg-success"><?php echo htmlspecialchars($selectedRec['statut'] ?? '—'); ?></span>
                        </p>
                        <div class="mt-3">
                            <strong>Réponse:</strong>
                            <div class="alert alert-light mt-2">
                                <?php echo nl2br(htmlspecialchars($selectedRec['reponse'] ?? '—')); ?>
                            </div>
                        </div>
                        <a href="index.php?section=reclamations" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php 
        endif;
    }
    ?>
    <?php endif; ?>
    
    <!-- Sponsors Section -->
    <?php if ($section === 'sponsors'): ?>
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1 class="h1">Nos Sponsors et Offres</h1>
                    <p>Découvrez nos partenaires et leurs offres exclusives</p>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mb-4">
                    <a href="index.php?section=sponsor&action=create" class="btn btn-success btn-lg me-2">
                        <i class="fas fa-handshake"></i> Devenir Sponsor
                    </a>
                    <a href="index.php?section=deal&action=create" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-gift"></i> Proposer une Offre
                    </a>
                    <a href="index.php?section=sponsor&action=assistantIA" class="btn btn-info btn-lg">
                        <i class="fas fa-robot"></i> Assistant IA Sponsors
                    </a>
                </div>
                
                <?php if (isset($_GET['success']) && $_GET['success'] === 'sponsor_created'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> Votre demande de sponsor a été soumise avec succès ! Elle sera examinée par l'administration.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Sort Options -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <input type="hidden" name="section" value="sponsors">
                            <div class="col-md-4">
                                <label for="sort" class="form-label">Trier par:</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="">Par défaut</option>
                                    <option value="nom_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'nom_asc') ? 'selected' : ''; ?>>Nom (A-Z)</option>
                                    <option value="montant_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'montant_desc') ? 'selected' : ''; ?>>Montant (Décroissant)</option>
                                    <option value="deal_date_debut_recent" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'deal_date_debut_recent') ? 'selected' : ''; ?>>Offres récentes</option>
                                    <option value="deal_montant_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'deal_montant_desc') ? 'selected' : ''; ?>>Prix décroissant</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-filter"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sponsors List -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h2 class="h3 mb-4"><i class="fas fa-handshake"></i> Nos Sponsors</h2>
                        <?php if (empty($sponsors)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Aucun sponsor actif pour le moment.</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($sponsors as $sponsor): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <?php if (!empty($sponsor['logoUrl'])): ?>
                                    <div class="card-img-top text-center p-3" style="height: 150px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                        <img src="<?php echo htmlspecialchars($sponsor['logoUrl']); ?>" 
                                             alt="<?php echo htmlspecialchars($sponsor['nomEntreprise']); ?>" 
                                             class="img-fluid" 
                                             style="max-height: 120px; max-width: 100%; object-fit: contain;">
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($sponsor['nomEntreprise']); ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($sponsor['typeSponsoring']); ?><br>
                                                <?php if (!empty($sponsor['domaineActivite'])): ?>
                                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($sponsor['domaineActivite']); ?><br>
                                                <?php endif; ?>
                                                <?php if (!empty($sponsor['montantEngage'])): ?>
                                                <i class="fas fa-money-bill-wave"></i> <?php echo number_format($sponsor['montantEngage'], 2); ?> DT
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="index.php?section=sponsor&action=show&id=<?php echo $sponsor['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-eye"></i> Voir le sponsor
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Deals/Offers List -->
                <div class="row">
                    <div class="col-12">
                        <h2 class="h3 mb-4"><i class="fas fa-gift"></i> Offres Disponibles</h2>
                        <?php if (empty($deals)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Aucune offre disponible pour le moment.</p>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($deals as $deal): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($deal['intitule']); ?></h5>
                                        <small>Par <?php echo htmlspecialchars($deal['nomEntreprise']); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($deal['descriptionD']); ?></p>
                                        <div class="mb-3">
                                            <p class="mb-1">
                                                <strong>Prix initial:</strong> 
                                                <span class="text-decoration-line-through text-muted">
                                                    <?php echo number_format($deal['prixInitial'] ?? $deal['prixinitial'] ?? 0, 2); ?> DT
                                                </span>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Réduction:</strong> 
                                                <span class="text-success fw-bold">
                                                    <?php echo number_format($deal['reduction'], 2); ?>%
                                                </span>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Prix final:</strong> 
                                                <span class="text-success fw-bold fs-5">
                                                    <?php 
                                                    $prixInitial = $deal['prixInitial'] ?? $deal['prixinitial'] ?? 0;
                                                    echo number_format($prixInitial * (1 - $deal['reduction'] / 100), 2); 
                                                    ?> DT
                                                </span>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> 
                                                Du <?php echo date('d/m/Y', strtotime($deal['dateDebut'])); ?><br>
                                                <i class="fas fa-clock"></i> 
                                                Valide <?php echo $deal['periodeValidite']; ?> jours<br>
                                                <?php if ($deal['expire'] == 'OUI'): ?>
                                                <span class="badge bg-danger">Expiré</span>
                                                <?php else: ?>
                                                <span class="badge bg-success">Disponible</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <?php if ($deal['expire'] == 'NON'): ?>
                                        <a href="index.php?section=deal&idDeal=<?php echo $deal['idDeal']; ?>" class="btn btn-success w-100">
                                            <i class="fas fa-eye"></i> Voir l'offre
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-ban"></i> Offre expirée
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if ($section === 'sponsor' && $dealAction === 'show' && isset($GLOBALS['sponsor'])): ?>
    <!-- Sponsor Show - included here with navbar/footer -->
    <?php 
    // Get sponsor from $GLOBALS set by controller
    $sponsor = $GLOBALS['sponsor'];
    // Mark that this is being included from front office
    $GLOBALS['isFrontOfficeInclude'] = true;
    $_GET['section'] = 'sponsor';
    include 'sponsor/show.php';
    ?>
    <?php elseif ($section === 'sponsor' && $dealAction === 'create'): ?>
    <!-- Sponsor Creation - form will be included here with navbar/footer -->
    <?php 
    // Mark that this is being included from front office
    $GLOBALS['isFrontOfficeInclude'] = true;
    // Get variables from controller (set via $GLOBALS)
    $old = $GLOBALS['old'] ?? [];
    $fieldErrors = $GLOBALS['fieldErrors'] ?? [];
    include 'sponsor/create.php';
    ?>
    <?php elseif ($section === 'deal' && $selectedDeal): ?>
    <!-- Deal Detail Section -->
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <a href="index.php?section=sponsors" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Retour aux offres
                </a>
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h2 class="mb-0"><?php echo htmlspecialchars($selectedDeal['intitule']); ?></h2>
                        <small>Par <?php echo htmlspecialchars($selectedDeal['nomEntreprise']); ?></small>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($selectedDeal['descriptionD'])); ?></p>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Prix initial:</strong> 
                                    <span class="text-decoration-line-through text-muted">
                                        <?php echo number_format($selectedDeal['prixInitial'] ?? $selectedDeal['prixinitial'] ?? 0, 2); ?> DT
                                    </span>
                                </p>
                                <p><strong>Réduction:</strong> 
                                    <span class="text-success fw-bold">
                                        <?php echo number_format($selectedDeal['reduction'], 2); ?>%
                                    </span>
                                </p>
                                <p><strong>Prix final:</strong> 
                                    <span class="text-success fw-bold fs-4">
                                        <?php 
                                        $prixInitial = $selectedDeal['prixInitial'] ?? $selectedDeal['prixinitial'] ?? 0;
                                        echo number_format($prixInitial * (1 - $selectedDeal['reduction'] / 100), 2); 
                                        ?> DT
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Date de début:</strong> 
                                    <?php echo date('d/m/Y', strtotime($selectedDeal['dateDebut'])); ?>
                                </p>
                                <p><strong>Période de validité:</strong> 
                                    <?php echo $selectedDeal['periodeValidite']; ?> jours
                                </p>
                                <p><strong>Statut:</strong> 
                                    <?php if ($selectedDeal['expire'] == 'OUI'): ?>
                                        <span class="badge bg-danger">Expiré</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php if (!empty($selectedDeal['has_coupon']) && $selectedDeal['has_coupon'] === 'OUI' && !empty($selectedDeal['coupon_code'])): ?>
                        <div class="alert alert-info">
                            <strong>Code coupon:</strong> <code><?php echo htmlspecialchars($selectedDeal['coupon_code']); ?></code>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ($section === 'deal' && $dealAction === 'create'): ?>
    <!-- Deal Creation Section -->
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <a href="index.php?section=sponsors" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Retour aux offres
                </a>
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><i class="fas fa-gift"></i> Proposer une Offre</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Vous devez être connecté pour proposer une offre.
                            <a href="sign-in.php" class="alert-link">Se connecter</a>
                        </div>
                        <?php else: ?>
                        <form method="post" action="index.php?section=deal&action=create" id="dealCreateForm">
                            <input type="hidden" name="action" value="store">
                            
                            <div class="mb-3">
                                <label class="form-label">Intitulé de l'offre <span class="text-danger">*</span></label>
                                <input type="text" name="intitule" id="intitule" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sponsor <span class="text-danger">*</span></label>
                                    <select name="idSponsor" id="idSponsor" class="form-control" required>
                                        <option value="">-- Choisir un sponsor --</option>
                                        <?php foreach ($sponsorsForDeal as $sp): ?>
                                        <option value="<?= (int)$sp['id'] ?>">
                                            <?= htmlspecialchars($sp['nomEntreprise']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prix initial (DT) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="prixInitial" id="prixInitial" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Réduction (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="reduction" id="reduction" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="descriptionD" id="descriptionD" rows="4" class="form-control" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" name="dateDebut" id="dateDebut" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Période de validité (jours) <span class="text-danger">*</span></label>
                                    <input type="number" name="periodeValidite" id="periodeValidite" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Note (1-5, optionnel)</label>
                                    <input type="number" min="1" max="5" name="note" id="note" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Générer un coupon ?</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="has_coupon" id="coupon_no" value="NON" checked>
                                    <label class="form-check-label" for="coupon_no">Non</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="has_coupon" id="coupon_yes" value="OUI">
                                    <label class="form-check-label" for="coupon_yes">Oui</label>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Votre offre sera soumise pour validation. Elle sera visible une fois approuvée par l'administration.
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php?section=sponsors" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Soumettre l'offre
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ($section === 'forum' && isset($GLOBALS['forums'])): ?>
    <!-- Forum Index - List of forums -->
    <?php 
    $forums = $GLOBALS['forums'] ?? [];
    $categories = $GLOBALS['categories'] ?? [];
    $selectedCategorie = $GLOBALS['selectedCategorie'] ?? '';
    $selectedSort = $GLOBALS['selectedSort'] ?? 'date_desc';
    include '../forum/index.php';
    ?>
    <?php elseif ($section === 'forum' && isset($GLOBALS['forumAction']) && $GLOBALS['forumAction'] === 'create'): ?>
    <!-- Forum Create Form -->
    <?php 
    $old = $GLOBALS['old'] ?? [];
    $fieldErrors = $GLOBALS['fieldErrors'] ?? [];
    include '../forum/create.php';
    ?>
    <?php elseif ($section === 'forum' && isset($GLOBALS['forumAction']) && $GLOBALS['forumAction'] === 'show' && isset($GLOBALS['forum'])): ?>
    <!-- Forum Show - Topic with comments -->
    <?php 
    $forum = $GLOBALS['forum'] ?? null;
    $comments = $GLOBALS['comments'] ?? [];
    include '../forum/show.php';
    ?>
    <?php elseif ($section === 'deal' && !$selectedDeal): ?>
    <!-- Deal Not Found -->
    <section class="container py-5">
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <p>Offre non trouvée ou non disponible.</p>
            <a href="index.php?section=sponsors" class="btn btn-primary">Retour aux offres</a>
        </div>
    </section>
    <?php // Dons section redirects are handled at the top of the file before any output ?>
    <?php endif; ?>
    
    <?php if ($section !== 'reclamations' && $section !== 'sponsors' && $section !== 'deal' && $section !== 'forum' && $section !== 'dons'): ?>
    <!-- Start Categories of The Month -->
    <section class="container py-5">
        <div class="row text-center pt-3">
            <div class="col-lg-6 m-auto">
                <h1 class="h1">Nos Événement </h1>
                <p>
                    Découvrez les différents types d’événements que nous organisons pour renforcer la solidarité, créer du lien et soutenir ceux qui en ont besoin
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-4 p-5 mt-3">
                <a href="#"><img src="./assets/img/soirée.jpg" class="rounded-circle img-fluid border"></a>
                <h5 class="text-center mt-3 mb-3">soirées</h5>
                <!--<p class="text-center"><a class="btn btn-success">Go Shop</a></p>-->
            </div>
            <div class="col-12 col-md-4 p-5 mt-3">
                <a href="#"><img src="./assets/img/marche.jpg" class="rounded-circle img-fluid border"></a>
                <h2 class="h5 text-center mt-3 mb-3">Marche solidaire</h2>
                <!--<p class="text-center"><a class="btn btn-success">Go Shop</a></p>-->
            </div>
            <div class="col-12 col-md-4 p-5 mt-3">
                <a href="#"><img src="./assets/img/conf2.jpg" class="rounded-circle img-fluid border"></a>
                <h2 class="h5 text-center mt-3 mb-3">Conférences</h2>
                <!--<p class="text-center"><a class="btn btn-success">Go Shop</a></p>-->
            </div>
            
        </div>
    </section>
    <!-- End Categories of The Month -->


    <!-- Start Featured Product -->
    <!--<section class="bg-light">
        <div class="container py-5">
            <div class="row text-center py-3">
                <div class="col-lg-6 m-auto">
                    <h1 class="h1">Featured Product</h1>
                    <p>
                        Reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident.
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-4 mb-4">
                    <div class="card h-100">
                        <a href="shop-single.html">
                            <img src="./assets/img/feature_prod_01.jpg" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <ul class="list-unstyled d-flex justify-content-between">
                                <li>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-muted fa fa-star"></i>
                                    <i class="text-muted fa fa-star"></i>
                                </li>
                                <li class="text-muted text-right">$240.00</li>
                            </ul>
                            <a href="shop-single.html" class="h2 text-decoration-none text-dark">Gym Weight</a>
                            <p class="card-text">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt in culpa qui officia deserunt.
                            </p>
                            <p class="text-muted">Reviews (24)</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-4">
                    <div class="card h-100">
                        <a href="shop-single.html">
                            <img src="./assets/img/feature_prod_02.jpg" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <ul class="list-unstyled d-flex justify-content-between">
                                <li>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-muted fa fa-star"></i>
                                    <i class="text-muted fa fa-star"></i>
                                </li>
                                <li class="text-muted text-right">$480.00</li>
                            </ul>
                            <a href="shop-single.html" class="h2 text-decoration-none text-dark">Cloud Nike Shoes</a>
                            <p class="card-text">
                                Aenean gravida dignissim finibus. Nullam ipsum diam, posuere vitae pharetra sed, commodo ullamcorper.
                            </p>
                            <p class="text-muted">Reviews (48)</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mb-4">
                    <div class="card h-100">
                        <a href="shop-single.html">
                            <img src="./assets/img/feature_prod_03.jpg" class="card-img-top" alt="...">
                        </a>
                        <div class="card-body">
                            <ul class="list-unstyled d-flex justify-content-between">
                                <li>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                    <i class="text-warning fa fa-star"></i>
                                </li>
                                <li class="text-muted text-right">$360.00</li>
                            </ul>
                            <a href="shop-single.html" class="h2 text-decoration-none text-dark">Summer Addides Shoes</a>
                            <p class="card-text">
                                Curabitur ac mi sit amet diam luctus porta. Phasellus pulvinar sagittis diam, et scelerisque ipsum lobortis nec.
                            </p>
                            <p class="text-muted">Reviews (74)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>-->
    <!-- End Featured Product -->


    <!-- Start Footer -->
    <footer class="bg-dark" id="tempaltemo_footer">
        <div class="container">
            <div class="row">

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-success border-bottom pb-3 border-light logo">SOLIDA</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            1, 2 rue André Ampère - 2083 - Pôle Technologique - El Ghazala
                        </li>
                        <li>
                            <i class="fa fa-phone fa-fw"></i>
                            <a class="text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                        </li>
                        <li>
                            <i class="fa fa-envelope fa-fw"></i>
                            <a class="text-decoration-none" href="mailto:info@company.com">info@company.com</a>
                        </li>
                    </ul>
                </div>
<!--
                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Products</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Luxury</a></li>
                        <li><a class="text-decoration-none" href="#">Sport Wear</a></li>
                        <li><a class="text-decoration-none" href="#">Men's Shoes</a></li>
                        <li><a class="text-decoration-none" href="#">Women's Shoes</a></li>
                        <li><a class="text-decoration-none" href="#">Popular Dress</a></li>
                        <li><a class="text-decoration-none" href="#">Gym Accessories</a></li>
                        <li><a class="text-decoration-none" href="#">Sport Shoes</a></li>
                    </ul>
                </div>
-->
                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Further Info</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Home</a></li>
                        <li><a class="text-decoration-none" href="#">Événement</a></li>
                        <li><a class="text-decoration-none" href="#">Dons</a></li>
                        <li><a class="text-decoration-none" href="#">Reclamation</a></li>
                        <li><a class="text-decoration-none" href="#">Contact</a></li>
                    </ul>
                </div>

            </div>

            <div class="row text-light mb-4">
                <div class="col-12 mb-3">
                    <div class="w-100 my-3 border-top border-light"></div>
                </div>
                <div class="col-auto me-auto">
                    <ul class="list-inline text-left footer-icons">
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="http://facebook.com/"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.instagram.com/"><i class="fab fa-instagram fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://twitter.com/"><i class="fab fa-twitter fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.linkedin.com/"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="subscribeEmail">Email address</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control bg-dark border-light" id="subscribeEmail" placeholder="Email address">
                        <div class="input-group-text btn-success text-light">Subscribe</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 bg-black py-3">
            <div class="container">
                <div class="row pt-2">
                    <div class="col-12">
                        <p class="text-left text-light">
                            Copyright &copy; 2025 SOLIDA 
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </footer>
    <!-- End Footer -->
    <?php endif; ?>

    <!-- Start Script -->
    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
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
    </script>
    <!-- End Script -->
</body>

</html>