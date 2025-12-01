<?php
// updateReclamation.php (Version Stylisée et Minimaliste)

require_once __DIR__ . '/../../Controllers/ReclamationController.php';

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

// Initialisation des variables pour pré-remplir avec les données existantes
$nom = htmlspecialchars($reclamation['nom'] ?? '');
$prenom = htmlspecialchars($reclamation['prenom'] ?? '');
$telephone = htmlspecialchars($reclamation['telephone'] ?? '');
$email = htmlspecialchars($reclamation['email'] ?? '');
$ville = htmlspecialchars($reclamation['ville'] ?? '');
$position_gps = htmlspecialchars($reclamation['position_gps'] ?? '');
$description = htmlspecialchars($reclamation['description_detaillee'] ?? '');
$gouvernorat_selectionne = $reclamation['gouvernorat'] ?? '';
$delegation_selectionnee = $reclamation['delegation'] ?? '';
$priorite_selectionnee = $reclamation['priorite'] ?? 'Normale';
$message_soumission = '';

// Liste des gouvernorats et délégations (pour les menus déroulants)
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// ------------------------------
// 🚀 Traitement de la Soumission (Logique simple)
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données POST et mise à jour des variables pour pré-remplissage en cas d'erreur
    $nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '';
    $telephone = isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $ville = isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : '';
    $position_gps = isset($_POST['position_gps']) ? htmlspecialchars($_POST['position_gps']) : '';
    $description = isset($_POST['description_detaillee']) ? htmlspecialchars($_POST['description_detaillee']) : '';
    $gouvernorat_selectionne = isset($_POST['gouvernorat']) ? $_POST['gouvernorat'] : '';
    $delegation_selectionnee = isset($_POST['delegation']) ? $_POST['delegation'] : '';
    $priorite_selectionnee = isset($_POST['priorite']) ? $_POST['priorite'] : 'Normale';
    $message_soumission = '';

    // Validation minimale côté serveur
    $erreurs = [];
    
    // Validation Nom (lettres uniquement + minimum 2 caractères)
    if (empty($_POST['nom'])) {
        $erreurs[] = "Le nom est obligatoire.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,}$/u', $_POST['nom'])) {
        $erreurs[] = "Le nom ne doit contenir que des lettres (minimum 2 caractères).";
    }
    
    // Validation Prénom (lettres uniquement + minimum 2 caractères)
    if (empty($_POST['prenom'])) {
        $erreurs[] = "Le prénom est obligatoire.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,}$/u', $_POST['prenom'])) {
        $erreurs[] = "Le prénom ne doit contenir que des lettres (minimum 2 caractères).";
    }
    
    // Validation Téléphone (exactement 8 chiffres)
    if (empty($_POST['telephone'])) {
        $erreurs[] = "Le téléphone est obligatoire.";
    } elseif (!preg_match('/^[0-9]{8}$/', $_POST['telephone'])) {
        $erreurs[] = "Le téléphone doit contenir exactement 8 chiffres.";
    }
    
    // Validation Email (doit contenir @gmail.com)
    if (empty($_POST['email'])) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email est invalide.";
    } elseif (!preg_match('/@gmail\.com$/i', $_POST['email'])) {
        $erreurs[] = "L'email doit se terminer par @gmail.com";
    }
    
    if (empty($_POST['gouvernorat'])) $erreurs[] = "Le gouvernorat est obligatoire.";
    if (empty($_POST['delegation'])) $erreurs[] = "La délégation est obligatoire.";
    
    // Validation Description (minimum 10 caractères)
    if (empty($_POST['description_detaillee'])) {
        $erreurs[] = "La description est obligatoire.";
    } elseif (strlen(trim($_POST['description_detaillee'])) < 10) {
        $erreurs[] = "La description doit contenir au moins 10 caractères.";
    }
    
    if (empty($_POST['position_gps'])) $erreurs[] = "La position GPS est obligatoire.";
    
    if (!empty($erreurs)) {
         $message_soumission = '<p class="error-message">❌ Erreur de validation :<br>' . implode('<br>', $erreurs) . '</p>';
    } else {
        $reclamation_update = [
            'id' => $id_reclamation,
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'gouvernorat' => $_POST['gouvernorat'] ?? '',
            'delegation' => $_POST['delegation'] ?? '',
            'ville' => $_POST['ville'] ?? '',
            'position_gps' => $_POST['position_gps'] ?? '',
            'description_detaillee' => $_POST['description_detaillee'] ?? '',
            'priorite' => $_POST['priorite'] ?? 'Normale',
            'statut' => $reclamation['statut'] ?? 'Nouveau', // Garder le statut existant
            'date_modification' => date('Y-m-d H:i:s')
        ];
        
        // Appel du contrôleur pour la mise à jour
        $ReclamationController = new ReclamationController();
        $result = $ReclamationController->updateReclamation($reclamation_update);
        
        if ($result) {
            // Redirection en cas de succès vers "Mes Réclamations" avec l'email
            $email_redirect = urlencode($_POST['email'] ?? '');
            header('Location: listeReclamation.php?success=update&email=' . $email_redirect);
            exit;
        } else {
            $message_soumission = '<p class="error-message">❌ Une erreur est survenue lors de la mise à jour.</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Réclamation - Design Vert</title>
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
        .input-error { 
            border-color: var(--color-error) !important; 
            box-shadow: 0 0 0 2px rgba(244, 67, 54, 0.1) !important;
        }
        .validation-error { 
            color: var(--color-error); 
            font-size: 0.85rem; 
            margin-top: 5px; 
            min-height: 20px;
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
        .input-group-tel input { 
            border-radius: 0 6px 6px 0; 
            max-width: 150px;
        }

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

        /* On garde une seule couleur pour la priorité pour respecter "vert akahaw" */
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

        /* Messages */
        .success-message { background-color: #DCEDC8; color: #33691E; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #8BC34A; }
        .error-message { background-color: #FFCDD2; color: #B71C1C; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #F44336; }

        /* Compteur de caractères */
        .char-counter {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
            display: flex;
            justify-content: space-between;
        }
        .char-counter .count {
            font-weight: bold;
        }
        .char-counter .insufficient {
            color: #F44336;
        }
        .char-counter .sufficient {
            color: #4CAF50;
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
    <h2><span>&#x270E;</span> Modifier la Réclamation #<?php echo $id_reclamation; ?></h2>
    
    <?php echo $message_soumission; ?>

    <form method="POST" id="reclamationForm"> 
        
        <div class="form-section-title"><span>&#x1F464;</span> Informations Personnelles</div>
        <div class="form-row">
            <div class="form-group">
                <label for="nom" class="required">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo $nom; ?>" 
                       required 
                       pattern="[a-zA-ZÀ-ÿ\s\-]{2,}"
                       title="Seules les lettres sont autorisées (minimum 2 caractères)"
                       oninput="validateField(this, 'nom')">
                <div id="nom-error" class="validation-error"></div>
            </div>
            <div class="form-group">
                <label for="prenom" class="required">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo $prenom; ?>" 
                       required 
                       pattern="[a-zA-ZÀ-ÿ\s\-]{2,}"
                       title="Seules les lettres sont autorisées (minimum 2 caractères)"
                       oninput="validateField(this, 'prenom')">
                <div id="prenom-error" class="validation-error"></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telephone" class="required">Téléphone</label>
                <div class="input-group-tel">
                    <span class="prefix">+216</span>
                    <input type="text" id="telephone" name="telephone" 
                           value="<?php echo $telephone; ?>" 
                           required 
                           pattern="[0-9]{8}"
                           title="Doit contenir exactement 8 chiffres"
                           maxlength="8"
                           oninput="validateField(this, 'telephone')">
                </div>
                <div id="telephone-error" class="validation-error"></div>
            </div>
            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo $email; ?>" 
                       required 
                       pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
                       title="Doit être une adresse Gmail valide (ex: exemple@gmail.com)"
                       oninput="validateField(this, 'email')">
                <div id="email-error" class="validation-error"></div>
            </div>
        </div>
        
        <div class="form-section-title"><span>&#x1F4CD;</span> Localisation</div>
        <div class="form-row">
            <div class="form-group">
                <label for="gouvernorat" class="required">Gouvernorat</label>
                <select id="gouvernorat" name="gouvernorat" required onchange="validateField(this, 'gouvernorat'); updateDelegations(this.value);">
                    <option value="">-- Choisir un gouvernorat --</option>
                    <?php
                    foreach (array_keys($data) as $gouvernorat) {
                        $selected = ($gouvernorat == $gouvernorat_selectionne) ? 'selected' : '';
                        echo "<option value='{$gouvernorat}' {$selected}>{$gouvernorat}</option>";
                    }
                    ?>
                </select>
                <div id="gouvernorat-error" class="validation-error"></div>
            </div>
            <div class="form-group">
                <label for="delegation" class="required">Délégation</label>
                <select id="delegation" name="delegation" required onchange="validateField(this, 'delegation')">
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
                <div id="delegation-error" class="validation-error"></div>
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
                        value="<?php echo $position_gps; ?>" placeholder="Latitude, Longitude ou Adresse"
                        oninput="validateField(this, 'position_gps')">
                    <button type="button" onclick="getLocalisation()">
                        &#x1F4CD; Localiser 
                    </button>
                </div>
                <div id="position_gps-error" class="validation-error"></div>
                <small>Cliquez sur "Localiser" pour détecter automatiquement votre position</small>
            </div>
        </div>
        
        <div class="form-section-title"><span>&#x1F4DD;</span> Détails de la Réclamation</div>
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
        
        <div class="form-group">
            <label for="description_detaillee" class="required">Description détaillée</label>
            <textarea id="description_detaillee" name="description_detaillee" 
                      rows="6" 
                      placeholder="Décrivez votre réclamation en détail..." 
                      minlength="10" 
                      required
                      oninput="validateDescription(this)"><?php echo $description; ?></textarea>
            <div id="description_detaillee-error" class="validation-error"></div>
            <div class="char-counter">
                <span>Minimum 10 caractères</span>
                <span class="count <?php echo (strlen($description) >= 10 ? 'sufficient' : 'insufficient'); ?>" id="desc-count"><?php echo strlen($description); ?></span>
            </div>
        </div>
        
        <button type="submit" class="submit-btn">
            <span>&#x27A1;</span> Mettre à jour la réclamation
        </button>
    </form>
</div>

<script>
    // --- Variables globales ---
    const dataJs = <?php echo json_encode($data); ?>;
    const delegationSelect = document.getElementById('delegation');
    const gouvernoratSelect = document.getElementById('gouvernorat');
    const initialDelegationValue = "<?php echo $delegation_selectionnee; ?>";
    
    // --- Initialisation au chargement ---
    document.addEventListener('DOMContentLoaded', () => {
        // Initialiser les délégations
        const selectedGouvernorat = gouvernoratSelect.value;
        if (selectedGouvernorat) {
            updateDelegations(selectedGouvernorat, initialDelegationValue);
        }
        
        // Valider tous les champs au chargement (pour afficher les erreurs initiales)
        validateAllFields();
    });
    
    // --- Fonctions pour les dépendances Gouvernorat/Délégation ---
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
    
    // --- Système de priorité ---
    const prioriteBtns = document.querySelectorAll('.priorite-btn');
    const prioriteInput = document.getElementById('priorite_input');
    
    prioriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            prioriteBtns.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            prioriteInput.value = this.getAttribute('data-value');
        });
    });
    
    // --- Validation générique pour tous les champs ---
    function validateField(input, fieldName) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const value = input.value.trim();
        
        // Réinitialiser le message d'erreur
        errorElement.textContent = '';
        input.classList.remove('input-error');
        
        // Validation selon le type de champ
        let isValid = true;
        let errorMessage = '';
        
        switch(fieldName) {
            case 'nom':
            case 'prenom':
                if (value === '') {
                    errorMessage = 'Ce champ est obligatoire';
                    isValid = false;
                } else if (!/^[a-zA-ZÀ-ÿ\s\-]{2,}$/u.test(value)) {
                    errorMessage = 'Doit contenir uniquement des lettres (minimum 2 caractères)';
                    isValid = false;
                }
                break;
                
            case 'telephone':
                // N'autoriser que les chiffres
                let phoneValue = value.replace(/\D/g, '');
                if (phoneValue.length > 8) {
                    phoneValue = phoneValue.slice(0, 8);
                }
                input.value = phoneValue;
                
                if (phoneValue === '') {
                    errorMessage = 'Ce champ est obligatoire';
                    isValid = false;
                } else if (!/^[0-9]{8}$/.test(phoneValue)) {
                    errorMessage = 'Le téléphone doit contenir exactement 8 chiffres';
                    isValid = false;
                }
                break;
                
            case 'email':
                if (value === '') {
                    errorMessage = 'Ce champ est obligatoire';
                    isValid = false;
                } else if (!/^[a-zA-Z0-9._%+-]+@gmail\.com$/i.test(value)) {
                    errorMessage = 'L\'email doit se terminer par @gmail.com';
                    isValid = false;
                }
                break;
                
            case 'gouvernorat':
            case 'delegation':
            case 'position_gps':
                if (value === '') {
                    errorMessage = 'Ce champ est obligatoire';
                    isValid = false;
                }
                break;
        }
        
        // Afficher l'erreur si nécessaire
        if (!isValid) {
            errorElement.textContent = errorMessage;
            input.classList.add('input-error');
        }
        
        return isValid;
    }
    
    // --- Validation spécifique pour la description ---
    function validateDescription(textarea) {
        const errorElement = document.getElementById('description_detaillee-error');
        const countElement = document.getElementById('desc-count');
        const value = textarea.value;
        
        // Mettre à jour le compteur
        const length = value.length;
        countElement.textContent = length;
        
        // Changer la couleur selon le nombre de caractères
        if (length < 10) {
            countElement.className = 'count insufficient';
        } else {
            countElement.className = 'count sufficient';
        }
        
        // Réinitialiser le message d'erreur
        errorElement.textContent = '';
        textarea.classList.remove('input-error');
        
        // Validation
        if (value === '') {
            errorElement.textContent = 'Ce champ est obligatoire';
            textarea.classList.add('input-error');
            return false;
        } else if (length < 10) {
            errorElement.textContent = 'La description doit contenir au moins 10 caractères';
            textarea.classList.add('input-error');
            return false;
        }
        
        return true;
    }
    
    // --- Valider tous les champs (pour l'initialisation) ---
    function validateAllFields() {
        const fields = [
            { id: 'nom', name: 'nom' },
            { id: 'prenom', name: 'prenom' },
            { id: 'telephone', name: 'telephone' },
            { id: 'email', name: 'email' },
            { id: 'gouvernorat', name: 'gouvernorat' },
            { id: 'delegation', name: 'delegation' },
            { id: 'position_gps', name: 'position_gps' }
        ];
        
        fields.forEach(field => {
            const input = document.getElementById(field.id);
            if (input) {
                validateField(input, field.name);
            }
        });
        
        // Valider la description
        const descriptionTextarea = document.getElementById('description_detaillee');
        if (descriptionTextarea) {
            validateDescription(descriptionTextarea);
        }
    }
    
    // --- Validation complète au submit ---
    document.getElementById('reclamationForm').addEventListener('submit', function(e) {
        let isValid = true;
        const errorFields = [];
        
        // Valider tous les champs
        const fieldsToValidate = [
            { id: 'nom', name: 'nom' },
            { id: 'prenom', name: 'prenom' },
            { id: 'telephone', name: 'telephone' },
            { id: 'email', name: 'email' },
            { id: 'gouvernorat', name: 'gouvernorat' },
            { id: 'delegation', name: 'delegation' },
            { id: 'position_gps', name: 'position_gps' }
        ];
        
        fieldsToValidate.forEach(field => {
            const input = document.getElementById(field.id);
            if (input && !validateField(input, field.name)) {
                isValid = false;
                errorFields.push(field.name);
            }
        });
        
        // Valider la description
        const descriptionTextarea = document.getElementById('description_detaillee');
        if (descriptionTextarea && !validateDescription(descriptionTextarea)) {
            isValid = false;
            errorFields.push('description_detaillee');
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Supprimer les anciens messages d'erreur généraux
            const oldMessages = document.querySelectorAll('.error-message');
            oldMessages.forEach(msg => {
                if (msg.innerHTML.includes('Veuillez corriger')) {
                    msg.remove();
                }
            });
            
            // Afficher un nouveau message d'erreur général
            const messageDiv = document.createElement('div');
            messageDiv.className = 'error-message';
            messageDiv.innerHTML = '❌ Veuillez corriger les erreurs dans le formulaire avant de soumettre.';
            
            // Insérer le message après le h2
            const h2 = document.querySelector('h2');
            h2.parentNode.insertBefore(messageDiv, h2.nextSibling);
            
            // Faire défiler vers la première erreur
            if (errorFields.length > 0) {
                const firstErrorField = errorFields[0];
                const firstErrorInput = document.querySelector(`[name="${firstErrorField}"]`);
                if (firstErrorInput) {
                    firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorInput.focus();
                }
            }
        }
    });
    
    // --- Validation automatique à la perte de focus ---
    const fieldsToValidateOnBlur = ['nom', 'prenom', 'telephone', 'email', 'position_gps'];
    fieldsToValidateOnBlur.forEach(fieldName => {
        const input = document.getElementById(fieldName);
        if (input) {
            input.addEventListener('blur', function() {
                validateField(this, fieldName);
            });
        }
    });
    
    // Validation pour les select
    document.getElementById('gouvernorat').addEventListener('blur', function() {
        validateField(this, 'gouvernorat');
    });
    
    document.getElementById('delegation').addEventListener('blur', function() {
        validateField(this, 'delegation');
    });
    
    document.getElementById('description_detaillee').addEventListener('blur', function() {
        validateDescription(this);
    });
    
    // --- Fonction de géolocalisation ---
    function getLocalisation() {
        const gpsInput = document.getElementById('position_gps');
        const villeInput = document.getElementById('ville');
        gpsInput.value = 'Localisation en cours...';
        
        // Effacer l'erreur
        const errorElement = document.getElementById('position_gps-error');
        if (errorElement) {
            errorElement.textContent = '';
        }
        gpsInput.classList.remove('input-error');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // Utilisation du proxy pour le géocodage inverse
                    const apiUrl =
                        `https://api.allorigins.win/get?url=` +
                        encodeURIComponent(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`
                        );
                    fetch(apiUrl)
                        .then(response => response.json())
                        .then(result => {
                            const data = JSON.parse(result.contents);
                            if (data.display_name) {
                                // Afficher l'adresse complète dans le champ GPS
                                gpsInput.value = data.display_name;
                                // Tenter de remplir le champ Ville
                                const ville =
                                    data.address.city ||
                                    data.address.town ||
                                    data.address.village ||
                                    "";
                                villeInput.value = ville;
                            } else {
                                // Si l'adresse n'est pas trouvée, afficher les coordonnées
                                gpsInput.value = `${lat}, ${lon} (Adresse non trouvée)`;
                            }
                            // Valider le champ après remplissage
                            validateField(gpsInput, 'position_gps');
                        })
                        .catch(err => {
                            console.error("Erreur API :", err);
                            // En cas d'échec de l'API, afficher au moins les coordonnées
                            gpsInput.value = `${lat}, ${lon} (Erreur API)`;
                            validateField(gpsInput, 'position_gps');
                        });
                },
                () => {
                    gpsInput.value = "Erreur : Permission refusée";
                    gpsInput.classList.add('input-error');
                    const errorElement = document.getElementById('position_gps-error');
                    if (errorElement) {
                        errorElement.textContent = 'Autorisez la géolocalisation ou entrez manuellement la position';
                    }
                    alert("Autorisez la géolocalisation !");
                }
            );
        } else {
            gpsInput.value = "Géolocalisation non supportée";
            gpsInput.classList.add('input-error');
            const errorElement = document.getElementById('position_gps-error');
            if (errorElement) {
                errorElement.textContent = 'Votre navigateur ne supporte pas la géolocalisation';
            }
        }
    }
</script>
</body>
</html>