<?php
session_start();

// Vérifier si l'utilisateur est connecté et est admin
// Décommentez ces lignes lorsque vous avez le système d'authentification
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../front_office/sign-in.php');
//     exit();
// }

// Connexion à la base de données
require_once '../../connection.php';

// Récupérer les statistiques


// Valeurs par défaut pour les autres statistiques
$totalEvents = 0; // À implémenter avec la table événements
$totalDonations = 0; // À implémenter avec la table dons
$totalClaims = 0; // À implémenter avec la table réclamations
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SOLIDA Admin</title>
    <link rel="stylesheet" href="../../css/admin.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    </head>
<body>
    
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>SOLIDA</h2>
            <p>Panneau d'Administration</p>
        </div>
        
        <div class="sidebar-user-info">
            <div class="user-info-detail">
                <div class="user-avatar-initials">
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
                <div class="user-details-text">
                    <div class="name">
                        <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? 'Administrateur'); ?>
                    </div>
                    <div class="email">
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
                <a href="events.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Événements</span>
                </a>
            </li>
            <li>
                <a href="backofficedons.php">
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
    
    <div class="main-content">
        
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
        
        <div class="content-area">
            
            <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Utilisateurs</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalUsers) ? $totalUsers : '0'; ?></div>
                    <div class="stat-label">Total des utilisateurs inscrits</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Événements</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalEvents; ?></div>
                    <div class="stat-label">Événements actifs</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>Dons</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalDonations; ?></div>
                    <div class="stat-label">Dons reçus ce mois</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>Réclamations</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalClaims; ?></div>
                    <div class="stat-label">En attente de traitement</div>
                </div>
                
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h2>Derniers Utilisateurs Inscrits</h2>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Voir Tous
                    </a>
                </div>
                
                <div class="table-responsive">
                    <?php if (isset($recentUsers) && count($recentUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle" style="margin-right: 8px; color: #59ab6e;"></i>
                                    <?php echo htmlspecialchars($user['fullname']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px;"></i>
                                    <?php 
                                        $date = new DateTime($user['created_at']);
                                        echo $date->format('d/m/Y H:i'); 
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="table-empty-message">
                        <i class="fas fa-inbox"></i>
                        Aucun utilisateur trouvé
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="quick-actions-grid">
                
                <div class="info-card">
                    <h3>
                        <i class="fas fa-chart-line"></i>
                        Statistiques Rapides
                    </h3>
                    <p>
                        <strong>Administrateurs:</strong> <?php echo isset($totalAdmins) ? $totalAdmins : '0'; ?><br>
                        <strong>Utilisateurs Standards:</strong> <?php echo isset($totalUsers) && isset($totalAdmins) ? ($totalUsers - $totalAdmins) : '0'; ?><br>
                        <strong>Plateforme:</strong> Active
                    </p>
                </div>
                
                <div class="info-card">
                    <h3>
                        <i class="fas fa-bell"></i>
                        Notifications
                    </h3>
                    <p>
                        Bienvenue sur le panneau d'administration SOLIDA.<br>
                        Gérez efficacement tous les aspects de votre plateforme étudiante.
                    </p>
                </div>
                
            </div>
            
        </div>
        
    </div>
    <style>
        /* ============================================
   SOLIDA - Back Office Admin Styles
   Matching Front Office Color Palette
   ============================================ */

:root {
    --primary-green: #59ab6e;
    --primary-dark: #212934;
    --hover-green: #69bb7e;
    --light-bg: #e9eef5;
    --text-light: #cfd6e1;
    --text-muted: #bcbcbc;
    --warning: #f39c12; /* Couleur pour l'icône de notification */
    --danger: #e74c3c; /* Couleur pour le message d'erreur/alertes */
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 4px 16px rgba(0, 0, 0, 0.15);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--light-bg);
    color: var(--primary-dark);
    line-height: 1.6;
}

/* ============================================
   SIDE NAVBAR
   ============================================ */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, var(--primary-dark) 0%, #1a2028 100%);
    color: var(--text-light);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 30px 20px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h2 {
    color: var(--primary-green);
    margin: 0;
    font-weight: 700;
    font-size: 24px;
}

.sidebar-header p {
    margin: 5px 0 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

/* (NEW) Custom User Info Block */
.sidebar-user-info {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.user-info-detail {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-initials {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-green), var(--hover-green));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}

.user-details-text {
    flex: 1;
    min-width: 0;
}

.user-details-text .name {
    color: white;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-details-text .email {
    color: rgba(255,255,255,0.7);
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    flex-grow: 1;
    overflow-y: auto;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: var(--text-light);
    text-decoration: none;
    transition: background-color 0.3s, color 0.3s;
}

.sidebar-menu li a i {
    font-size: 18px;
    width: 30px;
    text-align: center;
    margin-right: 15px;
}

.sidebar-menu li a:hover,
.sidebar-menu li a.active {
    background-color: var(--hover-green);
    color: white;
}

/* ============================================
   MAIN CONTENT & TOP BAR
   ============================================ */

.main-content {
    margin-left: 260px; /* Offset for the fixed sidebar */
    padding: 0;
    min-height: 100vh;
    background-color: var(--light-bg);
    display: flex;
    flex-direction: column;
}

.top-bar {
    background-color: white;
    padding: 20px 30px;
    box-shadow: var(--shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
}

.top-bar h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 500;
    color: var(--primary-dark);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.user-details span {
    display: block;
    font-weight: 500;
    font-size: 14px;
    color: var(--primary-dark);
}

.user-details small {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
}

.content-area {
    padding: 30px;
    flex-grow: 1;
}

/* ============================================
   CONTENT AREA - Alerts & Quick Grids (NEW)
   ============================================ */

.error-message {
    background: var(--danger);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 16px;
    font-weight: 500;
}

.quick-actions-grid {
    margin-top: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

/* (NEW) General style for the secondary information cards, replacing table-container misuse */
.info-card {
    background: white; 
    border-radius: 8px; 
    box-shadow: var(--shadow);
    padding: 25px; 
    transition: all 0.3s ease;
}

.info-card h3 {
    color: var(--primary-dark); 
    margin-bottom: 15px;
    font-size: 18px; 
    font-weight: 500;
}

.info-card h3 i {
    color: var(--primary-green);
    margin-right: 10px;
}

.info-card .fa-bell {
    color: var(--warning); 
}

.info-card p {
    color: var(--text-muted); 
    font-size: 14px; 
    line-height: 1.8;
}

/* ============================================
   STATS CARDS
   ============================================ */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s ease;
    border-left: 5px solid; /* Base for color strip */
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.stat-card h3 {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
    color: var(--primary-dark);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

/* Specific Card Styles */
.stat-card.users { border-left-color: #3498db; }
.stat-card.users .stat-icon { background-color: #3498db; }

.stat-card.events { border-left-color: var(--primary-green); }
.stat-card.events .stat-icon { background-color: var(--primary-green); }

.stat-card.donations { border-left-color: #f1c40f; }
.stat-card.donations .stat-icon { background-color: #f1c40f; }

.stat-card.claims { border-left-color: var(--danger); }
.stat-card.claims .stat-icon { background-color: var(--danger); }


.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ============================================
   TABLES
   ============================================ */

.table-container {
    background-color: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 25px;
    margin-bottom: 30px;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.table-header h2 {
    font-size: 20px;
    font-weight: 500;
    color: var(--primary-dark);
    margin: 0;
}

.btn-primary {
    background-color: var(--primary-green);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: var(--hover-green);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: var(--light-bg);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    color: var(--primary-dark);
}

th {
    font-weight: 600;
    color: var(--primary-dark);
    text-transform: uppercase;
    font-size: 12px;
}

tbody tr:hover {
    background-color: #f5f5f5;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.badge.admin {
    background-color: var(--primary-dark);
    color: white;
}

.badge.user {
    background-color: var(--primary-green);
    color: white;
}

/* (NEW) Table empty state */
.table-empty-message {
    text-align: center; 
    padding: 40px; 
    color: var(--text-muted); 
    font-size: 16px;
    line-height: 1.5;
}

.table-empty-message i {
    font-size: 48px; 
    display: block; 
    margin-bottom: 15px;
}


/* ============================================
   RESPONSIVE
   ============================================ */

@media (max-width: 992px) {
    .sidebar {
        width: 80px;
    }
    .sidebar-header h2, .sidebar-header p {
        display: none;
    }
    .sidebar-menu li a span {
        display: none;
    }
    .sidebar-menu li a {
        justify-content: center;
        padding: 15px 0;
    }
    .sidebar-menu li a i {
        margin: 0;
    }
    .main-content {
        margin-left: 80px;
    }
    .sidebar-user-info {
        display: none; /* Cache le bloc user info sur petit écran */
    }
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
    .sidebar {
        display: none; /* Cache la barre latérale sur très petit écran */
    }
}
    </style>
    
</body>
</html>