<?php
require_once '../../config/config.php';
session_start();
require_once '../../controllers/ReclamationController.php'; 

// Créer une instance de ReclamationController
$reclamationController = new ReclamationController($pdo);

// Si l'utilisateur est admin, rediriger vers le dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../back_office/dashboard.php');
    exit();
}

// Récupérer les réclamations de l'utilisateur connecté uniquement
$reclamations = [];
if (isset($_SESSION['user_id'])) {
    $allReclamations = $reclamationController->getReclamation();
    // Filtrer pour ne montrer que les réclamations de l'utilisateur connecté
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
            $reclamations[] = $rec;
        }
    }
} else {
    // Si non connecté, rediriger vers la page de connexion
    header('Location: sign-in.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>SOLIDA - Liste des Réclamations</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Icônes et CSS de base -->
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <!-- event.css removed to prevent unwanted styles -->
    
    <!-- Styles spécifiques à la page -->
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Protect navbar from page styles - minimal overrides only */
        #templatemo_nav_top,
        .navbar.shadow,
        .navbar.navbar-expand-lg {
            position: relative !important;
            z-index: 1001 !important;
        }
        
        /* Don't override Bootstrap navbar classes - let navbar.php handle it */
        
        /* Ensure page starts immediately after navbar */
        body > .container-main {
            margin-top: 20px !important;
        }

        /* Main content container */
        .container-main {
            width: 100%;
            max-width: 1400px;
            margin: 40px auto;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 30px;
            box-sizing: border-box;
            border-radius: 8px;
        }
        
        /* Hide any unwanted content from CSS files */
        .banner,
        .hero,
        .carousel,
        .slider,
        .featured-products,
        .category-section {
            display: none !important;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .add-container {
            text-align: right;
            margin-bottom: 20px; 
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 60px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .add-btn:hover {
            background-color: #45a049;
        }
        
        .table-responsive-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 12px 15px; 
            text-align: center;
            border: 1px solid #ddd;
            white-space: normal; 
            word-wrap: break-word;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        td a {
            padding: 6px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin: 0 2px;
            display: inline-block;
        }
        td:last-child {
            white-space: nowrap; 
        }
        .update-btn { background-color: #4CAF50; }
        .update-btn:hover { background-color: #45a049; }
        .delete-btn { background-color: #f44336; }
        .delete-btn:hover { background-color: #e53935; }

        @media (max-width: 768px) {
            .container-main {
                padding: 15px;
                margin: 20px 10px;
            }
            th, td {
                font-size: 12px;
                padding: 8px 4px; 
            }
            .add-container {
                text-align: center;
            }
            .add-btn {
                display: block;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <!-- Contenu principal de la page -->
    <div class="container-main">
        <h1>Liste des réclamations</h1>

        <div class="add-container">
            <a href="index.php?section=reclamations" class="add-btn">← Retour</a>
            <a href="AddReclamation.php" class="add-btn">+ Ajouter une réclamation</a>
        </div>

        <div class="table-responsive-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Gouvernorat</th>
                        <th>Délégation</th>
                        <th>Ville</th>
                        <th>Position GPS</th>
                        <th>Description détaillée</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reclamations)): ?>
                        <tr>
                            <td colspan="14" style="text-align: center; padding: 40px; color: #666;">
                                <p style="font-size: 18px; margin-bottom: 10px;">Aucune réclamation trouvée</p>
                                <a href="AddReclamation.php" class="add-btn" style="display: inline-block; margin-top: 10px;">+ Ajouter votre première réclamation</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reclamations as $reclamation ): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reclamation['id']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['nom']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['telephone']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['email']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['gouvernorat']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['delegation']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['ville']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['position_gps']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['description_detaillee']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['priorite']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['statut']); ?></td>
                                <td><?php echo htmlspecialchars($reclamation['date']); ?></td>
                                <td>
                                    <a href="UpdateReclamation.php?id=<?php echo urlencode($reclamation['id']); ?>" class="update-btn">Modifier</a>
                                    <a href="suppreclamation.php?id=<?php echo urlencode($reclamation['id']); ?>" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
