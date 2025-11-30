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

// Récupérer les statistiques
try {
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Compter les admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Récupérer les derniers utilisateurs inscrits
    $stmt = $pdo->query("SELECT fullname, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}

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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
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
        
        <!-- Content Area -->
        <div class="content-area">
            
            <?php if (isset($error)): ?>
            <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
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
            
            <!-- Recent Users Table -->
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
                    <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Aucun utilisateur trouvé
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                
                <div class="table-container" style="padding: 25px;">
                    <h3 style="color: #212934; margin-bottom: 15px; font-size: 18px;">
                        <i class="fas fa-chart-line" style="color: #59ab6e; margin-right: 10px;"></i>
                        Statistiques Rapides
                    </h3>
                    <p style="color: #bcbcbc; font-size: 14px; line-height: 1.8;">
                        <strong>Administrateurs:</strong> <?php echo isset($totalAdmins) ? $totalAdmins : '0'; ?><br>
                        <strong>Utilisateurs Standards:</strong> <?php echo isset($totalUsers) && isset($totalAdmins) ? ($totalUsers - $totalAdmins) : '0'; ?><br>
                        <strong>Plateforme:</strong> Active
                    </p>
                </div>
                
                <div class="table-container" style="padding: 25px;">
                    <h3 style="color: #212934; margin-bottom: 15px; font-size: 18px;">
                        <i class="fas fa-bell" style="color: #f39c12; margin-right: 10px;"></i>
                        Notifications
                    </h3>
                    <p style="color: #bcbcbc; font-size: 14px; line-height: 1.8;">
                        Bienvenue sur le panneau d'administration SOLIDA.<br>
                        Gérez efficacement tous les aspects de votre plateforme étudiante.
                    </p>
                </div>
                
            </div>
            
        </div>
        
    </div>
    
</body>
</html>
