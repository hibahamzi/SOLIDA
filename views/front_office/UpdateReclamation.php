<?php
// updateReclamation.php - Version corrigée

require_once '../../Controllers/ReclamationController.php';

// Initialisation des variables
$reclamationController = new ReclamationController();
$message_soumission = '';
$reclamation = null;
$id = null;

// Liste des gouvernorats et délégations
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// ------------------------------
// 1. CHARGEMENT DES DONNÉES EXISTANTES (GET)
// ------------------------------
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $reclamation = $reclamationController->getReclamationById($id);

    if (!$reclamation) {
        header('Location: ListReclamation.php');
        exit;
    }
} else {
    header('Location: ListReclamation.php');
    exit;
}

// Variables pour pré-remplir
$nom = $reclamation['nom'] ?? '';
$prenom = $reclamation['prenom'] ?? '';
$telephone = $reclamation['telephone'] ?? '';
$email = $reclamation['email'] ?? '';
$ville = $reclamation['ville'] ?? '';
$position_gps = $reclamation['position_gps'] ?? '';
$description = $reclamation['description_detaillee'] ?? '';
$gouvernorat_selectionne = $reclamation['gouvernorat'] ?? '';
$delegation_selectionnee = $reclamation['delegation'] ?? '';
$categorie_selectionnee = $reclamation['categorie'] ?? '';
$priorite_selectionnee = $reclamation['priorite'] ?? 'Normale';
$statut_actuel = $reclamation['statut'] ?? 'Nouveau';
$date_actuelle = $reclamation['date'] ?? date('Y-m-d H:i:s');

// ------------------------------
// 2. GESTION DE LA SOUMISSION (POST) - CORRIGÉE
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $position_gps = trim($_POST['position_gps'] ?? '');
    $description = trim($_POST['description_detaillee'] ?? '');
    $gouvernorat_selectionne = $_POST['gouvernorat'] ?? '';
    $delegation_selectionnee = $_POST['delegation'] ?? '';
    $categorie_selectionnee = $_POST['categorie'] ?? '';
    $priorite_selectionnee = $_POST['priorite'] ?? 'Normale';
    $statut_actuel = $_POST['statut'] ?? 'Nouveau';
    $date_actuelle = $_POST['date'] ?? date('Y-m-d H:i:s');
    
    // Validation basique
    $erreurs = [];
    if (empty($nom)) $erreurs[] = "Le nom est obligatoire";
    if (empty($prenom)) $erreurs[] = "Le prénom est obligatoire";
    if (empty($telephone)) $erreurs[] = "Le téléphone est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'email est invalide";
    if (empty($gouvernorat_selectionne)) $erreurs[] = "Le gouvernorat est obligatoire";
    if (empty($delegation_selectionnee)) $erreurs[] = "La délégation est obligatoire";
    if (empty($description)) $erreurs[] = "La description est obligatoire";
    
    if (empty($erreurs)) {
        try {
            // ✅ APPEL CORRECT AVEC 13 PARAMÈTRES
            $success = $reclamationController->updateReclamation(
                $id,                    // 1
                $nom,                   // 2
                $prenom,                // 3
                $telephone,             // 4
                $email,                 // 5
                $gouvernorat_selectionne, // 6
                $delegation_selectionnee, // 7
                $ville,                 // 8
                $position_gps,          // 9
                $description,           // 10
                $categorie_selectionnee, // 11
                $priorite_selectionnee, // 12
                $statut_actuel          // 13
            );

            if ($success) {
                header('Location: ListReclamation.php?success=updated&id=' . $id);
                exit;
            } else {
                $message_soumission = '<div class="error-message">❌ Erreur lors de la modification.</div>';
            }
        } catch (Exception $e) {
            $message_soumission = '<div class="error-message">❌ Erreur: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message_soumission = '<div class="error-message">❌ ' . implode('<br>', $erreurs) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>SOLIDA - Modifier une Réclamation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/event.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome-6.0.0/css/all.min.css">
    
    <style>
        /* Votre CSS existant reste inchangé */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --color-primary: #4CAF50;
            --color-light: #E8F5E9;
            --color-dark: #388E3C;
            --color-text: #333;
            --color-error: #F44336;
            --color-success: #4CAF50;
            --color-border: #BDBDBD;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--color-light);
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        .top-navbar {
            background: #343a40;
            padding: 8px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .top-nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .top-nav-left, .top-nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-nav-left a, .top-nav-right a {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .main-navbar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 48px;
            width: 100%;
            z-index: 999;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4CAF50;
            text-decoration: none;
        }

        .main-container {
            margin-top: 128px;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 128px);
        }

        .form-container { 
            width: 100%;
            max-width: 800px; 
            background: #fff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        h2 { 
            color: var(--color-primary); 
            font-size: 2rem; 
            margin-bottom: 30px; 
            text-align: center;
            font-weight: 600;
        }

        h3 {
            color: var(--color-dark); 
            font-size: 1.2rem; 
            margin-top: 30px; 
            margin-bottom: 15px; 
            padding-bottom: 5px;
            border-bottom: 2px solid var(--color-light);
            font-weight: 500;
        }

        .form-row { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 20px; 
        }
        .form-group { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            font-size: 0.95rem; 
            color: var(--color-text); 
        }
        .required::after { 
            content: " *"; 
            color: var(--color-error); 
        }

        input[type="text"], input[type="email"], textarea, select { 
            padding: 12px; 
            border: 1px solid var(--color-border); 
            border-radius: 6px; 
            width: 100%; 
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s; 
        }
        input:focus, select:focus, textarea:focus { 
            border-color: var(--color-primary); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2); 
        }
        
        .input-group-tel { display: flex; width: 100%; }
        .input-group-tel .prefix { 
            background-color: var(--color-light); 
            border: 1px solid var(--color-border); 
            border-right: none; 
            padding: 12px; 
            border-radius: 6px 0 0 6px; 
            display: flex; 
            align-items: center; 
            font-weight: bold; 
            color: var(--color-dark);
        }
        .input-group-tel input { border-radius: 0 6px 6px 0; }

        .input-group-gps { display: flex; gap: 5px; }
        .input-group-gps input { flex-grow: 1; }
        .input-group-gps button { 
            background-color: var(--color-primary); 
            color: white; 
            border: none; 
            padding: 12px 15px; 
            border-radius: 6px; 
            cursor: pointer; 
            white-space: nowrap; 
            font-weight: bold; 
            transition: background-color 0.3s;
        }
        .input-group-gps button:hover { background-color: var(--color-dark); }
        .input-group-gps small { font-size: 0.8rem; color: #6c757d; margin-top: 5px; }

        .priorite-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .priorite-btn { 
            flex: 1; text-align: center; padding: 12px; border-radius: 6px; 
            cursor: pointer; font-weight: bold; color: white; transition: all 0.3s; 
            border: 2px solid transparent;
            background-color: #A5D6A7;
            color: var(--color-dark);
        }
        .priorite-btn:hover {
            background-color: #81C784;
        }
        .priorite-btn.selected { 
            border-color: var(--color-primary); 
            background-color: var(--color-primary);
            color: white;
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.5);
        }

        .submit-btn {
            width: 100%; 
            background-color: var(--color-primary); 
            color: white; 
            padding: 15px 20px; 
            border: none; 
            border-radius: 6px; 
            font-size: 1.1rem; 
            font-weight: bold; 
            cursor: pointer; 
            margin-top: 30px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            transition: background-color 0.3s, transform 0.1s; 
        }
        .submit-btn:hover { 
            background-color: var(--color-dark); 
            transform: translateY(-1px);
        }

        .success-message { 
            background-color: #DCEDC8; 
            color: #33691E; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            border: 1px solid #8BC34A; 
        }
        .error-message { 
            background-color: #FFCDD2; 
            color: #B71C1C; 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            border: 1px solid #F44336; 
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) { 
            .top-navbar {
                display: none;
            }

            .main-navbar {
                top: 0;
            }

            .main-container {
                margin-top: 80px;
            }

            .form-container { 
                padding: 25px; 
            }
            
            .form-row { 
                flex-direction: column; 
                gap: 0; 
            } 
            
            .priorite-group { 
                flex-direction: column; 
            } 
            
            .input-group-gps { 
                flex-direction: column; 
            } 
            
            .input-group-gps button { 
                width: 100%; 
                margin-top: 5px; 
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- BANDE NOIRE EN HAUT -->
    <nav class="top-navbar">
        <div class="top-nav-container">
            <div class="top-nav-left">
                <a href="mailto:info@company.com">
                    <i class="fa fa-envelope"></i>info@company.com
                </a>
                <a href="tel:010-020-0340">
                    <i class="fa fa-phone"></i>010-020-0340
                </a>
            </div>
            <div class="top-nav-right">
                <a href="https://fb.com/templatemo" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://twitter.com/" target="_blank">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.linkedin.com/" target="_blank">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- NAVBAR PRINCIPALE -->
    <nav class="main-navbar">
        <div class="nav-container">
            <a href="index.html" class="logo">SOLIDA</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-container">
            <h2>Modifier la Réclamation #<?php echo $id; ?></h2>
            
            <?php 
            if (!empty($message_soumission)) {
                echo $message_soumission;
            }
            ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <input type="hidden" name="statut" value="<?php echo htmlspecialchars($statut_actuel); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date_actuelle); ?>">

                <h3>Informations Personnelles</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom" class="required">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom" class="required">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone" class="required">Téléphone</label>
                        <div class="input-group-tel">
                            <span class="prefix">+216</span>
                            <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" required pattern="[0-9]{8}" title="Doit contenir 8 chiffres">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>

                <h3>Localisation</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="gouvernorat" class="required">Gouvernorat</label>
                        <select id="gouvernorat" name="gouvernorat" required>
                            <option value="">-- Choisir un gouvernorat --</option>
                            <?php foreach (array_keys($data) as $gouvernorat): ?>
                                <option value="<?php echo htmlspecialchars($gouvernorat); ?>" <?php echo ($gouvernorat == $gouvernorat_selectionne) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($gouvernorat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="delegation" class="required">Délégation</label>
                        <select id="delegation" name="delegation" required>
                            <option value="">-- Choisir d'abord le gouvernorat --</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($ville); ?>" placeholder="Ex: El Battan">
                    </div>
                    <div class="form-group">
                        <label for="position_gps" class="required">Position GPS</label>
                        <div class="input-group-gps">
                            <input type="text" id="position_gps" name="position_gps" required
                                value="<?php echo htmlspecialchars($position_gps); ?>" placeholder="Latitude, Longitude ou Adresse">
                            <button type="button" onclick="getLocalisation()">
                                &#x1F4CD; Localiser 
                            </button>
                        </div>
                        <small>Cliquez sur "Localiser" pour détecter automatiquement votre position</small>
                    </div>
                </div>

                <h3>Détails de la Réclamation</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categorie" class="required">Catégorie</label>
                        <select id="categorie" name="categorie" required>
                            <option value="">-- Choisir une catégorie --</option>
                            <option value="Route" <?php echo ($categorie_selectionnee == 'Route') ? 'selected' : ''; ?>>Route</option>
                            <option value="Eclairage" <?php echo ($categorie_selectionnee == 'Eclairage') ? 'selected' : ''; ?>>Éclairage Public</option>
                            <option value="Proprete" <?php echo ($categorie_selectionnee == 'Proprete') ? 'selected' : ''; ?>>Propreté</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priorite" class="required">Priorité</label>
                        <div class="priorite-group" id="priorite-group">
                            <div class="priorite-btn priorite-faible <?php echo ($priorite_selectionnee == 'Faible') ? 'selected' : ''; ?>" data-value="Faible">
                                &#x2193; Faible
                            </div>
                            <div class="priorite-btn priorite-normale <?php echo ($priorite_selectionnee == 'Normale') ? 'selected' : ''; ?>" data-value="Normale">
                                &#x2192; Normale
                            </div>
                            <div class="priorite-btn priorite-urgente <?php echo ($priorite_selectionnee == 'Urgente') ? 'selected' : ''; ?>" data-value="Urgente">
                                &#x2191; Urgente
                            </div>
                        </div>
                        <input type="hidden" id="priorite_input" name="priorite" value="<?php echo htmlspecialchars($priorite_selectionnee); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description_detaillee" class="required">Description Détaillée</label>
                    <textarea id="description_detaillee" name="description_detaillee" rows="6" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <span>&#x2705;</span> Mettre à jour la Réclamation
                </button>
                
                <a href="ListeReclamation.php" class="back-link">← Retour à la liste des Réclamations</a>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
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
                        <li><a class="text-decoration-none" href="#">Home</a></li>
                        <li><a class="text-decoration-none" href="#">Événement</a></li>
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

    <script>
        const data = <?php echo json_encode($data); ?>;
        const gouvernoratSelect = document.getElementById('gouvernorat');
        const delegationSelect = document.getElementById('delegation');
        const initialDelegationValue = "<?php echo htmlspecialchars($delegation_selectionnee); ?>";
        const prioriteBtns = document.querySelectorAll('.priorite-btn');
        const prioriteInput = document.getElementById('priorite_input');

        // Gestion des délégations
        function updateDelegations(gouvernorat, selectedDelegation = '') {
            delegationSelect.innerHTML = '<option value="">-- Choisir une délégation --</option>';
            if (data[gouvernorat]) {
                data[gouvernorat].forEach(delegation => {
                    const option = document.createElement('option');
                    option.value = delegation;
                    option.textContent = delegation;
                    if (delegation === selectedDelegation) {
                        option.selected = true;
                    }
                    delegationSelect.appendChild(option);
                });
            }
        }

        gouvernoratSelect.addEventListener('change', function() {
            updateDelegations(this.value);
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', () => {
            const selectedGouvernorat = gouvernoratSelect.value;
            if (selectedGouvernorat) {
                updateDelegations(selectedGouvernorat, initialDelegationValue);
            }
        });

        // Gestion des priorités
        prioriteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                prioriteBtns.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                prioriteInput.value = this.getAttribute('data-value');
            });
        });

        // Géolocalisation
        function getLocalisation() {
            const gpsInput = document.getElementById('position_gps');
            const villeInput = document.getElementById('ville');

            gpsInput.value = 'Localisation en cours...';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        
                        const apiUrl = `https://api.allorigins.win/get?url=` +
                            encodeURIComponent(
                                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`
                            );

                        fetch(apiUrl)
                            .then(response => response.json())
                            .then(result => {
                                const data = JSON.parse(result.contents);
                                if (data.display_name) {
                                    gpsInput.value = data.display_name;
                                    const ville = data.address.city || data.address.town || data.address.village || "";
                                    villeInput.value = ville;
                                } else {
                                    gpsInput.value = `${lat}, ${lon} (Adresse non trouvée)`;
                                }
                            })
                            .catch(err => {
                                console.error("Erreur API :", err);
                                gpsInput.value = `${lat}, ${lon} (Erreur API)`;
                            });
                    },
                    () => {
                        gpsInput.value = "Erreur : Permission refusée";
                        alert("Autorisez la géolocalisation !");
                    }
                );
            } else {
                gpsInput.value = "Géolocalisation non supportée";
            }
        }
    </script>
</body>
</html>