<?php
session_start();

// Vérifier si l'utilisateur est connecté
// Décommentez ces lignes lorsque vous avez le système d'authentification complet
// if (!isset($_SESSION['user_id'])) {
//     header('Location: sign-in.php');
//     exit();
// }

// Connexion à la base de données
require_once '../../config/config.php';

$success = '';
$error = '';
$user = null;

// Utiliser l'ID de session ou un ID par défaut pour la démo
$userId = $_SESSION['user_id'] ?? 1;

// Afficher message de succès après inscription
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "Inscription réussie! Bienvenue sur SOLIDA.";
}

// Afficher message de succès après mise à jour
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "Profil mis à jour avec succès!";
    unset($_SESSION['success_message']);
}

// Récupérer les erreurs de mise à jour
$updateErrors = [];
if (isset($_SESSION['update_errors'])) {
    $updateErrors = $_SESSION['update_errors'];
    unset($_SESSION['update_errors']);
}

if (isset($_SESSION['update_message'])) {
    if (!$success) {
        $error = $_SESSION['update_message'];
    }
    unset($_SESSION['update_message']);
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
        $address = trim($_POST['address']);
        $bio = trim($_POST['bio']);
        $interests = trim($_POST['interests']);
        
        // Validation
        if (empty($fullname) || empty($email)) {
            throw new Exception("Le nom complet et l'email sont obligatoires.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format d'email invalide.");
        }
        
        // Mise à jour du profil
        $stmt = $pdo->prepare("
            UPDATE users 
            SET fullname = ?, email = ?, age = ?, address = ?, bio = ?, interests = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$fullname, $email, $age, $address, $bio, $interests, $userId]);
        
        $success = "Profil mis à jour avec succès!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Traitement de la suppression du profil
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    require_once '../../controllers/userControllers.php';
    $controller = new userController();
    $controller->handleDeleteProfile();
    exit();
}

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "Utilisateur non trouvé.";
    }
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Mon Profil - SOLIDA</title>
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
        .profile-section {
            padding: 60px 0;
            background-color: #e9eef5;
        }
        
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e9eef5;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #59ab6e, #69bb7e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(89, 171, 110, 0.3);
            text-transform: uppercase;
        }
        
        .profile-info h2 {
            color: #212934;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .profile-info p {
            color: #bcbcbc;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .profile-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            background-color: #59ab6e;
            color: white;
            margin-top: 10px;
        }
        
        .form-section h3 {
            color: #212934;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h3 i {
            color: #59ab6e;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #212934;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .form-group label i {
            color: #59ab6e;
            margin-right: 5px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #59ab6e;
            box-shadow: 0 0 0 3px rgba(89, 171, 110, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background-color: #59ab6e;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: #69bb7e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(89, 171, 110, 0.3);
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #59ab6e;
            color: white;
        }
        
        .alert-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-box {
            text-align: center;
            padding: 25px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9eef5 100%);
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(89, 171, 110, 0.15);
        }
        
        .stat-box h4 {
            color: #59ab6e;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-box p {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }
        
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
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
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
                            <i class="fas fa-chevron-down text-dark" style="font-size: 12px;"></i>
                        </div>
                        <div class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <p><?php echo htmlspecialchars($user['fullname']); ?></p>
                                <small><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                            <a href="sign-up.php" class="user-dropdown-item">
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

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="profile-container">
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span><?php echo $success; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($user): ?>
                
                <!-- Profile Header Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php 
                                $nameParts = explode(' ', $user['fullname']);
                                $initials = strtoupper(substr($nameParts[0], 0, 1));
                                if (count($nameParts) > 1) {
                                    $initials .= strtoupper(substr($nameParts[1], 0, 1));
                                }
                                echo $initials;
                            ?>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
                            <p>
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <span class="profile-badge">
                                <i class="fas fa-user"></i>
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                            <p style="margin-top: 15px; font-size: 14px;">
                                <i class="far fa-calendar"></i>
                                Membre depuis le 
                                <?php 
                                    $date = new DateTime($user['created_at']);
                                    echo $date->format('d/m/Y'); 
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Stats Row -->
                    <div class="stats-row" style="margin-top: 30px; margin-bottom: 25px;">
                        <div class="stat-box">
                            <h4>0</h4>
                            <p>Événements Suivis</p>
                        </div>
                        <div class="stat-box">
                            <h4>0</h4>
                            <p>Dons Effectués</p>
                        </div>
                        <div class="stat-box">
                            <h4>0</h4>
                            <p>Réclamations</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons in Header -->
                    <div style="display: flex; gap: 15px; justify-content: center; padding-top: 25px; border-top: 2px solid #e9eef5;">
                        <button type="button" class="btn-submit" id="editProfileBtn" onclick="toggleEditMode()">
                            <i class="fas fa-edit"></i> Modifier le Compte
                        </button>
                        <button type="button" class="btn-cancel" style="background-color: #e74c3c; border-color: #e74c3c;" onclick="confirmDelete()">
                            <i class="fas fa-trash-alt"></i> Supprimer le Compte
                        </button>
                    </div>

                </div>
                
                <!-- Profile Details View (Default) -->
                <div class="profile-card" id="profileView">
                    <h3 style="color: #212934; margin-bottom: 25px; font-size: 24px; font-weight: 600;">
                        <i class="fas fa-info-circle"></i> Mes Informations
                    </h3>
                    
                    <div class="info-row" style="margin-bottom: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-user"></i> Nom Complet
                                </label>
                                <p style="color: #212934; font-size: 16px; font-weight: 500;">
                                    <?php echo htmlspecialchars($user['fullname']); ?>
                                </p>
                            </div>
                            
                            <div>
                                <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <p style="color: #212934; font-size: 16px; font-weight: 500;">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-row" style="margin-bottom: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-birthday-cake"></i> Âge
                                </label>
                                <p style="color: #212934; font-size: 16px; font-weight: 500;">
                                    <?php echo $user['age'] ? $user['age'] . ' ans' : 'Non renseigné'; ?>
                                </p>
                            </div>
                            
                            <div>
                                <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                                    <i class="fas fa-map-marker-alt"></i> Adresse
                                </label>
                                <p style="color: #212934; font-size: 16px; font-weight: 500;">
                                    <?php echo $user['address'] ? htmlspecialchars($user['address']) : 'Non renseignée'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-row" style="margin-bottom: 20px;">
                        <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-info-circle"></i> Biographie
                        </label>
                        <p style="color: #212934; font-size: 16px; line-height: 1.6;">
                            <?php echo $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : 'Aucune biographie'; ?>
                        </p>
                    </div>
                    
                    <div class="info-row">
                        <label style="color: #bcbcbc; font-size: 14px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-heart"></i> Centres d'Intérêt
                        </label>
                        <p style="color: #212934; font-size: 16px;">
                            <?php 
                            if ($user['interests']) {
                                $interests = explode(', ', $user['interests']);
                                foreach ($interests as $interest) {
                                    echo '<span style="display: inline-block; background: #e9eef5; padding: 5px 12px; border-radius: 15px; margin: 3px; font-size: 14px;">' . htmlspecialchars($interest) . '</span>';
                                }
                            } else {
                                echo 'Aucun centre d\'intérêt renseigné';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Edit Profile Form (Hidden by default) -->
                <div class="profile-card" id="profileEdit" style="display: none;">
                    <form method="POST" action="../../controllers/userControllers.php" id="updateProfileForm">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-edit"></i>
                                Modifier mes informations
                            </h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fullname">
                                        <i class="fas fa-user"></i> Nom Complet *
                                    </label>
                                    <input 
                                        type="text" 
                                        id="fullname" 
                                        name="fullname" 
                                        value="<?php echo htmlspecialchars($user['fullname']); ?>"
                                        required
                                    >
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope"></i> Email *
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        value="<?php echo htmlspecialchars($user['email']); ?>"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="age">
                                        <i class="fas fa-birthday-cake"></i> Âge
                                    </label>
                                    <input 
                                        type="number" 
                                        id="age" 
                                        name="age" 
                                        value="<?php echo $user['age'] ?: ''; ?>"
                                        min="18"
                                        max="100"
                                        placeholder="Votre âge"
                                    >
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">
                                        <i class="fas fa-map-marker-alt"></i> Adresse
                                    </label>
                                    <input 
                                        type="text" 
                                        id="address" 
                                        name="address" 
                                        value="<?php echo htmlspecialchars($user['address'] ?: ''); ?>"
                                        placeholder="Votre adresse"
                                    >
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">
                                    <i class="fas fa-info-circle"></i> Biographie *
                                </label>
                                <textarea 
                                    id="bio" 
                                    name="bio" 
                                    placeholder="Parlez-nous un peu de vous... (minimum 10 mots)"
                                    required
                                ><?php echo htmlspecialchars($user['bio'] ?: ''); ?></textarea>
                                <small class="form-text text-muted">Minimum 10 mots</small>
                            </div>
                            
                            <!-- Centres d'Intérêt -->
                            <div class="mb-4" id="interestsContainer">
                                <label class="form-label">
                                    <i class="fas fa-heart"></i> Centres d'Intérêt *
                                </label>
                                <div class="row">
                                    <?php 
                                    $interestsList = [
                                        'Sports', 'Musique', 'Technologie', 'Arts', 
                                        'Voyages', 'Lecture', 'Cinéma', 'Cuisine',
                                        'Photographie', 'Danse', 'Sciences', 'Mode',
                                        'Jeux Vidéo', 'Nature', 'Bénévolat', 'Entrepreneuriat'
                                    ];
                                    
                                    $selectedInterests = [];
                                    if ($user['interests']) {
                                        $selectedInterests = explode(', ', $user['interests']);
                                    }
                                    
                                    foreach ($interestsList as $interest): 
                                        $checked = in_array($interest, $selectedInterests) ? 'checked' : '';
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="interests[]" 
                                                value="<?php echo $interest; ?>" 
                                                id="interest_edit_<?php echo str_replace(' ', '_', $interest); ?>"
                                                <?php echo $checked; ?>
                                            >
                                            <label class="form-check-label" for="interest_edit_<?php echo str_replace(' ', '_', $interest); ?>">
                                                <?php echo $interest; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted d-block mt-2">Sélectionnez au moins un centre d'intérêt</small>
                            </div>
                            
                            <div style="display: flex; gap: 15px; margin-top: 30px;">
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                                <button type="button" class="btn-cancel" onclick="toggleEditMode()">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
                
                <?php endif; ?>
                
            </div>
        </div>
    </section>

    <!-- Start Footer -->
    <footer class="bg-dark" id="tempaltemo_footer">
        <div class="container">
            <div class="row">

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-success border-bottom pb-3 border-light logo">SOLIDA</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            Tunis, Tunisie
                        </li>
                        <li>
                            <i class="fa fa-phone fa-fw"></i>
                            <a class="text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                        </li>
                        <li>
                            <i class="fa fa-envelope fa-fw"></i>
                            <a class="text-decoration-none" href="mailto:info@company.com">info@solida.com</a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Liens Rapides</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="index.php">Accueil</a></li>
                        <li><a class="text-decoration-none" href="#">Événements</a></li>
                        <li><a class="text-decoration-none" href="#">Dons</a></li>
                        <li><a class="text-decoration-none" href="#">Réclamations</a></li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Suivez-nous</h2>
                    <ul class="list-inline">
                        <li class="list-inline-item"><a class="text-light" target="_blank" href="#"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a></li>
                        <li class="list-inline-item"><a class="text-light" target="_blank" href="#"><i class="fab fa-instagram fa-lg fa-fw"></i></a></li>
                        <li class="list-inline-item"><a class="text-light" target="_blank" href="#"><i class="fab fa-twitter fa-lg fa-fw"></i></a></li>
                        <li class="list-inline-item"><a class="text-light" target="_blank" href="#"><i class="fab fa-linkedin fa-lg fa-fw"></i></a></li>
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
                            <a class="text-light text-decoration-none" target="_blank" href="#"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="#"><i class="fab fa-instagram fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="#"><i class="fab fa-twitter fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="#"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="subscribeEmail">Email address</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control bg-dark border-light" id="subscribeEmail" placeholder="Email">
                        <div class="input-group-text btn-success text-light">S'abonner</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 bg-black py-3">
            <div class="container">
                <div class="row pt-2">
                    <div class="col-12">
                        <p class="text-left text-light">
                            Copyright &copy; 2025 SOLIDA - Plateforme Étudiante
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </footer>
    <!-- End Footer -->

    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/update-profile.js"></script>
    
    <script>
        // Toggle user dropdown menu
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Toggle entre mode visualisation et mode édition
        function toggleEditMode() {
            const viewMode = document.getElementById('profileView');
            const editMode = document.getElementById('profileEdit');
            const editBtn = document.getElementById('editProfileBtn');
            
            if (viewMode.style.display === 'none') {
                // Retour au mode visualisation
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Modifier le Compte';
                // Scroll vers le haut
                viewMode.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                // Passage en mode édition
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                editBtn.innerHTML = '<i class="fas fa-eye"></i> Voir le Profil';
                // Scroll vers le formulaire
                editMode.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        // Fonction de confirmation de suppression du profil
        function confirmDelete() {
            const confirmation = confirm(
                "⚠️ ATTENTION ⚠️\n\n" +
                "Êtes-vous ABSOLUMENT sûr(e) de vouloir supprimer votre profil ?\n\n" +
                "Cette action est DÉFINITIVE et IRRÉVERSIBLE :\n" +
                "• Toutes vos données seront supprimées\n" +
                "• Vos événements suivis seront perdus\n" +
                "• Votre historique sera effacé\n\n" +
                "Cliquez sur OK pour confirmer la suppression, ou Annuler pour garder votre compte."
            );
            
            if (confirmation) {
                // Demander une double confirmation
                const doubleConfirm = confirm(
                    "Dernière confirmation !\n\n" +
                    "Êtes-vous vraiment sûr(e) ?\n\n" +
                    "Cette action ne peut PAS être annulée."
                );
                
                if (doubleConfirm) {
                    // Rediriger vers l'action de suppression
                    window.location.href = '../../controllers/userControllers.php?confirm=yes';
                }
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
