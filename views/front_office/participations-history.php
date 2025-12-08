<?php
// Connexion à la base de données
require_once '../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: sign-in.php');
    exit();
}
require_once '../../controllers/participationController.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';
$participations = [];
$user = null;

// Récupérer les messages de session
if (isset($_SESSION['participation_success'])) {
    $success = $_SESSION['participation_success'];
    unset($_SESSION['participation_success']);
}

if (isset($_SESSION['participation_message'])) {
    $error = $_SESSION['participation_message'];
    unset($_SESSION['participation_message']);
}

// Récupérer les erreurs de formulaire
$formErrors = [];
if (isset($_SESSION['participation_errors'])) {
    $formErrors = $_SESSION['participation_errors'];
    unset($_SESSION['participation_errors']);
}

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Récupérer les participations de l'utilisateur
$controller = new participationController();
try {
    $participations = $controller->getParticipationsByUser($userId);
} catch (Exception $e) {
    $error = "Erreur lors de la récupération des participations: " . $e->getMessage();
}

// Récupérer la participation à modifier si spécifiée
$editParticipation = null;
if (isset($_GET['modal']) && $_GET['modal'] === 'edit' && isset($_GET['id'])) {
    $participationId = intval($_GET['id']);
    try {
        $editParticipation = $controller->getParticipationById($participationId);
        // Vérifier que la participation appartient à l'utilisateur
        if ($editParticipation && $editParticipation['id_user'] != $userId) {
            $editParticipation = null;
            $error = "Vous n'avez pas accès à cette participation.";
        }
    } catch (Exception $e) {
        // Silently fail
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Mes Participations - SOLIDA</title>
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
        
        .participation-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .participation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .participation-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9eef5;
        }
        
        .participation-title {
            color: #212934;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .participation-meta {
            color: #bcbcbc;
            font-size: 14px;
        }
        
        .participation-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }
        
        .badge-upcoming {
            background-color: #59ab6e;
            color: white;
        }
        
        .badge-past {
            background-color: #6c757d;
            color: white;
        }
        
        .participation-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-item i {
            color: #59ab6e;
            width: 20px;
        }
        
        .detail-item strong {
            color: #212934;
            margin-right: 5px;
        }
        
        .detail-item span {
            color: #6c757d;
        }
        
        .participation-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9eef5;
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
    <!-- Start Top Nav -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
        <div class="container text-light">
            <div class="w-100 d-flex justify-content-between">
                <div>
                    <i class="fa fa-envelope mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="mailto:info@company.com">info@company.com</a>
                    <i class="fa fa-phone mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                </div>
                <div>
                    <a class="text-light" href="https://fb.com/templatemo" target="_blank" rel="sponsored"><i class="fab fa-facebook-f fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://twitter.com/" target="_blank"><i class="fab fa-twitter fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin fa-sm fa-fw"></i></a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Top Nav -->

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">

            <a class="navbar-brand text-success logo h1 align-self-center" href="index.php">
                SOLIDA
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between" id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="evenement.php">Événement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Dons</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Réclamation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="userprofile.php">Mon Profil</a>
                        </li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <div class="d-lg-none flex-sm-fill mt-3 mb-4 col-7 col-sm-auto pr-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputMobileSearch" placeholder="Rechercher ...">
                            <div class="input-group-text">
                                <i class="fa fa-fw fa-search"></i>
                            </div>
                        </div>
                    </div>
                    <a class="nav-icon d-none d-lg-inline" href="#" data-bs-toggle="modal" data-bs-target="#templatemo_search">
                        <i class="fa fa-fw fa-search text-dark mr-2"></i>
                    </a>

                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                            <i class="fa fa-fw fa-user text-dark"></i>
                            <span class="text-dark ms-2 d-none d-md-inline"><?php echo htmlspecialchars($user['fullname']); ?></span>
                            <i class="fas fa-chevron-down text-dark ms-1" style="font-size: 12px;"></i>
                        </div>
                        <div class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <p><?php echo htmlspecialchars($user['fullname']); ?></p>
                                <small><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                            <a href="userprofile.php" class="user-dropdown-item">
                                <i class="fas fa-user"></i>
                                Mon Profil
                            </a>
                            <a href="participations-history.php" class="user-dropdown-item">
                                <i class="fas fa-calendar-check"></i>
                                Mes Participations
                            </a>
                            <a href="sign-in.php" class="user-dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Se déconnecter
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </nav>
    <!-- Close Header -->

    <!-- Modal -->
    <div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="w-100 pt-1 mb-5 text-right">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="get" class="modal-content modal-body border-0 p-0">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="inputModalSearch" name="q" placeholder="Rechercher ...">
                    <button type="submit" class="input-group-text bg-success text-light">
                        <i class="fa fa-fw fa-search text-white"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row text-center pt-3 mb-4">
            <div class="col-lg-6 m-auto">
                <h1 class="h1">Mes Participations</h1>
                <p>
                    Gérez toutes vos participations aux événements SOLIDA
                </p>
            </div>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($formErrors)): ?>
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Erreurs de validation:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($formErrors as $field => $errorMsg): ?>
                <li><?php echo htmlspecialchars($errorMsg); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Participations List -->
        <div class="row mt-4">
            <div class="col-12">
                <?php if (count($participations) > 0): ?>
                    <?php foreach ($participations as $participation): ?>
                        <?php 
                        $eventDate = new DateTime($participation['date_evenement']);
                        $today = new DateTime();
                        $today->setTime(0, 0, 0);
                        $isPast = $eventDate < $today;
                        $inscriptionDate = new DateTime($participation['date_inscription']);
                        ?>
                        <div class="participation-card">
                            <div class="participation-header">
                                <div>
                                    <h3 class="participation-title">
                                        <?php echo htmlspecialchars($participation['titre_evenement']); ?>
                                        <span class="participation-badge <?php echo $isPast ? 'badge-past' : 'badge-upcoming'; ?>">
                                            <?php echo $isPast ? 'Terminé' : 'À venir'; ?>
                                        </span>
                                    </h3>
                                    <p class="participation-meta">
                                        <i class="far fa-calendar"></i> 
                                        Inscrit le <?php echo $inscriptionDate->format('d/m/Y à H:i'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="participation-details">
                                <div class="detail-item">
                                    <i class="far fa-calendar"></i>
                                    <strong>Date:</strong>
                                    <span><?php echo $eventDate->format('d/m/Y'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-building"></i>
                                    <strong>Organisateur:</strong>
                                    <span><?php echo htmlspecialchars($participation['organisateur']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-users"></i>
                                    <strong>Type:</strong>
                                    <span><?php echo ucfirst($participation['type_participation']); ?></span>
                                </div>
                                <?php if ($participation['type_participation'] === 'groupe'): ?>
                                <div class="detail-item">
                                    <i class="fas fa-user-friends"></i>
                                    <strong>Nombre:</strong>
                                    <span><?php echo $participation['nombre_personnes']; ?> personnes</span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <i class="fas fa-utensils"></i>
                                    <strong>Déjeuner:</strong>
                                    <span><?php echo $participation['desir_dejeuner'] === 'oui' ? 'Oui' : 'Non'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Source:</strong>
                                    <span><?php echo htmlspecialchars($participation['source_information']); ?></span>
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                                <strong style="color: #212934; display: block; margin-bottom: 8px;">
                                    <i class="fas fa-heart text-success"></i> Motivation:
                                </strong>
                                <p style="color: #6c757d; margin: 0; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($participation['motivation'])); ?>
                                </p>
                            </div>
                            
                            <div class="participation-actions">
                                <button type="button" 
                                        class="btn btn-primary edit-participation-btn" 
                                        data-id="<?php echo $participation['id_participation']; ?>"
                                        data-titre="<?php echo htmlspecialchars($participation['titre_evenement'], ENT_QUOTES); ?>"
                                        data-motivation="<?php echo base64_encode($participation['motivation']); ?>"
                                        data-source="<?php echo htmlspecialchars($participation['source_information'], ENT_QUOTES); ?>"
                                        data-type="<?php echo $participation['type_participation']; ?>"
                                        data-nombre="<?php echo $participation['nombre_personnes']; ?>"
                                        data-dejeuner="<?php echo $participation['desir_dejeuner']; ?>">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <a href="../../controllers/participationController.php?action=delete&id=<?php echo $participation['id_participation']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette participation?');">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center py-5" role="alert">
                        <i class="fas fa-calendar-times fa-3x mb-3" style="color: #59ab6e;"></i>
                        <h4>Aucune participation</h4>
                        <p class="mb-3">Vous n'avez pas encore participé à un événement.</p>
                        <a href="evenement.php" class="btn btn-success">
                            <i class="fas fa-calendar-alt"></i> Voir les Événements
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Edit Participation Modal -->
    <div class="modal fade" id="editParticipationModal" tabindex="-1" role="dialog" aria-labelledby="editParticipationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editParticipationModalLabel">
                        <i class="fas fa-edit"></i> Modifier ma Participation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editParticipationForm" action="../../controllers/participationController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_participation_id" name="id_participation" value="">
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Further Info</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="index.php">Home</a></li>
                        <li><a class="text-decoration-none" href="evenement.php">Événement</a></li>
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

    <!-- Start Script -->
    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/participation-validation.js"></script>
    
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
        
        // Open edit participation modal - Global function
        function openEditModal(id, titre, motivation, source, type, nombre, dejeuner) {
            try {
                // Set form values
                document.getElementById('edit_participation_id').value = id || '';
                document.getElementById('edit_participation_event_title').textContent = titre || '';
                document.getElementById('edit_motivation').value = motivation || '';
                document.getElementById('edit_source_information').value = source || '';
                document.getElementById('edit_type_participation').value = type || 'seul';
                document.getElementById('edit_nombre_personnes').value = nombre || 1;
                document.getElementById('edit_desir_dejeuner').value = dejeuner || 'non';
                
                // Toggle nombre personnes group
                const nombreGroup = document.getElementById('edit_nombre_personnes_group');
                const nombreInput = document.getElementById('edit_nombre_personnes');
                if (nombreGroup && nombreInput) {
                    if (type === 'groupe') {
                        nombreGroup.style.display = 'block';
                        nombreInput.removeAttribute('disabled');
                        nombreInput.setAttribute('name', 'nombre_personnes');
                    } else {
                        nombreGroup.style.display = 'none';
                        nombreInput.setAttribute('disabled', 'disabled');
                        nombreInput.removeAttribute('name');
                        // Set value to 1 for seul type
                        nombreInput.value = '1';
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
                
                // Show modal using Bootstrap 5
                const modalElement = document.getElementById('editParticipationModal');
                if (modalElement) {
                    // Try Bootstrap 5 method first
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        
                        // Re-initialize validation when modal is shown
                        modalElement.addEventListener('shown.bs.modal', function() {
                            initializeEditFormValidation();
                        }, { once: true });
                    } else if (typeof $ !== 'undefined') {
                        // Fallback to jQuery if Bootstrap not loaded
                        $(modalElement).modal('show');
                        $(modalElement).on('shown.bs.modal', function() {
                            initializeEditFormValidation();
                        });
                    } else {
                        // Last resort - show directly
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        setTimeout(initializeEditFormValidation, 100);
                    }
                }
            } catch (error) {
                console.error('Error opening edit modal:', error);
                alert('Erreur lors de l\'ouverture du formulaire de modification.');
            }
        }
        
        // Initialize edit form validation when modal opens
        function initializeEditFormValidation() {
            const editForm = document.getElementById('editParticipationForm');
            if (!editForm) {
                console.error('Edit form not found');
                return;
            }
            
            // Get form elements
            const motivationTextarea = document.getElementById('edit_motivation');
            const sourceInput = document.getElementById('edit_source_information');
            const typeSelect = document.getElementById('edit_type_participation');
            const nombreInput = document.getElementById('edit_nombre_personnes');
            
            if (!motivationTextarea || !sourceInput || !typeSelect) {
                console.error('Form elements not found');
                return;
            }
            
            // Check if handler already attached
            if (editForm.hasAttribute('data-validation-attached')) {
                return; // Already initialized
            }
            
            // Mark as attached
            editForm.setAttribute('data-validation-attached', 'true');
            
            // Use the form directly
            const finalForm = editForm;
            const finalMotivation = motivationTextarea;
            const finalSource = sourceInput;
            const finalType = typeSelect;
            const finalNombre = nombreInput;
            
            // Validation functions
            function validateMotivation(textarea) {
                if (!textarea) return true;
                const motivation = textarea.value.trim();
                const wordCount = motivation.split(/\s+/).filter(word => word.length > 0).length;
                
                if (motivation === '') {
                    showError(textarea, "La motivation est obligatoire.");
                    return false;
                }
                
                if (wordCount < 10) {
                    showError(textarea, `La motivation doit contenir au moins 10 mots. (${wordCount}/10)`);
                    return false;
                }
                
                showSuccess(textarea);
                return true;
            }
            
            function validateSourceInformation(input) {
                if (!input) return true;
                const source = input.value.trim();
                
                if (source === '') {
                    showError(input, "La source d'information est obligatoire.");
                    return false;
                }
                
                if (source.length < 3) {
                    showError(input, "La source d'information doit contenir au moins 3 caractères.");
                    return false;
                }
                
                if (source.length > 100) {
                    showError(input, "La source d'information ne peut pas dépasser 100 caractères.");
                    return false;
                }
                
                showSuccess(input);
                return true;
            }
            
            function validateNombrePersonnes(input, type) {
                if (!input) return true;
                
                const nombre = input.value.trim();
                
                if (type === 'groupe') {
                    if (nombre === '') {
                        showError(input, "Le nombre de personnes est obligatoire pour une participation en groupe.");
                        return false;
                    }
                    
                    const nombreNum = parseInt(nombre);
                    
                    if (isNaN(nombreNum)) {
                        showError(input, "Le nombre de personnes doit être un nombre.");
                        return false;
                    }
                    
                    if (nombreNum < 2) {
                        showError(input, "Pour une participation en groupe, le nombre de personnes doit être d'au moins 2.");
                        return false;
                    }
                    
                    if (nombreNum > 50) {
                        showError(input, "Le nombre de personnes ne peut pas dépasser 50.");
                        return false;
                    }
                }
                
                showSuccess(input);
                return true;
            }
            
            function showError(element, message) {
                if (!element) return;
                const formGroup = element.closest('.form-group');
                if (!formGroup) return;
                
                if (element.classList) {
                    element.classList.remove('is-valid');
                    element.classList.add('is-invalid');
                }
                
                let feedback = formGroup.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.style.display = 'block';
                    feedback.style.color = '#e74c3c';
                    feedback.style.fontSize = '13px';
                    feedback.style.marginTop = '5px';
                    formGroup.appendChild(feedback);
                }
                feedback.textContent = message;
            }
            
            function showSuccess(element) {
                if (!element) return;
                const formGroup = element.closest('.form-group');
                if (!formGroup) return;
                
                if (element.classList) {
                    element.classList.remove('is-invalid');
                    element.classList.add('is-valid');
                }
                
                const feedback = formGroup.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }
            
            // Add change listener for type_participation to toggle nombre_personnes
            if (finalType && finalNombre) {
                finalType.addEventListener('change', function() {
                    const nombreGroup = document.getElementById('edit_nombre_personnes_group');
                    if (this.value === 'groupe') {
                        if (nombreGroup) nombreGroup.style.display = 'block';
                        finalNombre.removeAttribute('disabled');
                        finalNombre.setAttribute('name', 'nombre_personnes');
                    } else {
                        if (nombreGroup) nombreGroup.style.display = 'none';
                        finalNombre.setAttribute('disabled', 'disabled');
                        finalNombre.removeAttribute('name');
                        finalNombre.value = '1';
                    }
                });
            }
            
            // Add submit handler
            if (finalForm) {
                finalForm.addEventListener('submit', function(e) {
                    // Ensure nombre_personnes is handled correctly before validation
                    if (finalType.value === 'seul') {
                        // If seul, remove name attribute to prevent HTML5 validation
                        if (finalNombre) {
                            finalNombre.removeAttribute('name');
                        }
                    } else {
                        // If groupe, ensure name attribute exists
                        if (finalNombre) {
                            finalNombre.setAttribute('name', 'nombre_personnes');
                            finalNombre.removeAttribute('disabled');
                        }
                    }
                    
                    // Always validate first
                    const isMotivationValid = validateMotivation(finalMotivation);
                    const isSourceValid = validateSourceInformation(finalSource);
                    const isNombreValid = validateNombrePersonnes(finalNombre, finalType.value);
                    
                    if (!isMotivationValid || !isSourceValid || !isNombreValid) {
                        // Prevent submission if validation fails
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const firstError = finalForm.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                        return false;
                    }
                    
                    // If validation passes, allow form to submit normally
                    const submitBtn = finalForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
                        submitBtn.disabled = true;
                    }
                    // Form will submit normally - don't prevent default
                });
            }
        }
        
        // Add event listeners to edit buttons
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-participation-btn');
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const titre = this.getAttribute('data-titre');
                    const motivationEncoded = this.getAttribute('data-motivation');
                    const motivation = motivationEncoded ? atob(motivationEncoded) : '';
                    const source = this.getAttribute('data-source');
                    const type = this.getAttribute('data-type');
                    const nombre = this.getAttribute('data-nombre');
                    const dejeuner = this.getAttribute('data-dejeuner');
                    
                    if (id) {
                        openEditModal(id, titre, motivation, source, type, nombre, dejeuner);
                    }
                });
            });
            
            // Auto-open modal if specified in URL
            <?php if ($editParticipation): ?>
            openEditModal(
                "<?php echo $editParticipation['id_participation']; ?>",
                "<?php echo htmlspecialchars($editParticipation['titre_evenement'], ENT_QUOTES); ?>",
                <?php echo json_encode($editParticipation['motivation']); ?>,
                "<?php echo htmlspecialchars($editParticipation['source_information'], ENT_QUOTES); ?>",
                "<?php echo $editParticipation['type_participation']; ?>",
                "<?php echo $editParticipation['nombre_personnes']; ?>",
                "<?php echo $editParticipation['desir_dejeuner']; ?>"
            );
            <?php endif; ?>
        });
        
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

