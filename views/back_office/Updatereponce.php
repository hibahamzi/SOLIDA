
<?php
// CORRECTION DU CHEMIN - remonter de 2 niveaux
require_once __DIR__ . '/../../Controllers/ReclamationController.php';

// Initialisation des variables
$message_soumission = '';
$reclamation = null;
$nom = '';
$prenom = '';
$telephone = '';
$email = '';
$ville = '';
$position_gps = '';
$description = '';
$gouvernorat_selectionne = '';
$delegation_selectionnee = '';
$date_localisation = '';
$priorite_selectionnee = 'Normale';
$statut = 'Nouveau';

// Récupération de l'ID de la réclamation à modifier
$id_reclamation = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_reclamation <= 0) {
    die("ID de réclamation invalide");
}

// Chargement des données de la réclamation
try {
    $ReclamationController = new ReclamationController();
    $reclamation = $ReclamationController->getReclamationById($id_reclamation);
    
    if (!$reclamation) {
        die("Réclamation non trouvée");
    }
} catch (Exception $e) {
    die("Erreur lors du chargement de la réclamation: " . $e->getMessage());
}

// Pré-remplissage des variables avec les données de la réclamation
$nom = htmlspecialchars($reclamation['nom'] ?? '');
$prenom = htmlspecialchars($reclamation['prenom'] ?? '');
$telephone = htmlspecialchars($reclamation['telephone'] ?? '');
$email = htmlspecialchars($reclamation['email'] ?? '');
$ville = htmlspecialchars($reclamation['ville'] ?? '');
$position_gps = htmlspecialchars($reclamation['position_gps'] ?? '');
$description = htmlspecialchars($reclamation['description_detaillee'] ?? '');
$gouvernorat_selectionne = $reclamation['gouvernorat'] ?? '';
$delegation_selectionnee = $reclamation['delegation'] ?? '';
$date_localisation = $reclamation['date_localisation'] ?? '';
$priorite_selectionnee = $reclamation['priorite'] ?? 'Normale';
$statut = $reclamation['statut'] ?? 'Nouveau';

// Liste des gouvernorats et délégations (conservée de l'original)
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
    'Nabeul' => ['Nabeul Ville', 'Hammamet', 'Kélibia', 'Korba'],
    'Sousse' => ['Sousse Ville', 'Msaken', 'Kalaa Kebira', 'Akouda'],
    'Monastir' => ['Monastir Ville', 'Moknine', 'Bembla', 'Khniss'],
    'Mahdia' => ['Mahdia Ville', 'Rejiche', 'Bou Merdes', 'Chebba'],
    'Kairouan' => ['Kairouan Ville', 'Haffouz', 'Sbikha', 'Chebika'],
    'Bizerte' => ['Bizerte Ville', 'Mateur', 'Sejnane', 'Ghar El Melh']
];

// Traitement de la Soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreurs = [];
    
    // Récupération des données POST et mise à jour des variables pour pré-remplissage en cas d'erreur
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $position_gps = $_POST['position_gps'] ?? '';
    $description = $_POST['description_detaillee'] ?? '';
    $gouvernorat_selectionne = $_POST['gouvernorat'] ?? '';
    $delegation_selectionnee = $_POST['delegation'] ?? '';
    $date_localisation = $_POST['date_localisation'] ?? date('Y-m-d');
    $priorite_selectionnee = $_POST['priorite'] ?? 'Normale';
    
    // Validation (conservée de l'original)
    if (empty($nom)) { $erreurs[] = "Le nom est obligatoire."; } elseif (strlen($nom) < 2) { $erreurs[] = "Le nom doit contenir au moins 2 caractères."; } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $nom)) { $erreurs[] = "Le nom ne doit contenir que des lettres, espaces et tirets."; }
    if (empty($prenom)) { $erreurs[] = "Le prénom est obligatoire."; } elseif (strlen($prenom) < 2) { $erreurs[] = "Le prénom doit contenir au moins 2 caractères."; } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $prenom)) { $erreurs[] = "Le prénom ne doit contenir que des lettres, espaces et tirets."; }
    if (empty($telephone)) { $erreurs[] = "Le téléphone est obligatoire."; } elseif (!preg_match('/^[0-9]+$/', $telephone)) { $erreurs[] = "Le téléphone ne doit contenir que des chiffres."; } elseif (strlen($telephone) < 8) { $erreurs[] = "Le téléphone doit contenir au moins 8 chiffres."; } elseif (strlen($telephone) > 15) { $erreurs[] = "Le téléphone ne doit pas dépasser 15 chiffres."; }
    if (empty($email)) { $erreurs[] = "L'email est obligatoire."; } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $erreurs[] = "L'email est invalide."; }
    
    if (empty($date_localisation)) { $erreurs[] = "La date de localisation est obligatoire."; } else {
        $date_saisie = $date_localisation;
        $date_actuelle = date('Y-m-d');
        $date_min = date('Y-m-d', strtotime('-1 year'));
        if ($date_saisie > $date_actuelle) { $erreurs[] = "La date de localisation ne peut pas être dans le futur."; } elseif ($date_saisie < $date_min) { $erreurs[] = "La date de localisation ne peut pas être antérieure à un an."; }
    }
    
    if (empty($gouvernorat_selectionne)) { $erreurs[] = "Le gouvernorat est obligatoire."; }
    if (empty($delegation_selectionnee)) { $erreurs[] = "La délégation est obligatoire."; }
    
    if (empty($description)) { $erreurs[] = "La description est obligatoire."; } elseif (strlen($description) < 10) { $erreurs[] = "La description doit contenir au moins 10 caractères."; } elseif (strlen($description) > 1000) { $erreurs[] = "La description ne doit pas dépasser 1000 caractères."; }
    
    if (empty($position_gps)) { $erreurs[] = "L'adresse complète est obligatoire."; } else {
        $position_gps_trim = trim($position_gps);
        if (strlen($position_gps_trim) < 8) { $erreurs[] = "L'adresse doit contenir au moins 8 caractères."; }
        if (!preg_match('/[a-zA-ZÀ-ÿ]/u', $position_gps_trim)) { $erreurs[] = "L'adresse doit contenir des lettres."; }
    }

    if (!empty($erreurs)) {
        $message_soumission = '<div class="error-message">';
        $message_soumission .= '<strong>❌ Erreur de validation :</strong><br>';
        foreach ($erreurs as $erreur) {
            $message_soumission .= '• ' . $erreur . '<br>';
        }
        $message_soumission .= '</div>';
    } else {
        try {
          $reclamation_update = [
            'id' => $id_reclamation,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'email' => $email,
            'date_localisation' => $date_localisation,
            'gouvernorat' => $gouvernorat_selectionne,
            'delegation' => $delegation_selectionnee,
            'ville' => $ville,
            'position_gps' => $position_gps,
            'description_detaillee' => $description,
            'priorite' => $priorite_selectionnee,
            'statut' => $statut, // Garder le statut existant
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        $ReclamationController = new ReclamationController();
        $result = $ReclamationController->updateReclamation($reclamation_update);

        if ($result) {
            // Redirection vers la liste avec message de succès
            header('Location: listeReclamation.php?success=update&email=' . urlencode($email));
            exit;
        }else {
            $message_soumission = '<div class="error-message">❌ Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.</div>';
        }
        } catch (Exception $e) {
            $message_soumission = '<div class="error-message">❌ Erreur système : ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>SOLIDA - Modifier la Réclamation #<?php echo $id_reclamation; ?></title>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Palette de Verts et Orange */
        :root {
            --color-primary: #4CAF50; /* Vert principal */
            --color-light: #E8F5E9; /* Fond très clair */
            --color-dark: #388E3C; /* Vert foncé */
            --color-text: #333;
            --color-error: #F44336;
            --color-success: #4CAF50;
            --color-border: #BDBDBD;
            --color-warning: #FF9800;
            --color-normal: #FF9800; /* Orange pour Normal */
            --color-normal-dark: #F57C00;
            --color-faible: #A5D6A7; /* Vert clair pour Faible */
            --color-urgente: #A5D6A7; /* Vert clair pour Urgente */
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--color-light);
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        .main-container {
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .form-container { 
            width: 100%;
            max-width: 700px; /* Légèrement plus étroit */
            background: #fff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        h2 { 
            color: var(--color-primary); 
            font-size: 1.8rem; 
            margin-bottom: 30px; 
            text-align: center;
            font-weight: 600;
        }

        /* NOUVEAU STYLE pour les titres de section (avec icône) */
        .form-section-title {
            color: var(--color-primary); 
            font-size: 1.1rem; 
            margin-top: 25px; 
            margin-bottom: 15px; 
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .form-section-title i {
            margin-right: 10px;
            font-size: 1.3rem;
        }

        .form-row { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 15px; /* Réduit l'espace */
        }
        .form-group { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }
        
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            font-size: 0.9rem; 
            color: var(--color-text); 
        }
        .required::after { 
            content: "*"; 
            color: var(--color-error); 
            margin-left: 2px;
        }

        input[type="text"], input[type="email"], input[type="date"], textarea, select { 
            padding: 10px; 
            border: 1px solid #E0E0E0; /* Bordure plus claire */
            border-radius: 6px; 
            width: 100%; 
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s; 
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { 
            border-color: var(--color-primary); 
            outline: none; 
            box-shadow: 0 0 0 1px var(--color-primary); 
        }
        
        /* Placeholder styling */
        input::placeholder, textarea::placeholder {
            color: #BDBDBD;
            font-size: 0.9rem;
        }

        /* NOUVELLE STRUCTURE pour le téléphone */
        .input-group-tel { 
            display: flex; 
            width: 100%; 
            border: 1px solid #E0E0E0; 
            border-radius: 6px; 
            overflow: hidden;
        }
        .input-group-tel .prefix { 
            background-color: #F5F5F5; /* Fond gris clair */
            border-right: 1px solid #E0E0E0; 
            padding: 10px; 
            display: flex; 
            align-items: center; 
            font-weight: bold; 
            color: var(--color-primary); /* Vert pour le +216 */
        }
        .input-group-tel input { 
            border: none; 
            padding: 10px;
            border-radius: 0; 
        }
        .input-group-tel:focus-within {
            border-color: var(--color-primary); 
            box-shadow: 0 0 0 1px var(--color-primary); 
        }

        /* Bouton GPS */
        .input-group-gps { display: flex; gap: 5px; }
        .input-group-gps input { flex-grow: 1; }
        .input-group-gps button { 
            background-color: var(--color-primary); 
            color: white; 
            border: none; 
            padding: 10px 15px; 
            border-radius: 6px; 
            cursor: pointer; 
            white-space: nowrap; 
            font-weight: bold; 
            transition: background-color 0.3s;
        }
        .input-group-gps button:hover { background-color: var(--color-dark); }
        .input-group-gps small { 
            font-size: 0.8rem; 
            color: #6c757d; 
            margin-top: 5px; 
            display: block;
        }

        /* NOUVEAU STYLE pour Priorité Group (avec Orange) */
        .priorite-group { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .priorite-btn { 
            flex: 1; 
            text-align: center; 
            padding: 10px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            transition: all 0.3s; 
            border: 1px solid transparent;
            color: white;
        }
        
        /* Styles spécifiques aux priorités */
        .priorite-faible { background-color: var(--color-faible); }
        .priorite-normale { background-color: var(--color-normal); }
        .priorite-urgente { background-color: var(--color-urgente); }

        /* Style sélectionné */
        .priorite-btn.selected { 
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1); /* Ombre pour la sélection */
            transform: translateY(-1px);
        }
        .priorite-faible.selected { border-color: var(--color-primary); }
        .priorite-normale.selected { border-color: var(--color-normal-dark); }
        .priorite-urgente.selected { border-color: var(--color-primary); }
        
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

        /* Date picker styling */
        input[type="date"] {
            position: relative;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
        }

        /* Responsive */
        @media (max-width: 768px) { 
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
                flex-direction: row; 
                flex-wrap: wrap;
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
    <!-- Contenu Principal -->
    <div class="main-container">
        <div class="form-container">
            <!-- Titre mis à jour pour correspondre à l'image -->
            <h2><i class="fas fa-pencil-alt"></i> Modifier la Réclamation #<?php echo $id_reclamation; ?></h2>
            
            <?php echo $message_soumission; ?>

            <form method="POST" id="reclamationForm"> 
                
                <div class="form-section">
                    <!-- Titre de section mis à jour pour correspondre à l'image -->
                    <div class="form-section-title"><i class="fas fa-user"></i> Informations Personnelles</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom" class="required">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" 
                                   placeholder="Votre nom" required
                                   minlength="2" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label for="prenom" class="required">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" 
                                   placeholder="Votre prénom" required
                                   minlength="2" maxlength="50">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <div class="input-group-tel">
                                <span class="prefix">+216</span>
                                <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" 
                                       placeholder="12345678" required 
                                       pattern="[0-9]+" title="Seuls les chiffres sont autorisés"
                                       minlength="8" maxlength="15">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                                   placeholder="votre@email.com" required
                                   maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <!-- Titre de section mis à jour pour correspondre à l'image -->
                    <div class="form-section-title"><i class="fas fa-map-marker-alt"></i> Localisation et Date</div>
                    
                    <!-- Ligne 1: Gouvernorat et Délégation -->
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
                                // Pré-remplissage des délégations si un gouvernorat est déjà sélectionné
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
                    
                    <!-- Ligne 2: Date de Localisation et Ville/Quartier -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_localisation" class="required">Date de Localisation</label>
                            <input type="date" id="date_localisation" name="date_localisation" 
                                   value="<?php echo htmlspecialchars($date_localisation); ?>" 
                                   min="<?php echo date('Y-m-d', strtotime('-1 year')); ?>"
                                   max="<?php echo date('Y-m-d'); ?>" required>
                            <small style="color: #6c757d; margin-top: 5px;">
                                Date à laquelle vous avez constaté le problème
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville/Quartier</label>
                            <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($ville); ?>" 
                                   placeholder="Ex: El Battan, Cité Olympique" maxlength="50">
                        </div>
                    </div>
                    
                    <!-- Ligne 3: Adresse Complète (Position GPS) -->
                    <div class="form-group">
                        <label for="position_gps" class="required">Adresse Complète</label>
                        <div class="input-group-gps">
                            <input type="text" id="position_gps" name="position_gps" required
                                value="<?php echo htmlspecialchars($position_gps); ?>" 
                                placeholder="Ex: 12 Rue Habib Bourguiba, Tunis"
                                minlength="8" maxlength="200">
                            <button type="button" id="gpsButton" onclick="getLocalisationPrecise()">
                                <i class="fas fa-map-marker-alt"></i> Localiser
                            </button>
                        </div>
                        <small>Cliquez sur "Localiser" pour détecter automatiquement votre position avec précision</small>
                    </div>
                </div>

                <div class="form-section">
                    <!-- Titre de section mis à jour pour correspondre à l'image -->
                    <div class="form-section-title"><i class="fas fa-clipboard-list"></i> Détails de la Réclamation</div>

                    <!-- Priorité -->
                    <div class="form-group">
                        <label class="required">Priorité</label>
                        <div class="priorite-group">
                            <div class="priorite-btn priorite-faible <?php echo ($priorite_selectionnee == 'Faible') ? 'selected' : ''; ?>" data-value="Faible">
                                <i class="fas fa-arrow-down"></i> Faible
                            </div>
                            <div class="priorite-btn priorite-normale <?php echo ($priorite_selectionnee == 'Normale') ? 'selected' : ''; ?>" data-value="Normale">
                                <i class="fas fa-arrow-right"></i> Normale
                            </div>
                            <div class="priorite-btn priorite-urgente <?php echo ($priorite_selectionnee == 'Urgente') ? 'selected' : ''; ?>" data-value="Urgente">
                                <i class="fas fa-arrow-up"></i> Urgente
                            </div>
                            <input type="hidden" id="priorite_input" name="priorite" value="<?php echo htmlspecialchars($priorite_selectionnee); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description_detaillee" class="required">Description détaillée</label>
                        <textarea id="description_detaillee" name="description_detaillee" rows="6" 
                                  placeholder="Décrivez votre réclamation en détail (nature du problème, observations, impacts...)" required
                                  minlength="10" maxlength="1000"><?php echo htmlspecialchars($description); ?></textarea>
                        <small id="description-counter" style="text-align: right; display: block; color: #6c757d; margin-top: 5px;">
                            <span id="description-length"><?php echo strlen($description); ?></span>/1000 caractères
                        </small>
                    </div>
                </div>

                <!-- Texte du bouton mis à jour -->
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check"></i> Mettre à jour la réclamation
                </button>
                <div style="text-align: center; margin-top: 15px;">
                <a href="listeReclamation.php" style="color: var(--color-primary); text-decoration: none;">
                 &#x2190; Retour à la liste des Réclamations
                </a>
            </div>
            </form>
        </div>
    </div>

    <script>
        // Données pour les gouvernorats et délégations
        const dataJs = <?php echo json_encode($data); ?>;
        const delegationSelect = document.getElementById('delegation');
        const gouvernoratSelect = document.getElementById('gouvernorat');

        // Mise à jour des délégations en fonction du gouvernorat
        function updateDelegations(gouvernorat) {
            if (!delegationSelect) return;
            
            delegationSelect.innerHTML = '<option value="">-- Choisir une délégation --</option>';
            if (gouvernorat && dataJs[gouvernorat]) {
                dataJs[gouvernorat].forEach(delegation => {
                    const option = document.createElement('option');
                    option.value = delegation;
                    option.textContent = delegation;
                    // Sélectionner l'option si elle correspond à la délégation sélectionnée
                    if (delegation === '<?php echo $delegation_selectionnee; ?>') {
                        option.selected = true;
                    }
                    delegationSelect.appendChild(option);
                });
            }
        }
        
        if (gouvernoratSelect) {
            gouvernoratSelect.addEventListener('change', function() {
                updateDelegations(this.value);
            });
            // Appel initial pour charger les délégations si un gouvernorat est déjà sélectionné (chargement initial)
            updateDelegations(gouvernoratSelect.value);
        }

        // Gestion des boutons de priorité
        const prioriteBtns = document.querySelectorAll('.priorite-btn');
        const prioriteInput = document.getElementById('priorite_input');

        if (prioriteBtns && prioriteInput) {
            prioriteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    prioriteBtns.forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    prioriteInput.value = this.getAttribute('data-value');
                });
            });
        }

        // Compteur de caractères pour la description
        const descriptionTextarea = document.getElementById('description_detaillee');
        const descriptionCounter = document.getElementById('description-length');
        
        if (descriptionTextarea && descriptionCounter) {
            descriptionTextarea.addEventListener('input', function() {
                const length = this.value.length;
                descriptionCounter.textContent = length;
                
                // Changer la couleur si approche de la limite
                if (length > 900) {
                    descriptionCounter.style.color = '#f44336';
                } else if (length > 800) {
                    descriptionCounter.style.color = '#ff9800';
                } else {
                    descriptionCounter.style.color = '#6c757d';
                }
            });
        }
        
        // Géolocalisation PRÉCISE avec plusieurs services (conservée de l'original)
        function getLocalisationPrecise() {
            const gpsInput = document.getElementById('position_gps');
            const gpsButton = document.getElementById('gpsButton');
            
            if (!navigator.geolocation) {
                alert("Votre navigateur ne supporte pas la géolocalisation");
                return;
            }

            gpsButton.disabled = true;
            gpsButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';

            // Options pour une géolocalisation plus précise
            const options = {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 60000
            };

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    
                    let address = '';
                    
                    // Essayer d'abord avec Nominatim (OpenStreetMap)
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`);
                        const data = await response.json();
                        
                        if (data && data.display_name) {
                            address = data.display_name;
                        }
                    } catch (error) {
                        // Fallback
                    }
                    
                    // Si aucun service ne fonctionne, utiliser les coordonnées avec indication de précision
                    if (!address || address.includes('Unnamed')) {
                        address = `Position GPS: ${lat.toFixed(6)}, ${lon.toFixed(6)} (Précision: ${Math.round(accuracy)}m)`;
                    }
                    
                    gpsInput.value = address;
                    
                    gpsButton.disabled = false;
                    gpsButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Localiser';
                    
                    // Afficher un message de confirmation
                    if (accuracy < 50) {
                        showMessage('✅ Localisation précise obtenue!', 'success');
                    } else if (accuracy < 200) {
                        showMessage('⚠️ Localisation moyennement précise', 'warning');
                    } else {
                        showMessage('📍 Localisation approximative - vérifiez l\'adresse', 'warning');
                    }
                },
                (error) => {
                    let message = "Erreur de localisation: ";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message = "❌ Permission refusée. Veuillez autoriser la localisation dans les paramètres de votre navigateur.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = "❌ Position indisponible. Vérifiez votre connexion internet.";
                            break;
                        case error.TIMEOUT:
                            message = "⏰ Temps de localisation écoulé. Veuillez réessayer.";
                            break;
                        default:
                            message = `❌ Erreur inconnue (${error.code}).`;
                            break;
                    }
                    showMessage(message, 'error');
                    
                    gpsButton.disabled = false;
                    gpsButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Localiser';
                },
                options
            );
        }

        // Fonction pour afficher des messages (utilisée par la géolocalisation)
        function showMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = (type === 'success' ? 'success-message' : 'error-message');
            messageDiv.textContent = text;
            
            const formContainer = document.querySelector('.form-container');
            formContainer.insertBefore(messageDiv, formContainer.querySelector('form'));
            
            // Supprimer le message après 5 secondes
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
    </script>
</body>
</html>