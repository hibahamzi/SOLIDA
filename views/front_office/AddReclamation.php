<?php
// addReclamation.php (Version avec validation en temps réel)
require_once '../../Controllers/ReclamationController.php';

// Initialisation des variables
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

// Tableau pour stocker les erreurs de chaque champ
$field_errors = [
    'nom' => '',
    'prenom' => '',
    'telephone' => '',
    'email' => '',
    'gouvernorat' => '',
    'delegation' => '',
    'position_gps' => '',
    'description_detaillee' => '',
    'priorite' => ''
];

// Liste des gouvernorats et délégations
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// ------------------------------
// 🚀 Traitement de la Soumission
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs
    $has_errors = false;
    
    // Validation du nom
    if (empty($_POST['nom'])) {
        $field_errors['nom'] = "Le nom est obligatoire";
        $has_errors = true;
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $_POST['nom'])) {
        $field_errors['nom'] = "Le nom ne doit contenir que des lettres, espaces et tirets";
        $has_errors = true;
    } elseif (strlen($_POST['nom']) < 2) {
        $field_errors['nom'] = "Le nom doit contenir au moins 2 caractères";
        $has_errors = true;
    }
    
    // Validation du prénom
    if (empty($_POST['prenom'])) {
        $field_errors['prenom'] = "Le prénom est obligatoire";
        $has_errors = true;
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $_POST['prenom'])) {
        $field_errors['prenom'] = "Le prénom ne doit contenir que des lettres, espaces et tirets";
        $has_errors = true;
    } elseif (strlen($_POST['prenom']) < 2) {
        $field_errors['prenom'] = "Le prénom doit contenir au moins 2 caractères";
        $has_errors = true;
    }
    
    // Validation du téléphone
    if (empty($_POST['telephone'])) {
        $field_errors['telephone'] = "Le téléphone est obligatoire";
        $has_errors = true;
    } elseif (!preg_match('/^[0-9]{8}$/', $_POST['telephone'])) {
        $field_errors['telephone'] = "Le téléphone doit contenir exactement 8 chiffres";
        $has_errors = true;
    }
    
    // Validation de l'email
    if (empty($_POST['email'])) {
        $field_errors['email'] = "L'email est obligatoire";
        $has_errors = true;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $field_errors['email'] = "Format d'email invalide";
        $has_errors = true;
    }
    
    // Validation du gouvernorat
    if (empty($_POST['gouvernorat'])) {
        $field_errors['gouvernorat'] = "Le gouvernorat est obligatoire";
        $has_errors = true;
    }
    
    // Validation de la délégation
    if (empty($_POST['delegation'])) {
        $field_errors['delegation'] = "La délégation est obligatoire";
        $has_errors = true;
    }
    
    // Validation de la position GPS
    if (empty($_POST['position_gps'])) {
        $field_errors['position_gps'] = "La position GPS est obligatoire";
        $has_errors = true;
    } elseif (strlen($_POST['position_gps']) < 3) {
        $field_errors['position_gps'] = "Position GPS trop courte";
        $has_errors = true;
    }
    
    // Validation de la description
    if (empty($_POST['description_detaillee'])) {
        $field_errors['description_detaillee'] = "La description est obligatoire";
        $has_errors = true;
    } elseif (strlen($_POST['description_detaillee']) < 10) {
        $field_errors['description_detaillee'] = "La description doit contenir au moins 10 caractères";
        $has_errors = true;
    }
    
    // Validation de la priorité
    if (empty($_POST['priorite'])) {
        $field_errors['priorite'] = "La priorité est obligatoire";
        $has_errors = true;
    }
    
    if (!$has_errors) {
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

        // Appel du contrôleur pour l'ajout
        $ReclamationController = new ReclamationController();
        $ReclamationController->addReclamation($reclamation);

        // Redirection en cas de succès
        $email_redirect = urlencode($_POST['email'] ?? '');
        header('Location: listeReclamation.php?success=1&email=' . $email_redirect);
        exit;
    } else {
        $message_soumission = '<div class="error-message">❌ Veuillez corriger les erreurs ci-dessous.</div>';
        
        // Mettre à jour les variables avec les valeurs POST pour affichage
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        $ville = $_POST['ville'] ?? '';
        $position_gps = $_POST['position_gps'] ?? '';
        $description = $_POST['description_detaillee'] ?? '';
        $gouvernorat_selectionne = $_POST['gouvernorat'] ?? '';
        $delegation_selectionnee = $_POST['delegation'] ?? '';
        $priorite_selectionnee = $_POST['priorite'] ?? 'Normale';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer une Réclamation - Design Vert</title>
    <style>
        /* Palette de Verts */
        :root {
            --color-primary: #4CAF50; /* Vert principal */
            --color-light: #E8F5E9;   /* Vert très clair pour le fond */
            --color-dark: #388E3C;    /* Vert foncé pour les hover */
            --color-text: #333;
            --color-error: #F44336;   /* Rouge pour les erreurs */
            --color-success: #4CAF50;
            --color-border: #BDBDBD;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--color-light); 
            padding: 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
            margin-bottom: 10px; 
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
        
        /* Style des champs en erreur */
        .error-field {
            border-color: var(--color-error) !important;
            box-shadow: 0 0 0 2px rgba(244, 67, 54, 0.2) !important;
        }
        
        .error-message-field {
            color: var(--color-error);
            font-size: 0.85rem;
            margin-top: 5px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: fadeIn 0.3s ease;
        }
        
        .error-message-field i {
            font-size: 0.9rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        input:focus, select:focus, textarea:focus { 
            border-color: var(--color-primary); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2); 
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
        .priorite-group { display: flex; gap: 10px; margin-bottom: 10px; }
        .priorite-btn { 
            flex: 1; text-align: center; padding: 12px; border-radius: 6px; 
            cursor: pointer; font-weight: bold; color: white; transition: all 0.3s; 
            border: 2px solid transparent;
            background-color: #A5D6A7; /* Vert clair */
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
        
        .priorite-faible, .priorite-normale, .priorite-urgente {
            background-color: #A5D6A7;
            color: var(--color-dark);
        }
        .priorite-faible.selected, .priorite-normale.selected, .priorite-urgente.selected {
            background-color: var(--color-primary);
            color: white;
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
        .submit-btn span { margin-right: 10px; }
        
        .submit-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
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

        @media (max-width: 600px) { 
            .form-row { flex-direction: column; gap: 0; } 
            .priorite-group { flex-direction: column; } 
            .input-group-gps { flex-direction: column; } 
            .input-group-gps button { width: 100%; margin-top: 5px; } 
            .form-container { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2><span>&#x270E;</span> Déposer une Réclamation</h2>
    
    <?php echo $message_soumission; ?>

    <form method="POST" id="reclamationForm" onsubmit="return validateForm()"> 
        
        <h3><span>&#x1F464;</span> Informations Personnelles</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nom" class="required">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" 
                       class="<?php echo !empty($field_errors['nom']) ? 'error-field' : ''; ?>">
                <?php if (!empty($field_errors['nom'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['nom']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="prenom" class="required">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>"
                       class="<?php echo !empty($field_errors['prenom']) ? 'error-field' : ''; ?>">
                <?php if (!empty($field_errors['prenom'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['prenom']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telephone" class="required">Téléphone</label>
                <div class="input-group-tel">
                    <span class="prefix">+216</span>
                    <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone); ?>" 
                           class="<?php echo !empty($field_errors['telephone']) ? 'error-field' : ''; ?>">
                </div>
                <?php if (!empty($field_errors['telephone'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['telephone']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                       class="<?php echo !empty($field_errors['email']) ? 'error-field' : ''; ?>">
                <?php if (!empty($field_errors['email'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['email']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h3><span>&#x1F4CD;</span> Localisation</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="gouvernorat" class="required">Gouvernorat</label>
                <select id="gouvernorat" name="gouvernorat" 
                        class="<?php echo !empty($field_errors['gouvernorat']) ? 'error-field' : ''; ?>">
                    <option value="">-- Choisir un gouvernorat --</option>
                    <?php
                    foreach (array_keys($data) as $gouvernorat) {
                        $selected = ($gouvernorat == $gouvernorat_selectionne) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($gouvernorat) . "' {$selected}>" . htmlspecialchars($gouvernorat) . "</option>";
                    }
                    ?>
                </select>
                <?php if (!empty($field_errors['gouvernorat'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['gouvernorat']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="delegation" class="required">Délégation</label>
                <select id="delegation" name="delegation" 
                        class="<?php echo !empty($field_errors['delegation']) ? 'error-field' : ''; ?>">
                    <option value="">-- Choisir d'abord le gouvernorat --</option>
                    <?php
                    if (isset($data[$gouvernorat_selectionne])) {
                        foreach ($data[$gouvernorat_selectionne] as $delegation) {
                            $selected = ($delegation == $delegation_selectionnee) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($delegation) . "' {$selected}>" . htmlspecialchars($delegation) . "</option>";
                        }
                    }
                    ?>
                </select>
                <?php if (!empty($field_errors['delegation'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['delegation']); ?>
                    </div>
                <?php endif; ?>
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
                    <input type="text" id="position_gps" name="position_gps"
                           value="<?php echo htmlspecialchars($position_gps); ?>" 
                           class="<?php echo !empty($field_errors['position_gps']) ? 'error-field' : ''; ?>"
                           placeholder="Latitude, Longitude ou Adresse">
                    <button type="button" onclick="getLocalisation()">
                        &#x1F4CD; Localiser 
                    </button>
                </div>
                <?php if (!empty($field_errors['position_gps'])): ?>
                    <div class="error-message-field">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($field_errors['position_gps']); ?>
                    </div>
                <?php endif; ?>
                <small>Cliquez sur "Localiser" pour détecter automatiquement votre position</small>
            </div>
        </div>

        <h3><span>&#x1F4DD;</span> Détails de la Réclamation</h3>

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
            <?php if (!empty($field_errors['priorite'])): ?>
                <div class="error-message-field">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($field_errors['priorite']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description_detaillee" class="required">Description détaillée</label>
            <textarea id="description_detaillee" name="description_detaillee" rows="6" 
                      placeholder="Décrivez votre réclamation en détail..."
                      class="<?php echo !empty($field_errors['description_detaillee']) ? 'error-field' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
            <?php if (!empty($field_errors['description_detaillee'])): ?>
                <div class="error-message-field">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($field_errors['description_detaillee']); ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <span>&#x27A1;</span> Envoyer la réclamation
        </button>
    </form>
</div>

<script>
    // --- Script JS pour les dépendances Gouvernorat/Délégation et la Priorité ---
    const dataJs = <?php echo json_encode($data); ?>;
    const delegationSelect = document.getElementById('delegation');
    const gouvernoratSelect = document.getElementById('gouvernorat');
    const initialDelegationValue = "<?php echo htmlspecialchars($delegation_selectionnee); ?>";
    const prioriteBtns = document.querySelectorAll('.priorite-btn');
    const prioriteInput = document.getElementById('priorite_input');
    const submitBtn = document.getElementById('submitBtn');

    // Gestion des délégations
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
        validateField('gouvernorat', this);
    });

    // Initialisation
    document.addEventListener('DOMContentLoaded', () => {
        const selectedGouvernorat = gouvernoratSelect.value;
        if (selectedGouvernorat) {
            updateDelegations(selectedGouvernorat, initialDelegationValue);
        }
        
        // Ajouter les écouteurs d'événements pour la validation en temps réel
        setupRealTimeValidation();
    });

    // Gestion des priorités
    prioriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            prioriteBtns.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            prioriteInput.value = this.getAttribute('data-value');
            validatePriorite();
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
                            validateField('position_gps', gpsInput);
                        })
                        .catch(err => {
                            console.error("Erreur API :", err);
                            gpsInput.value = `${lat}, ${lon} (Erreur API)`;
                            validateField('position_gps', gpsInput);
                        });
                },
                () => {
                    gpsInput.value = "Erreur : Permission refusée";
                    alert("Autorisez la géolocalisation !");
                    validateField('position_gps', gpsInput);
                }
            );
        } else {
            gpsInput.value = "Géolocalisation non supportée";
            validateField('position_gps', gpsInput);
        }
    }

    // Validation en temps réel
    function setupRealTimeValidation() {
        const fields = {
            'nom': { min: 2, required: true, pattern: /^[a-zA-ZÀ-ÿ\s\-]+$/ },
            'prenom': { min: 2, required: true, pattern: /^[a-zA-ZÀ-ÿ\s\-]+$/ },
            'telephone': { pattern: /^[0-9]{8}$/, required: true },
            'email': { type: 'email', required: true },
            'gouvernorat': { required: true },
            'delegation': { required: true },
            'position_gps': { min: 3, required: true },
            'description_detaillee': { min: 10, required: true }
        };

        Object.keys(fields).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    validateField(fieldId, this);
                });
                field.addEventListener('blur', function() {
                    validateField(fieldId, this);
                });
                field.addEventListener('change', function() {
                    validateField(fieldId, this);
                });
            }
        });
    }

    // Validation d'un champ spécifique
    function validateField(fieldId, field) {
        const errorDiv = field.parentNode.querySelector('.error-message-field') || 
                       document.querySelector(`[data-field="${fieldId}"]`);
        
        let errorMessage = '';
        let isValid = true;
        const value = field.value.trim();
        
        switch(fieldId) {
            case 'nom':
            case 'prenom':
                if (!value) {
                    errorMessage = `Le ${fieldId} est obligatoire`;
                    isValid = false;
                } else if (value.length < 2) {
                    errorMessage = `Le ${fieldId} doit contenir au moins 2 caractères`;
                    isValid = false;
                } else if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(value)) {
                    errorMessage = `Le ${fieldId} ne doit contenir que des lettres, espaces et tirets`;
                    isValid = false;
                }
                break;
                
            case 'telephone':
                if (!value) {
                    errorMessage = "Le téléphone est obligatoire";
                    isValid = false;
                } else if (!/^[0-9]{8}$/.test(value)) {
                    errorMessage = "Doit contenir exactement 8 chiffres";
                    isValid = false;
                }
                break;
                
            case 'email':
                if (!value) {
                    errorMessage = "L'email est obligatoire";
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    errorMessage = "Format d'email invalide";
                    isValid = false;
                }
                break;
                
            case 'gouvernorat':
            case 'delegation':
                if (!value) {
                    errorMessage = `Le ${fieldId} est obligatoire`;
                    isValid = false;
                }
                break;
                
            case 'position_gps':
                if (!value) {
                    errorMessage = "La position GPS est obligatoire";
                    isValid = false;
                } else if (value.length < 3) {
                    errorMessage = "Position GPS trop courte";
                    isValid = false;
                }
                break;
                
            case 'description_detaillee':
                if (!value) {
                    errorMessage = "La description est obligatoire";
                    isValid = false;
                } else if (value.length < 10) {
                    errorMessage = "La description doit contenir au moins 10 caractères";
                    isValid = false;
                }
                break;
        }
        
        // Mise à jour de l'affichage
        updateFieldError(field, errorDiv, errorMessage, isValid, fieldId);
        checkFormValidity();
        
        return isValid;
    }

    // Validation de la priorité
    function validatePriorite() {
        const errorDiv = document.querySelector('[data-field="priorite"]');
        const isValid = prioriteInput.value !== '';
        const errorMessage = isValid ? '' : 'La priorité est obligatoire';
        
        updateFieldError(null, errorDiv, errorMessage, isValid, 'priorite');
        checkFormValidity();
        
        return isValid;
    }

    // Mise à jour de l'affichage des erreurs
    function updateFieldError(field, errorDiv, errorMessage, isValid, fieldId) {
        // Supprimer l'ancien message d'erreur
        if (errorDiv) {
            errorDiv.remove();
        }
        
        // Si erreur, créer un nouveau message
        if (!isValid) {
            const newErrorDiv = document.createElement('div');
            newErrorDiv.className = 'error-message-field';
            newErrorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${errorMessage}`;
            newErrorDiv.setAttribute('data-field', fieldId);
            
            // Ajouter après le champ
            if (field) {
                if (fieldId === 'telephone') {
                    field.parentNode.parentNode.appendChild(newErrorDiv);
                } else {
                    field.parentNode.appendChild(newErrorDiv);
                }
            } else {
                // Pour la priorité
                const prioriteGroup = document.getElementById('priorite-group');
                prioriteGroup.parentNode.appendChild(newErrorDiv);
            }
            
            // Ajouter classe d'erreur au champ
            if (field) {
                field.classList.add('error-field');
            } else {
                prioriteBtns.forEach(btn => btn.classList.add('error-field'));
            }
        } else {
            // Retirer classe d'erreur
            if (field) {
                field.classList.remove('error-field');
            } else {
                prioriteBtns.forEach(btn => btn.classList.remove('error-field'));
            }
        }
    }

    // Vérifier la validité globale du formulaire
    function checkFormValidity() {
        let isFormValid = true;
        
        // Vérifier tous les champs
        const requiredFields = ['nom', 'prenom', 'telephone', 'email', 'gouvernorat', 'delegation', 'position_gps', 'description_detaillee'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !validateField(fieldId, field)) {
                isFormValid = false;
            }
        });
        
        // Vérifier la priorité
        if (!validatePriorite()) {
            isFormValid = false;
        }
        
        // Activer/désactiver le bouton de soumission
        submitBtn.disabled = !isFormValid;
        
        return isFormValid;
    }

    // Validation complète du formulaire avant soumission
    function validateForm() {
        return checkFormValidity();
    }
</script>

<!-- Font Awesome pour les icônes -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>