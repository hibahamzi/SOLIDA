<?php
// addReclamation.php (Version Stylisée et Minimaliste)
require_once '../../Controllers/ReclamationController.php';

// Initialisation des variables pour pré-remplir (si échec de la soumission)
$nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : "";
$prenom = isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : "";
$telephone = isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : "";
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : "";
$ville = isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : "";
$position_gps = isset($_POST['position_gps']) ? htmlspecialchars($_POST['position_gps']) : "";
$description = isset($_POST['description_detaillee']) ? htmlspecialchars($_POST['description_detaillee']) : "";
$gouvernorat_selectionne = isset($_POST['gouvernorat']) ? $_POST['gouvernorat'] : '';
$delegation_selectionnee = isset($_POST['delegation']) ? $_POST['delegation'] : '';
$priorite_selectionnee = isset($_POST['priorite']) ? $_POST['priorite'] : 'Normale'; 
$message_soumission = ''; 
$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

// Liste des gouvernorats et délégations
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// Traitement de la Soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreurs = [];
    if (empty($_POST['nom'])) {
        $erreurs[] = "Le nom est obligatoire.";
    } elseif (strlen($_POST['nom']) < 3) {
        $erreurs[] = "Le nom doit contenir au moins 3 caractères.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $_POST['nom'])) {
        $erreurs[] = "Le nom ne doit contenir que des lettres.";
    }
    if (empty($_POST['prenom'])) {
        $erreurs[] = "Le prénom est obligatoire.";
    } elseif (strlen($_POST['prenom']) < 3) {
        $erreurs[] = "Le prénom doit contenir au moins 3 caractères.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $_POST['prenom'])) {
        $erreurs[] = "Le prénom ne doit contenir que des lettres.";
    }
    if (empty($_POST['telephone'])) {
        $erreurs[] = "Le téléphone est obligatoire.";
    } elseif (!preg_match('/^[0-9]+$/', $_POST['telephone'])) {
        $erreurs[] = "Le téléphone ne doit contenir que des chiffres.";
    }
    if (empty($_POST['email'])) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email est invalide (doit contenir @).";
    }
    if (empty($_POST['gouvernorat'])) $erreurs[] = "Le gouvernorat est obligatoire.";
    if (empty($_POST['delegation'])) $erreurs[] = "La délégation est obligatoire.";
    if (empty($_POST['description_detaillee'])) {
        $erreurs[] = "La description est obligatoire.";
    } elseif (strlen($_POST['description_detaillee']) < 10) {
        $erreurs[] = "La description doit contenir au moins 10 caractères.";
    }
    if (empty($_POST['position_gps'])) $erreurs[] = "La position GPS est obligatoire.";

    if (!empty($erreurs)) {
         $message_soumission = '<p class="error-message">❌ Erreur de validation :<br>' . implode('<br>', $erreurs) . '</p>';
    } else {
        $reclamation = [
            'nom'                   => $_POST['nom'] ?? '',
            'prenom'                => $_POST['prenom'] ?? '',
            'telephone'             => $_POST['telephone'] ?? '',
            'email'                 => $_POST['email'] ?? '',
            'gouvernorat'           => $_POST['gouvernorat'] ?? '',
            'delegation'            => $_POST['delegation'] ?? '',
            'ville'                 => $_POST['ville'] ?? '',
            'position_gps'          => $_POST['position_gps'] ?? '', 
            'description_detaillee' => $_POST['description_detaillee'] ?? '',
            'priorite'              => $_POST['priorite'] ?? 'Normale',
            'statut'                => 'Nouveau',
            'date'                  => date('Y-m-d H:i:s')
        ];

        $ReclamationController = new ReclamationController();
        $ReclamationController->addReclamation($reclamation);

        $email_redirect = urlencode($_POST['email'] ?? '');
        header('Location: mesReclamations.php?success=1&email=' . $email_redirect);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>SOLIDA - Déposer une Réclamation</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/event.css">
    
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Palette de Verts */
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

        /* BANDE NOIRE EN HAUT */
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

        .top-nav-left i, .top-nav-right i {
            margin-right: 5px;
        }

        /* NAVBAR PRINCIPALE */
        .main-navbar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 48px; /* Position sous la bande noire */
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

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
            margin: 0;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 8px 0;
        }

        .nav-menu a:hover {
            color: #4CAF50;
        }

        .sign-in-btn {
            background: #4CAF50;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .sign-in-btn:hover {
            background: #388E3C;
            color: white;
        }

        /* Conteneur principal avec marge pour les navbars fixes */
        .main-container {
            margin-top: 128px; /* 48px (bande noire) + 80px (navbar principale) */
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

        .form-section-title {
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
        
        /* Style pour les champs en erreur */
        .input-error { border-color: var(--color-error) !important; }
        .validation-error { 
            color: var(--color-error); 
            font-size: 0.85rem; 
            margin-top: 5px; 
        }

        /* Préfixe Téléphone */
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

        /* Bouton GPS */
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

        /* Priorité Group */
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

        /* Bouton de soumission */
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

        /* Messages */
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

        /* Menu mobile */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }

        /* Responsive */
        @media (max-width: 768px) { 
            .top-navbar {
                display: none; /* Cacher la bande noire sur mobile */
            }

            .main-navbar {
                top: 0; /* Navbar principale en haut sur mobile */
            }

            .main-container {
                margin-top: 80px; /* Ajustement pour mobile */
            }

            .mobile-menu-btn {
                display: block;
            }

            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 150%;
                background: white;
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                gap: 15px;
            }

            .nav-menu.active {
                display: flex;
            }

            .main-container {
                padding: 20px 15px;
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

        @media (max-width: 576px) {
            .main-container {
                margin-top: 70px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .nav-container {
                padding: 0 30px;
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

    <!-- Contenu Principal -->
    <div class="main-container">
        <div class="form-container">
            <h2><span>&#x270E;</span> Déposer une Réclamation</h2>
            
            <?php echo $message_soumission; ?>

            <form method="POST"> 
                
                <div class="form-section-title"><span>&#x1F464;</span> Informations Personnelles</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom" >Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo $nom; ?>" required pattern="[a-zA-Z]+" minlength="3" title="Le nom doit contenir au moins 3 lettres.">
                        
                    </div>
                    <div class="form-group">
                        <label for="prenom" class="required">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo $prenom; ?>" required pattern="[a-zA-Z]+" minlength="3" title="Le prénom doit contenir au moins 3 lettres.">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone" class="required">Téléphone</label>
                        <div class="input-group-tel">
                            <span class="prefix">+216</span>
                            <input type="text" id="telephone" name="telephone" value="<?php echo $telephone; ?>" required pattern="[0-9]+" title="Seuls les chiffres sont autorisés">
                         
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" required title="Doit être un email valide (contenir @)">
                    </div>
                </div>

                <div class="form-section-title"><span>&#x1F4CD;</span> Localisation</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="gouvernorat" class="required">Gouvernorat</label>
                        <select id="gouvernorat" name="gouvernorat" required>
                            <option value="">-- Choisir un gouvernorat --</option>
                            <?php
                            foreach (array_keys($data) as $gouvernorat) {
                                $selected = ($gouvernorat == $gouvernorat_selectionne) ? 'selected' : '';
                                echo "<option value='{$gouvernorat}' {$selected}>{$gouvernorat}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="delegation" class="required">Délégation</label>
                        <select id="delegation" name="delegation" required>
                            <option value="">-- Choisir d'abord le gouvernorat --</option>
                            <?php
                            if (isset($data[$gouvernorat_selectionne])) {
                                foreach ($data[$gouvernorat_selectionne] as $delegation) {
                                    $selected = ($delegation == $delegation_selectionnee) ? 'selected' : '';
                                    echo "<option value='{$delegation}' {$selected}>{$delegation}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo $ville; ?>" placeholder="Ex: El Battan">
                    </div>
                    <div class="form-group">
                        <label for="position_gps" class="required">Position GPS</label>
                        <div class="input-group-gps">
                            <input type="text" id="position_gps" name="position_gps" required
                                value="<?php echo $position_gps; ?>" placeholder="Latitude, Longitude ou Adresse">
                            <button type="button" onclick="getLocalisation()">
                                &#x1F4CD; Localiser 
                            </button>
                        </div>
                        <small>Cliquez sur "Localiser" pour détecter automatiquement votre position</small>
                    </div>
                </div>

                <div class="form-section-title"><span>&#x1F4DD;</span> Détails de la Réclamation</div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="categorie">Catégorie *</label>
                        <select id="categorie" name="categorie">
                            <option value="">-- Choisir une catégorie --</option>
                            <option value="Route">Route</option>
                            <option value="Eclairage">Éclairage Public</option>
                            <option value="Proprete">Propreté</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priorite">Priorité *</label>
                        <div class="priorite-group" id="priorite-group">
                            <div class="priorite-btn priorite-faible <?php echo ($priorite_selectionnee == 'Faible') ? 'selected' : ''; ?>" data-value="Faible">
                                &#x2193; Faible
                            </div>
                            <div class="priorite-btn priorite-normale <?php echo ($priorite_selectionnee == 'Normale') ? 'selected' : ''; ?>" data-value="Normale">
                                &#x2193; Normale
                            </div>
                            <div class="priorite-btn priorite-urgente <?php echo ($priorite_selectionnee == 'Urgente') ? 'selected' : ''; ?>" data-value="Urgente">
                                &#x2191; Urgente
                            </div>
                            <input type="hidden" id="priorite_input" name="priorite" value="<?php echo $priorite_selectionnee; ?>">
                        </div>
                    </div>
                </div>
                

                <div class="form-group">
                    <label for="description_detaillee" class="required">Description détaillée</label>
                    <textarea id="description_detaillee" name="description_detaillee" rows="6" placeholder="Décrivez votre réclamation en détail..." minlength="10" required><?php echo $description; ?></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <span>&#x27A1;</span> Envoyer la réclamation
                </button>
            </form>
        </div>
    </div>

    <script>
        // Menu mobile
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navMenu = document.querySelector('.nav-menu');

        mobileMenuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Script JS pour les dépendances Gouvernorat/Délégation et la Priorité
        const dataJs = <?php echo json_encode($data); ?>;
        const delegationSelect = document.getElementById('delegation');
        const gouvernoratSelect = document.getElementById('gouvernorat');
        const initialDelegationValue = "<?php echo $delegation_selectionnee; ?>";

        function updateDelegations(gouvernorat, initialValue = null) {
            delegationSelect.innerHTML = '<option value="">-- Choisir une délégation --</option>';
            if (gouvernorat && dataJs[gouvernorat]) {
                dataJs[gouvernorat].forEach(delegation => {
                    const option = document.createElement('option');
                    option.value = delegation;
                    option.textContent = delegation;
                    if (delegation === initialValue) {
                        option.selected = true;
                    }
                    delegationSelect.appendChild(option);
                });
            }
        }
        
        gouvernoratSelect.addEventListener('change', function() {
            updateDelegations(this.value);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const selectedGouvernorat = gouvernoratSelect.value;
            if (selectedGouvernorat) {
                updateDelegations(selectedGouvernorat, initialDelegationValue);
            }
        });

        const prioriteBtns = document.querySelectorAll('.priorite-btn');
        const prioriteInput = document.getElementById('priorite_input');

        prioriteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                prioriteBtns.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                prioriteInput.value = this.getAttribute('data-value');
            });
        });

        // Fonction de géolocalisation
        function getLocalisation() {
            const options = {
                enableHighAccuracy: true,
                timeout: 5000, // 5 secondes
                maximumAge: 0
            };
            const gpsInput = document.getElementById('position_gps');
            const villeInput = document.getElementById('ville');

gpsInput.value = 'Localisation en cours...';

	            if (navigator.geolocation) {
	                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const accuracy = position.coords.accuracy; // Récupérer la précision
                        
                       // Afficher la précision pour l'utilisateur (bel ktiba)
	                        gpsInput.value = `Localisation précise (${accuracy.toFixed(2)}m)...`;
	                        
	                        // Appel direct à Nominatim (plus fiable que le proxy)
	                        const apiUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
	
	                        fetch(apiUrl)
	                            .then(response => response.json())
	                            .then(data => {
	                                if (data.display_name) {
	                                    // Affichage "bel ktiba" : adresse complète uniquement (selon la demande)
	                                    gpsInput.value = data.display_name;
	                                    const ville = data.address.city || data.address.town || data.address.village || "";
	                                    villeInput.value = ville;
	                                } else {
	                                    // Affichage d'un message d'erreur clair si l'adresse n'est pas trouvée
	                                    gpsInput.value = "Adresse non trouvée. Veuillez la saisir manuellement.";
	                                }
	                            })
                            .catch(err => {
	                                console.error("Erreur de géocodage :", err);
	                                // Affichage d'un message d'erreur clair en cas d'échec de l'API
	                                gpsInput.value = "Erreur de géocodage. Veuillez la saisir manuellement.";
	                            });
                    },
                    (error) => {
                        let errorMessage = "Erreur de géolocalisation : ";
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += "Permission refusée par l'utilisateur.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += "Information de position non disponible.";
                                break;
                            case error.TIMEOUT:
                                errorMessage += "La requête a expiré.";
                                break;
                            case error.UNKNOWN_ERROR:
                                errorMessage += "Erreur inconnue.";
                                break;
                        }
                        gpsInput.value = errorMessage;
                        alert(errorMessage + " Veuillez autoriser la géolocalisation.");
                    },
                    options // Ajout des options pour la haute précision
                );
            } else {
                gpsInput.value = "Géolocalisation non supportée";
            }
        }
    </script>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
<!--
                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Products</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Luxury</a></li>
                        <li><a class="text-decoration-none" href="#">Sport Wear</a></li>
                        <li><a class="text-decoration-none" href="#">Men's Shoes</a></li>
                        <li><a class="text-decoration-none" href="#">Women's Shoes</a></li>
                        <li><a class="text-decoration-none" href="#">Popular Dress</a></li>
                        <li><a class="text-decoration-none" href="#">Gym Accessories</a></li>
                        <li><a class="text-decoration-none" href="#">Sport Shoes</a></li>
                    </ul>
                </div>
-->
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
    <!-- End Footer -->

</body>
</html>