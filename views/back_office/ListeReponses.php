<?php
// ============================================
// FICHIER DE LISTE DES RÉPONSES ADMINISTRATIVES
// À placer dans views/back_office/
// ============================================

// CORRECTION DU CHEMIN: views/back_office/ -> ../.. -> controllers/ReclamationController.php
require_once __DIR__ . '/../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

require_once __DIR__ . '/../../controllers/ReclamationController.php';

// 🔧 Afficher les erreurs (pour debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// LOGIQUE PHP
// ============================================
$reclamationController = new ReclamationController($pdo);
$responses = [];
$message = '';

// --- ATTENTION ---
// Vous devez ajouter la méthode getAllAdminResponses() à votre ReclamationController.php
// Cette méthode doit récupérer TOUTES les réponses de la table 'adminreponse' (ou équivalent)
// et les retourner sous forme de tableau de tableaux/objets.
// Exemple d'implémentation dans ReclamationController.php:
/*
public function getAllAdminResponses() {
    // Logique de connexion à la base de données et requête SQL
    // $sql = "SELECT * FROM adminreponse ORDER BY date_creation DESC";
    // ...
    // return $resultats;
}
*/

// Tentative de récupération des données
try {
    $responses = $reclamationController->getAllAdminResponses();
    if (empty($responses)) {
        $message = '<div class="info-message">ℹ️ Aucune réponse administrative n\'a été trouvée.</div>';
    }
} catch (Exception $e) {
    $message = '<div class="error-message">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $responses = [];
}

// ============================================
// VUE HTML
// ============================================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Réponses Administratives - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <style>
        :root { --color-primary: #4CAF50; --color-light: #E8F5E9; --color-dark: #388E3C; --color-text: #333; --color-error: #F44336; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .content-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: var(--color-dark); font-size: 28px; margin-bottom: 30px; text-align: center; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .back-link { display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; color: var(--color-primary); text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .back-link:hover { color: var(--color-dark); text-decoration: underline; }
        
        /* Styles de la table */
        .response-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .response-table th, .response-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        .response-table th { background-color: var(--color-primary); color: white; font-weight: 600; text-transform: uppercase; }
        .response-table tr:nth-child(even) { background-color: #f8f8f8; }
        .response-table tr:hover { background-color: #f0f0f0; }
        
        /* Styles des badges et boutons */
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .status-Nouveau { background-color: #e3f2fd; color: #1565c0; }
        .status-EnCours { background-color: #fff3e0; color: #ef6c00; }
        .status-Traitée { background-color: #e8f5e9; color: #2e7d32; }
        .status-Rejetée { background-color: #ffebee; color: #c62828; }
        .action-btn { background-color: #2196f3; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 13px; transition: background-color 0.3s; }
        .action-btn:hover { background-color: #1976d2; }

        /* Messages */
        .error-message { background-color: #f8d7da; color: var(--color-error); padding: 15px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #f5c6cb; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .info-message { background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #bee5eb; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php 
        $pageTitle = 'Liste des Réponses Administratives';
        include 'includes/topbar.php'; 
        ?>
        
        <div class="container">
            <div class="content-card">
                <a href="BackofficeReclamations.php" class="back-link">
                    <span style="font-size: 18px;">←</span> Retour à la liste des réclamations
                </a>
    
    <h2><span style="font-size: 32px;">✉️</span> Liste des Réponses Administratives</h2>
    
    <?php echo $message; ?>

    <?php if (!empty($responses)): ?>
        <table class="response-table">
            <thead>
                <tr>
                    <th>ID Réponse</th>
                    <th>ID Réclamation</th>
                    <th>Statut</th>
                    <th>Date de Réponse</th>
                    <th>Extrait de la Réponse</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($responses as $response): 
                    // Assurez-vous que les clés correspondent aux colonnes de votre table adminreponse
                    $id_reponse = htmlspecialchars($response['id_reponse'] ?? 'N/A');
                    $id_reclamation = htmlspecialchars($response['id'] ?? 'N/A');
                    $statut = htmlspecialchars($response['statut'] ?? 'Nouveau');
                    $date_creation = isset($response['date_creation']) ? date('d/m/Y H:i', strtotime($response['date_creation'])) : 'N/A';
                    $reponse_extrait = htmlspecialchars(substr($response['reponse'] ?? '', 0, 50)) . (strlen($response['reponse'] ?? '') > 50 ? '...' : '');
                ?>
                <tr>
                    <td><?php echo $id_reponse; ?></td>
                    <td><?php echo $id_reclamation; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo str_replace(' ', '', $statut); ?>">
                            <?php echo $statut; ?>
                        </span>
                    </td>
                    <td><?php echo $date_creation; ?></td>
                    <td><?php echo $reponse_extrait; ?></td>
                    <td>
                        <a href="Updatereponce.php?id=<?php echo $id_reclamation; ?>" class="action-btn">
                            Voir/Modifier
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>