<?php
session_start();

// Connexion à la base de données
require_once '../../config/config.php';

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

// Récupérer les erreurs de formulaire
$formErrors = [];
if (isset($_SESSION['participation_errors'])) {
    $formErrors = $_SESSION['participation_errors'];
    unset($_SESSION['participation_errors']);
}

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
    $sql = "SELECT * FROM evenements WHERE date_evenement >= CURDATE() ORDER BY date_evenement ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="evenement.php">Événement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Dons</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Reclamation</a>
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

                    <?php if ($user): ?>
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
                    <?php else: ?>
                    <a class="nav-icon position-relative text-decoration-none" href="sign-in.php">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                        <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark"></span>
                    </a>
                    <?php endif; ?>
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
        
        <!-- Events Grid -->
        <div class="row mt-5">
            <?php if (count($evenements) > 0): ?>
                <?php foreach ($evenements as $event): ?>
                    <?php 
                    $eventDate = new DateTime($event['date_evenement']);
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    $isToday = $eventDate->format('Y-m-d') === $today->format('Y-m-d');
                    $frais = floatval($event['frais_participation']);
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm" style="border-radius: 10px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                            <div class="card-header bg-success text-white text-center py-3" style="background: linear-gradient(135deg, #59ab6e, #69bb7e) !important;">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['titre_evenement']); ?></h5>
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
                                <div class="d-grid gap-2">
                                    <?php if ($user): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-lg" 
                                            onclick="openParticipationModal(<?php echo $event['id_evenement']; ?>, '<?php echo htmlspecialchars($event['titre_evenement'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-hand-paper"></i> Participer
                                    </button>
                                    <?php else: ?>
                                    <a href="sign-in.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Connectez-vous pour participer
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-5" role="alert">
                        <i class="fas fa-calendar-times fa-3x mb-3" style="color: #59ab6e;"></i>
                        <h4>Aucun événement disponible</h4>
                        <p class="mb-0">Il n'y a actuellement aucun événement à venir. Revenez bientôt pour découvrir nos prochains événements !</p>
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
                            <input type="number" id="nombre_personnes" name="nombre_personnes" placeholder="2" value="2">
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

