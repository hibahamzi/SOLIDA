<?php
// Connexion à la base de données
require_once '../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

$success = '';
$error = '';
$users = [];

// Récupérer les messages de session
if (isset($_SESSION['users_success'])) {
    $success = $_SESSION['users_success'];
    unset($_SESSION['users_success']);
}

if (isset($_SESSION['users_message'])) {
    $error = $_SESSION['users_message'];
    unset($_SESSION['users_message']);
}

// Gestion des erreurs de formulaire
$formErrors = [];
if (isset($_SESSION['users_errors'])) {
    $formErrors = $_SESSION['users_errors'];
    unset($_SESSION['users_errors']);
}

// Gestion de la suppression d'utilisateur - Déplacer vers le contrôleur
// Note: Les actions sont maintenant gérées par UsersBackController.php

// Récupération des paramètres de recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Construire la requête SQL
try {
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];
    
    // Filtre de recherche
    if (!empty($search)) {
        $sql .= " AND (fullname LIKE ? OR email LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Filtre par rôle
    if ($roleFilter !== 'all') {
        $sql .= " AND role = ?";
        $params[] = $roleFilter;
    }
    
    // Tri
    $allowedSorts = ['id', 'fullname', 'email', 'role', 'created_at'];
    if (in_array($sortBy, $allowedSorts)) {
        $sql .= " ORDER BY $sortBy " . ($order === 'ASC' ? 'ASC' : 'DESC');
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Compter les statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $totalRegularUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - SOLIDA Admin</title>
    
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
                <a href="users.php" class="active">
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
            <h1>Gestion des Utilisateurs</h1>
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
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Total Utilisateurs</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalUsers) ? $totalUsers : '0'; ?></div>
                    <div class="stat-label">Tous les utilisateurs</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Administrateurs</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalAdmins) ? $totalAdmins : '0'; ?></div>
                    <div class="stat-label">Comptes administrateurs</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>Utilisateurs Standards</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalRegularUsers) ? $totalRegularUsers : '0'; ?></div>
                    <div class="stat-label">Comptes utilisateurs</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>Résultats</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo count($users); ?></div>
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
                            placeholder="Rechercher par nom ou email..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                        >
                    </div>
                    
                    <select name="role">
                        <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>Tous les rôles</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Administrateurs</option>
                        <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>Utilisateurs</option>
                    </select>
                    
                    <select name="sort">
                        <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Trier par date</option>
                        <option value="fullname" <?php echo $sortBy === 'fullname' ? 'selected' : ''; ?>>Trier par nom</option>
                        <option value="email" <?php echo $sortBy === 'email' ? 'selected' : ''; ?>>Trier par email</option>
                        <option value="role" <?php echo $sortBy === 'role' ? 'selected' : ''; ?>>Trier par rôle</option>
                    </select>
                    
                    <select name="order">
                        <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                        <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <a href="users.php" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                    
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Liste des Utilisateurs</h2>
                    <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                        <i class="fas fa-plus"></i> Ajouter un Utilisateur
                    </button>
                </div>
                
                <div class="table-responsive">
                    <?php if (count($users) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Âge</th>
                                <th>Date d'Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                                            <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($user['fullname']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-envelope" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $user['age'] ? $user['age'] . ' ans' : '-'; ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px; color: #bcbcbc;"></i>
                                    <?php 
                                        $date = new DateTime($user['created_at']);
                                        echo $date->format('d/m/Y'); 
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            title="Modifier"
                                            onclick='openEditUserModal(<?php echo json_encode($user); ?>)'
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <a 
                                            href="../../controllers/userControllers.php?action=toggle_role&id=<?php echo $user['id']; ?>" 
                                            class="btn btn-secondary btn-sm"
                                            title="Changer le rôle"
                                            onclick="return confirm('Voulez-vous vraiment changer le rôle de cet utilisateur?');"
                                        >
                                            <i class="fas fa-exchange-alt"></i>
                                        </a>
                                        
                                        <a 
                                            href="../../controllers/userControllers.php?action=delete&id=<?php echo $user['id']; ?>" 
                                            class="btn btn-danger btn-sm"
                                            title="Supprimer"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? Cette action est irréversible.');"
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
                        <strong style="display: block; font-size: 18px; margin-bottom: 10px;">Aucun utilisateur trouvé</strong>
                        <?php if (!empty($search) || $roleFilter !== 'all'): ?>
                        <span style="font-size: 14px;">Essayez de modifier vos critères de recherche</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Ajouter un Utilisateur</h2>
                <button type="button" class="close-modal" onclick="closeAddUserModal()">&times;</button>
            </div>
            <form id="addUserForm" action="../../controllers/userControllers.php" method="POST">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_fullname">
                            <i class="fas fa-user"></i> Nom Complet *
                        </label>
                        <input type="text" id="add_fullname" name="fullname" placeholder="Ex: Jean Dupont" required>
                        <small>Au moins 2 mots (prénom et nom)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_email">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input type="email" id="add_email" name="email" placeholder="exemple@email.com" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_password">
                            <i class="fas fa-lock"></i> Mot de passe *
                        </label>
                        <input type="password" id="add_password" name="password" placeholder="Min 8 caractères" required>
                        <small>Min 8 caractères + chiffre ou symbole</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_role">
                            <i class="fas fa-user-shield"></i> Rôle *
                        </label>
                        <select id="add_role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="add_age">
                            <i class="fas fa-birthday-cake"></i> Âge *
                        </label>
                        <input type="number" id="add_age" name="age" placeholder="Ex: 30" min="18" max="120" required>
                        <small>Minimum 18 ans</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="add_address">
                            <i class="fas fa-map-marker-alt"></i> Adresse *
                        </label>
                        <input type="text" id="add_address" name="address" placeholder="Ex: 123 rue de la Liberté" required>
                        <small>Format: numéro + rue/avenue/boulevard + nom</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add_bio">
                        <i class="fas fa-info-circle"></i> Biographie *
                    </label>
                    <textarea id="add_bio" name="bio" placeholder="Parlez-nous un peu de vous... (minimum 10 mots)" required></textarea>
                    <small><span id="add_bioWordCount">0 mots</span> (minimum 10 mots)</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-heart"></i> Centres d'Intérêt * (sélectionnez au moins 1)</label>
                    <div class="interests-grid">
                        <?php
                        $interests = ['Sports', 'Musique', 'Technologie', 'Arts', 'Voyages', 'Lecture', 'Cinéma', 'Cuisine', 'Photographie', 'Danse', 'Sciences', 'Mode', 'Jeux Vidéo', 'Nature', 'Bénévolat', 'Entrepreneuriat'];
                        foreach ($interests as $interest): ?>
                        <div class="interest-checkbox">
                            <input type="checkbox" id="add_interest_<?php echo str_replace(' ', '_', $interest); ?>" name="interests[]" value="<?php echo $interest; ?>">
                            <label for="add_interest_<?php echo str_replace(' ', '_', $interest); ?>"><?php echo $interest; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Créer l'Utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Modifier l'Utilisateur</h2>
                <button type="button" class="close-modal" onclick="closeEditUserModal()">&times;</button>
            </div>
            <form id="editUserForm" action="../../controllers/userControllers.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_user_id" name="user_id" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_fullname">
                            <i class="fas fa-user"></i> Nom Complet *
                        </label>
                        <input type="text" id="edit_fullname" name="fullname" placeholder="Ex: Jean Dupont" required>
                        <small>Au moins 2 mots (prénom et nom)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input type="email" id="edit_email" name="email" placeholder="exemple@email.com" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_password">
                            <i class="fas fa-lock"></i> Nouveau Mot de passe
                        </label>
                        <input type="password" id="edit_password" name="password" placeholder="Laisser vide pour ne pas changer">
                        <small>Min 8 caractères + chiffre ou symbole (optionnel)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">
                            <i class="fas fa-user-shield"></i> Rôle *
                        </label>
                        <select id="edit_role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_age">
                            <i class="fas fa-birthday-cake"></i> Âge *
                        </label>
                        <input type="number" id="edit_age" name="age" placeholder="Ex: 30" min="18" max="120" required>
                        <small>Minimum 18 ans</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_address">
                            <i class="fas fa-map-marker-alt"></i> Adresse *
                        </label>
                        <input type="text" id="edit_address" name="address" placeholder="Ex: 123 rue de la Liberté" required>
                        <small>Format: numéro + rue/avenue/boulevard + nom</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_bio">
                        <i class="fas fa-info-circle"></i> Biographie *
                    </label>
                    <textarea id="edit_bio" name="bio" placeholder="Parlez-nous un peu de vous... (minimum 10 mots)" required></textarea>
                    <small><span id="edit_bioWordCount">0 mots</span> (minimum 10 mots)</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-heart"></i> Centres d'Intérêt * (sélectionnez au moins 1)</label>
                    <div class="interests-grid" id="editInterestsGrid">
                        <?php foreach ($interests as $interest): ?>
                        <div class="interest-checkbox">
                            <input type="checkbox" id="edit_interest_<?php echo str_replace(' ', '_', $interest); ?>" name="interests[]" value="<?php echo $interest; ?>">
                            <label for="edit_interest_<?php echo str_replace(' ', '_', $interest); ?>"><?php echo $interest; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">
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
        
        .modal .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .modal .interests-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 10px;
        }
        
        .modal .interest-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .modal .interest-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .modal .interest-checkbox label {
            margin: 0;
            cursor: pointer;
            font-size: 13px;
            font-weight: normal;
        }
        
        .modal .invalid-feedback {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 5px;
            display: block;
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
            
            .modal .interests-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
    
    <!-- JavaScript -->
    <script src="assets/js/users-back.js"></script>
    <script>
        // Fonctions pour gérer les modals
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function openEditUserModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_fullname').value = user.fullname;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_age').value = user.age || '';
            document.getElementById('edit_address').value = user.address || '';
            document.getElementById('edit_bio').value = user.bio || '';
            
            // Décocher toutes les cases
            document.querySelectorAll('#editUserForm input[name="interests[]"]').forEach(cb => cb.checked = false);
            
            // Cocher les intérêts de l'utilisateur
            if (user.interests) {
                const userInterests = user.interests.split(', ');
                userInterests.forEach(interest => {
                    const checkbox = document.querySelector(`#edit_interest_${interest.replace(' ', '_')}`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            document.getElementById('editUserModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditUserModal() {
            document.getElementById('editUserModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Fermer les modals en cliquant en dehors
        window.onclick = function(event) {
            const addModal = document.getElementById('addUserModal');
            const editModal = document.getElementById('editUserModal');
            if (event.target == addModal) {
                closeAddUserModal();
            }
            if (event.target == editModal) {
                closeEditUserModal();
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
        
        // Ouvrir le modal automatiquement si spécifié dans l'URL
        <?php if (isset($_GET['modal']) && $_GET['modal'] === 'add'): ?>
        openAddUserModal();
        <?php endif; ?>
        
        <?php if (isset($_GET['modal']) && $_GET['modal'] === 'edit' && isset($_GET['id'])): ?>
        // Charger les données de l'utilisateur à modifier
        <?php
        require_once '../../models/UserModel.php';
        require_once '../../controllers/userControllers.php';
        $userController = new userController();
        $editUser = $userController->getUserById(intval($_GET['id']));
        if ($editUser):
        ?>
        openEditUserModal(<?php echo json_encode($editUser); ?>);
        <?php endif; ?>
        <?php endif; ?>
    </script>
    
</body>
</html>
