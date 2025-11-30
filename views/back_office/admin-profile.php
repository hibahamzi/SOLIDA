<?php
session_start();

// Vérifier si l'utilisateur est connecté et est admin
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: ../front_office/sign-in.php');
//     exit();
// }

// Connexion à la base de données
require_once '../../config/config.php';

$success = '';
$error = '';
$admin = null;

// Utiliser l'ID de session ou un ID par défaut pour la démo
$adminId = $_SESSION['user_id'] ?? 1;

// Afficher message de succès après mise à jour
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "Profil administrateur mis à jour avec succès!";
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

// Récupérer les informations de l'admin
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        $error = "Administrateur non trouvé.";
    }
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Liste des centres d'intérêt disponibles
$availableInterests = [
    'Sports', 'Musique', 'Technologie', 'Arts', 'Voyages', 'Lecture',
    'Cinéma', 'Cuisine', 'Photographie', 'Danse', 'Sciences', 'Mode',
    'Jeux Vidéo', 'Nature', 'Bénévolat', 'Entrepreneuriat'
];

// Récupérer les intérêts actuels de l'admin
$currentInterests = $admin && $admin['interests'] ? explode(', ', $admin['interests']) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <style>
        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #212934;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #59ab6e;
        }
        
        .form-group input.is-invalid,
        .form-group textarea.is-invalid {
            border-color: #e74c3c;
        }
        
        .form-group input.is-valid,
        .form-group textarea.is-valid {
            border-color: #59ab6e;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .invalid-feedback {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .interests-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 10px;
        }
        
        .interest-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .interest-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .interest-checkbox label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .interests-grid {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="events.php">
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
                <a href="admin-profile.php" class="active">
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
            <h1>Mon Profil Administrateur</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                        if ($admin) {
                            $nameParts = explode(' ', $admin['fullname']);
                            $initials = strtoupper(substr($nameParts[0], 0, 1));
                            if (count($nameParts) > 1) {
                                $initials .= strtoupper(substr($nameParts[1], 0, 1));
                            }
                            echo $initials;
                        } else {
                            echo 'A';
                        }
                    ?>
                </div>
                <div class="user-details">
                    <span><?php echo $admin ? htmlspecialchars($admin['fullname']) : 'Administrateur'; ?></span>
                    <small><?php echo $admin ? htmlspecialchars($admin['email']) : 'admin@solida.com'; ?></small>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            
            <?php if ($success): ?>
            <div class="alert alert-success" id="successAlert">
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
            
            <?php if ($admin): ?>
            
            <!-- Profile View Mode (Default) -->
            <div class="profile-card" id="profileView">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="color: #212934; font-size: 22px; font-weight: 600; margin: 0;">
                        <i class="fas fa-user-circle" style="color: #59ab6e; margin-right: 10px;"></i>
                        Informations du Profil
                    </h2>
                    <button type="button" class="btn btn-primary" id="editProfileBtn" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i> Modifier le Profil
                    </button>
                </div>
                
                <div class="form-row" style="margin-bottom: 20px;">
                    <div>
                        <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-user"></i> Nom Complet
                        </label>
                        <p style="color: #212934; font-size: 15px; font-weight: 500; margin: 0;">
                            <?php echo htmlspecialchars($admin['fullname']); ?>
                        </p>
                    </div>
                    
                    <div>
                        <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <p style="color: #212934; font-size: 15px; font-weight: 500; margin: 0;">
                            <?php echo htmlspecialchars($admin['email']); ?>
                        </p>
                    </div>
                </div>
                
                <div class="form-row" style="margin-bottom: 20px;">
                    <div>
                        <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-birthday-cake"></i> Âge
                        </label>
                        <p style="color: #212934; font-size: 15px; font-weight: 500; margin: 0;">
                            <?php echo $admin['age'] ? $admin['age'] . ' ans' : 'Non renseigné'; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                            <i class="fas fa-map-marker-alt"></i> Adresse
                        </label>
                        <p style="color: #212934; font-size: 15px; font-weight: 500; margin: 0;">
                            <?php echo $admin['address'] ? htmlspecialchars($admin['address']) : 'Non renseignée'; ?>
                        </p>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                        <i class="fas fa-info-circle"></i> Biographie
                    </label>
                    <p style="color: #212934; font-size: 15px; line-height: 1.6; margin: 0;">
                        <?php echo $admin['bio'] ? nl2br(htmlspecialchars($admin['bio'])) : 'Aucune biographie'; ?>
                    </p>
                </div>
                
                <div>
                    <label style="color: #bcbcbc; font-size: 13px; display: block; margin-bottom: 5px;">
                        <i class="fas fa-heart"></i> Centres d'Intérêt
                    </label>
                    <p style="color: #212934; font-size: 15px; margin: 0;">
                        <?php 
                            if ($admin['interests']) {
                                $interests = explode(', ', $admin['interests']);
                                foreach ($interests as $interest) {
                                    echo '<span style="display: inline-block; background: #e9eef5; color: #212934; padding: 5px 12px; border-radius: 15px; margin: 0 5px 5px 0; font-size: 13px;">' . htmlspecialchars($interest) . '</span>';
                                }
                            } else {
                                echo 'Aucun centre d\'intérêt';
                            }
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Profile Edit Mode (Hidden by default) -->
            <div class="profile-card" id="profileEdit" style="display: none;">
                <h2 style="color: #212934; font-size: 22px; font-weight: 600; margin-bottom: 25px;">
                    <i class="fas fa-edit" style="color: #59ab6e; margin-right: 10px;"></i>
                    Modifier le Profil
                </h2>
                
                <form id="adminProfileForm" action="../../controllers/userControllers.php" method="POST">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullname">
                                <i class="fas fa-user"></i> Nom Complet *
                            </label>
                            <input 
                                type="text" 
                                class="form-control <?php echo isset($updateErrors['fullname']) ? 'is-invalid' : ''; ?>" 
                                id="fullname" 
                                name="fullname" 
                                value="<?php echo htmlspecialchars($admin['fullname']); ?>"
                                placeholder="Ex: Jean Dupont"
                                required
                            >
                            <?php if (isset($updateErrors['fullname'])): ?>
                            <div class="invalid-feedback"><?php echo $updateErrors['fullname']; ?></div>
                            <?php endif; ?>
                            <small style="color: #bcbcbc; font-size: 12px;">Au moins 2 mots (prénom et nom)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input 
                                type="email" 
                                class="form-control <?php echo isset($updateErrors['email']) ? 'is-invalid' : ''; ?>" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($admin['email']); ?>"
                                placeholder="exemple@email.com"
                                required
                            >
                            <?php if (isset($updateErrors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $updateErrors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">
                                <i class="fas fa-birthday-cake"></i> Âge *
                            </label>
                            <input 
                                type="number" 
                                class="form-control <?php echo isset($updateErrors['age']) ? 'is-invalid' : ''; ?>" 
                                id="age" 
                                name="age" 
                                value="<?php echo $admin['age'] ?: ''; ?>"
                                min="18"
                                max="120"
                                placeholder="Ex: 30"
                                required
                            >
                            <?php if (isset($updateErrors['age'])): ?>
                            <div class="invalid-feedback"><?php echo $updateErrors['age']; ?></div>
                            <?php endif; ?>
                            <small style="color: #bcbcbc; font-size: 12px;">Minimum 18 ans</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">
                                <i class="fas fa-map-marker-alt"></i> Adresse *
                            </label>
                            <input 
                                type="text" 
                                class="form-control <?php echo isset($updateErrors['address']) ? 'is-invalid' : ''; ?>" 
                                id="address" 
                                name="address" 
                                value="<?php echo htmlspecialchars($admin['address'] ?: ''); ?>"
                                placeholder="Ex: 123 rue de la Liberté"
                                required
                            >
                            <?php if (isset($updateErrors['address'])): ?>
                            <div class="invalid-feedback"><?php echo $updateErrors['address']; ?></div>
                            <?php endif; ?>
                            <small style="color: #bcbcbc; font-size: 12px;">Format: numéro + rue/avenue/boulevard + nom</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">
                            <i class="fas fa-info-circle"></i> Biographie *
                        </label>
                        <textarea 
                            class="form-control <?php echo isset($updateErrors['bio']) ? 'is-invalid' : ''; ?>" 
                            id="bio" 
                            name="bio" 
                            placeholder="Parlez-nous un peu de vous... (minimum 10 mots)"
                            required
                        ><?php echo htmlspecialchars($admin['bio'] ?: ''); ?></textarea>
                        <?php if (isset($updateErrors['bio'])): ?>
                        <div class="invalid-feedback"><?php echo $updateErrors['bio']; ?></div>
                        <?php endif; ?>
                        <small style="color: #bcbcbc; font-size: 12px;">
                            <span id="bioWordCount">0 mots</span> (minimum 10 mots)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-heart"></i> Centres d'Intérêt * (sélectionnez au moins 1)
                        </label>
                        <div class="interests-grid">
                            <?php foreach ($availableInterests as $interest): ?>
                            <div class="interest-checkbox">
                                <input 
                                    type="checkbox" 
                                    id="interest_<?php echo str_replace(' ', '_', $interest); ?>" 
                                    name="interests[]" 
                                    value="<?php echo $interest; ?>"
                                    <?php echo in_array($interest, $currentInterests) ? 'checked' : ''; ?>
                                >
                                <label for="interest_<?php echo str_replace(' ', '_', $interest); ?>">
                                    <?php echo $interest; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($updateErrors['interests'])): ?>
                        <div class="invalid-feedback"><?php echo $updateErrors['interests']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                    </div>
                    
                </form>
            </div>
            
            <?php endif; ?>
            
        </div>
        
    </div>
    
    <script src="assets/js/admin-profile.js"></script>
    <script>
        // Toggle entre mode visualisation et mode édition
        function toggleEditMode() {
            const viewMode = document.getElementById('profileView');
            const editMode = document.getElementById('profileEdit');
            const editBtn = document.getElementById('editProfileBtn');
            
            if (viewMode.style.display === 'none') {
                // Retour au mode visualisation
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
            } else {
                // Passage en mode édition
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                // Scroll vers le formulaire
                editMode.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
        
        // Auto-hide success messages after 3 seconds
        const successAlert = document.getElementById('successAlert');
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
    
</body>
</html>
