<?php
require_once '../../Controllers/ReclamationController.php'; 

// Créer une instance de ReclamationController
$reclamationController = new ReclamationController();

// Récupérer toutes les réclamations
$reclamations = $reclamationController->getReclamation();
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
    <link rel="stylesheet" href="assets/css/event.css">
    
    <!-- Styles spécifiques à la page -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
        }

        /* --- MODIFICATION POUR LA LARGEUR COMPLÈTE --- */
        .container-main {
            width: 100%; /* Occupe toute la largeur */
            margin-top: 50px; /* Garde une marge en haut */
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px; /* Espace intérieur pour que le contenu ne colle pas aux bords */
            box-sizing: border-box; /* S'assure que le padding est inclus dans la largeur */
        }
        /* --- FIN DE LA MODIFICATION --- */

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
                padding: 10px;
                margin-top: 20px;
            }
            th, td {
                font-size: 12px;
                padding: 8px 4px; 
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
            <a class="navbar-brand text-success logo h1 align-self-center" href="index.html">
                SOLIDA
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="align-self-center collapse navbar-collapse flex-fill d-lg-flex justify-content-lg-between" id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="evenement.php">Événement</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Dons</a></li>
                        <li class="nav-item"><a class="nav-link" href="ListeReclamation.php">Reclamation</a></li>
                        <li class="nav-item"><a class="nav-link" href="sign-in.php">Sign in</a></li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <div class="d-lg-none flex-sm-fill mt-3 mb-4 col-7 col-sm-auto pr-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputMobileSearch" placeholder="Search ...">
                            <div class="input-group-text"><i class="fa fa-fw fa-search"></i></div>
                        </div>
                    </div>
                    <a class="nav-icon d-none d-lg-inline" href="#" data-bs-toggle="modal" data-bs-target="#templatemo_search">
                        <i class="fa fa-fw fa-search text-dark mr-2"></i>
                    </a>
                    <a class="nav-icon position-relative text-decoration-none" href="sign-up.php">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                        <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark"></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Header -->

    <!-- Contenu principal de la page -->
    <div class="container-main">
        <h1>Liste des réclamations</h1>

        <div class="add-container">
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
                                <a href="Suppreclamation.php?id=<?php echo urlencode($reclamation['id']); ?>" class="delete-btn">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
