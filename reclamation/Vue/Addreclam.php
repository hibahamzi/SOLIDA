<?php
// Initialisation des variables pour pré-remplir le formulaire
// Ces variables simulent le cas où l'utilisateur est connecté ou revient sur le formulaire
// après une erreur. Elles sont vides au premier chargement.

$nom = isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : "";
$prenom = isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : "";
$telephone = isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : "";
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : "";
$ville = isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : "";
$position_gps = isset($_POST['position_gps']) ? htmlspecialchars($_POST['position_gps']) : "";
$description = isset($_POST['description_detaillee']) ? htmlspecialchars($_POST['description_detaillee']) : "";
$gouvernorat_selectionne = isset($_POST['gouvernorat']) ? $_POST['gouvernorat'] : '';
$delegation_selectionnee = isset($_POST['delegation']) ? $_POST['delegation'] : '';
// Par défaut, la priorité est Normale
$priorite_selectionnee = isset($_POST['priorite']) ? $_POST['priorite'] : 'Normale'; 

// Liste des gouvernorats et délégations (Exemple pour la Tunisie)
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// --- Gestion de la soumission du formulaire ---
$message_soumission = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification basique des champs requis
    if (empty($nom) || empty($prenom) || empty($telephone) || empty($email) || empty($description) || empty($_POST['gouvernorat']) || empty($_POST['delegation'])) {
        $message_soumission = '<p class="error-message">❌ Veuillez remplir tous les champs obligatoires (*).</p>';
    } else {
        // Formulaire valide : Traitement des données (simulation)
        $message_soumission = '<p class="success-message">✅ Votre réclamation a été envoyée avec succès !</p>';
        
        // Optionnel : Réinitialiser les champs après succès si vous ne voulez pas afficher les données
        $nom = $prenom = $telephone = $email = $ville = $position_gps = $description = '';
        $gouvernorat_selectionne = $delegation_selectionnee = '';
        $priorite_selectionnee = 'Normale'; 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer une Réclamation</title>
    <style>
        /* CSS pour styliser le formulaire */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            padding: 20px;
        }

        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #8A2BE2; /* Violet du titre */
            border-bottom: 2px solid #E6E6FA;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        h3 {
            color: #8A2BE2; /* Violet des sous-titres */
            font-size: 1.1rem;
            margin-top: 25px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="email"],
        textarea,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        input[readonly] {
            background-color: #eee;
        }

        .required::after {
            content: " *";
            color: red;
        }

        /* Styling pour le Téléphone avec préfixe */
        .input-group-tel {
            display: flex;
            width: 100%;
        }

        .input-group-tel .prefix {
            background-color: #eee;
            border: 1px solid #ccc;
            border-right: none;
            padding: 10px;
            border-radius: 4px 0 0 4px;
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .input-group-tel input {
            border-radius: 0 4px 4px 0;
        }

        /* Styling pour la Position GPS avec bouton */
        .input-group-gps {
            display: flex;
            gap: 5px;
        }

        .input-group-gps input {
            flex-grow: 1;
        }

        .input-group-gps button {
            background-color: #8A2BE2; /* Bouton Localiser violet */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
            font-weight: bold;
        }
        
        .input-group-gps button:hover {
            background-color: #6A1B9A;
        }

        /* Zone de Priorité */
        .priorite-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .priorite-btn {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: opacity 0.2s;
            border: 2px solid transparent;
        }

        .priorite-btn.selected {
            border: 2px solid #333; /* Contour noir pour l'élément sélectionné */
        }

        /* Couleurs des priorités (comme dans l'image) */
        .priorite-faible { background-color: #66BB6A; } /* Vert clair */
        .priorite-normale { background-color: #FFC107; } /* Jaune */
        .priorite-urgente { background-color: #E57373; } /* Rouge clair */

        .priorite-faible:hover, .priorite-normale:hover, .priorite-urgente:hover {
            opacity: 0.8;
        }

        /* Bouton d'envoi */
        .submit-btn {
            width: 100%;
            background-color: #4AC0F2; /* Bleu ciel du bouton principal */
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.2s;
        }

        .submit-btn:hover {
            background-color: #2CAFD9;
        }
        
        .submit-btn span {
            margin-right: 10px;
        }

        /* Messages de soumission */
        .success-message {
            background-color: #D4EDDA;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #C3E6CB;
        }

        .error-message {
            background-color: #F8D7DA;
            color: #721C24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #F5C6CB;
        }
        
        /* Responsive Design */
        @media (max-width: 600px) {
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
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2><span>&#x270E;</span> Déposer une Réclamation</h2>
    
    <?php echo $message_soumission; // Affichage des messages de succès/erreur ?>

    <form method="POST" action="Addreclam.php"> 
        
        <h3><span>&#x2753;</span> Informations Personnelles</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nom" class="required">Nom</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?php echo $nom; ?>">
            </div>
            <div class="form-group">
                <label for="prenom" class="required">Prénom</label>
                <input type="text" id="prenom" name="prenom" required 
                       value="<?php echo $prenom; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="telephone" class="required">Téléphone</label>
                <div class="input-group-tel">
                    <span class="prefix">+216</span>
                    <input type="text" id="telephone" name="telephone" required 
                           value="<?php echo $telephone; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo $email; ?>">
            </div>
        </div>
        
        <h3><span>&#x2753;</span> Localisation</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="gouvernorat" class="required">Gouvernorat</label>
                <select id="gouvernorat" name="gouvernorat" required onchange="updateDelegations(this.value)">
                    <option value="">-- Choisir le gouvernorat --</option>
                    <?php
                    // Remplissage dynamique des gouvernorats
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
                    // Remplissage dynamique des délégations (si le formulaire est re-chargé après erreur)
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
                <input type="text" id="ville" name="ville" 
                       value="<?php echo $ville; ?>">
            </div>
            <div class="form-group">
                <label for="position_gps">Position GPS</label>
                <div class="input-group-gps">
                    <input type="text" id="position_gps" name="position_gps" readonly
                           value="<?php echo $position_gps; ?>">
                    <button type="button" onclick="getLocalisation()">
                       &#x1F4CD; Localiser 
                    </button>
                </div>
                <small>Cliquez sur "Localiser" pour détecter automatiquement votre position</small>
            </div>
        </div>

        <h3><span>&#x2261;</span> Détails de la Réclamation</h3>

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

        <div class="form-group">
            <label for="description_detaillee" class="required">Description détaillée</label>
            <textarea id="description_detaillee" name="description_detaillee" rows="6" required
                      placeholder="Décrivez ici les détails de votre réclamation..."><?php echo $description; ?></textarea>
        </div>

        <button type="submit" class="submit-btn">
            <span>&#x27A1;</span> Envoyer la réclamation
        </button>

    </form>
</div>

<script>
    // --- Script JavaScript pour l'interactivité ---

    // 1. Mise à jour dynamique des délégations
    const dataJs = <?php echo json_encode($data); ?>;
    const delegationSelect = document.getElementById('delegation');
    const gouvernoratSelect = document.getElementById('gouvernorat');
    const initialDelegationValue = "<?php echo $delegation_selectionnee; ?>";

    function updateDelegations(gouvernorat, initialValue = null) {
        delegationSelect.innerHTML = '<option value="">-- Choisir d\'abord le gouvernorat --</option>';
        if (gouvernorat && dataJs[gouvernorat]) {
            dataJs[gouvernorat].forEach(delegation => {
                const option = document.createElement('option');
                option.value = delegation;
                option.textContent = delegation;
                // Sélectionne la valeur si elle correspond à la valeur initiale
                if (delegation === initialValue) {
                    option.selected = true;
                }
                delegationSelect.appendChild(option);
            });
        }
    }
    
    // Initialisation au chargement
    document.addEventListener('DOMContentLoaded', () => {
        const selectedGouvernorat = gouvernoratSelect.value;
        // Recharge la liste de délégations si un gouvernorat est déjà sélectionné (par PHP)
        if (selectedGouvernorat) {
             // Utilise la valeur sélectionnée par PHP pour pré-remplir la bonne délégation
            updateDelegations(selectedGouvernorat, initialDelegationValue);
        }
    });

    // 2. Gestion du choix de la Priorité
    const prioriteBtns = document.querySelectorAll('.priorite-btn');
    const prioriteInput = document.getElementById('priorite_input');

    prioriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Désélectionner tous les boutons
            prioriteBtns.forEach(b => b.classList.remove('selected'));
            
            // Sélectionner le bouton cliqué
            this.classList.add('selected');
            
            // Mettre à jour le champ caché
            prioriteInput.value = this.getAttribute('data-value');
        });
    });

    // 3. Fonction de Localisation GPS
    function getLocalisation() {
        const gpsInput = document.getElementById('position_gps');
        gpsInput.value = 'Localisation en cours...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    gpsInput.value = `${lat}, ${lon}`;
                },
                (error) => {
                    alert("Erreur de Localisation: Veuillez autoriser la géolocalisation.");
                    gpsInput.value = 'Erreur: Refusé/Indisponible';
                }
            );
        } else {
            alert("Votre navigateur ne supporte pas la géolocalisation.");
            gpsInput.value = 'Non supporté';
        }
    }

</script>
</body>
</html>