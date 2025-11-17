<?php
// ------------------------------
// 🔧 Configuration et Débug
// ------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Liste des gouvernorats et délégations (Doit correspondre à Addreclam.php)
$data = [
    'Ariana' => ['Ariana Ville', 'Soukra', 'Raoued', 'Sidi Thabet'],
    'Ben Arous' => ['Ben Arous Ville', 'Mourouj', 'Ezzahra', 'Hammam Lif'],
    'Tunis' => ['Tunis Ville', 'Menzah', 'Lac', 'Carthage'],
    'Sfax' => ['Sfax Ville', 'Sakiet Ezzit', 'Thyna'],
];

// --- 1. CONNEXION À LA BASE DE DONNÉES ---
$conn = new mysqli("localhost", "root", "", "reclamations_db");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    // Dans un cas réel, loggez cette erreur au lieu d'afficher "die"
    die("❌ Erreur de connexion à la base de données : " . $conn->connect_error);
}

$message_soumission = '';
$reclamation = null;
$reclamation_id = null;

// Vérifier si un ID de réclamation est passé dans l'URL (GET)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reclamation_id = $_GET['id'];
} else {
    // Si l'ID est manquant ou invalide, on redirige
    header("Location: ../liste-reclam.php?error=invalid_id");
    exit();
}

// ------------------------------
// 🚀 Traitement de la Modification (POST)
// ------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // L'ID vient du champ caché du formulaire
    $id = $_POST['id'];
    
    // Validation des champs requis
    if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['telephone']) || empty($_POST['email']) || empty($_POST['description']) || empty($_POST['gouvernorat']) || empty($_POST['delegation'])) {
        $message_soumission = '<p class="error-message">❌ Veuillez remplir tous les champs obligatoires (*).</p>';
    } else {
        // Préparation de la requête UPDATE pour plus de sécurité
        $sql = "UPDATE reclamations SET 
                    nom = ?, prenom = ?, telephone = ?, email = ?, 
                    gouvernorat = ?, delegation = ?, ville = ?, position_gps = ?, 
                    description_detaillee = ?, priorite = ?, statut = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        
        // Récupération des données POST
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $email = $_POST['email'] ?? '';
        $gouvernorat = $_POST['gouvernorat'] ?? '';
        $delegation = $_POST['delegation'] ?? '';
        $ville = $_POST['ville'] ?? '';
        $positionGPS = $_POST['positionGPS'] ?? ''; // Utilisé comme 'position_gps' dans la DB
        $description = $_POST['description'] ?? ''; // Utilisé comme 'description_detaillee' dans la DB
        $priorite = $_POST['priorite'] ?? 'Normale';
        $statut = $_POST['statut'] ?? 'Nouveau';

        // Lier les paramètres : ssssssssssss (11 strings + 1 integer pour l'ID)
        // Vérifiez que l'ordre et les types correspondent à l'ordre dans la requête SQL !
        $stmt->bind_param("sssssssssssi", 
            $nom, 
            $prenom, 
            $telephone, 
            $email, 
            $gouvernorat, 
            $delegation, 
            $ville, 
            $positionGPS, 
            $description, 
            $priorite,
            $statut,
            $id // ID à la fin
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            // Succès : Redirection immédiate vers la liste
            header("Location: ../liste-reclam.php?success=updated");
            exit();
        } else {
            // Échec de la mise à jour
            $message_soumission = '<p class="error-message">❌ Erreur lors de la mise à jour : ' . $stmt->error . '</p>';
        }
        
        $stmt->close();
    }

    // Si le POST a échoué (erreur de validation ou DB), on utilise les données postées pour pré-remplir
    // On met à jour l'array $reclamation avec les valeurs POST pour l'affichage du formulaire
    $reclamation = [
        'id' => $id,
        'nom' => $nom ?? '',
        'prenom' => $prenom ?? '',
        'telephone' => $telephone ?? '',
        'email' => $email ?? '',
        'gouvernorat' => $gouvernorat ?? '',
        'delegation' => $delegation ?? '',
        'ville' => $ville ?? '',
        'position_gps' => $positionGPS ?? '', 
        'description_detaillee' => $description ?? '', 
        'priorite' => $priorite ?? 'Normale',
        'statut' => $statut ?? 'Nouveau',
    ];

} else {
    // ------------------------------
    // 🔄 Récupération des données pour l'affichage (GET)
    // ------------------------------
    $sql_fetch = "SELECT * FROM reclamations WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $reclamation_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows === 1) {
        $reclamation = $result->fetch_assoc();
    } else {
        // Réclamation non trouvée
        $conn->close(); // Fermer la connexion avant la redirection
        header("Location: ../liste-reclam.php?error=not_found");
        exit();
    }
    $stmt_fetch->close();
}

// Fermer la connexion si elle est encore ouverte (elle n'est plus ouverte si POST succès ou GET échec)
if (isset($conn) && $conn->ping()) {
    $conn->close();
}

// Définir les variables pour le formulaire HTML, en utilisant les données récupérées ou postées
$db_nom = $reclamation ? htmlspecialchars($reclamation['nom']) : '';
$db_prenom = $reclamation ? htmlspecialchars($reclamation['prenom']) : '';
$db_telephone = $reclamation ? htmlspecialchars($reclamation['telephone']) : '';
$db_email = $reclamation ? htmlspecialchars($reclamation['email']) : '';
$db_ville = $reclamation ? htmlspecialchars($reclamation['ville']) : '';
$db_position_gps = $reclamation ? htmlspecialchars($reclamation['position_gps']) : ''; 
$db_description = $reclamation ? htmlspecialchars($reclamation['description_detaillee']) : ''; 
$db_priorite = $reclamation ? htmlspecialchars($reclamation['priorite']) : 'Normale';
$db_statut = $reclamation ? htmlspecialchars($reclamation['statut']) : 'Nouveau';

$gouvernorat_selectionne = $reclamation['gouvernorat'] ?? '';
$delegation_selectionnee = $reclamation['delegation'] ?? '';

// Ajout de la variable data pour le JS de mise à jour des délégations
$dataJs = json_encode($data);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Réclamation</title>
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h2 { display: flex; align-items: center; color: #961096; margin-bottom: 30px; border-bottom: 2px solid #6903769c; padding-bottom: 10px; }
        .section-header { color: #f23fb9; font-weight: bold; margin-top: 20px; margin-bottom: 15px; display: flex; align-items: center; }
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        label { margin-bottom: 5px; font-size: 0.9em; color: #40b6f1; }
        input[type="text"], input[type="email"], select, textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 100%; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        .required::after { content: " *"; color: red; }
        .tel-group { display: flex; }
        .tel-prefix { padding: 10px; border: 1px solid #ccc; border-right: none; background-color: #eee; border-radius: 4px 0 0 4px; }
        .tel-input { flex-grow: 1; border-radius: 0 4px 4px 0 !important; }
        .btn-localize { background-color: #d443c8; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .priority-group { display: flex; gap: 10px; align-items: center; }
        .priority-group .btn { padding: 8px 15px; border: none; border-radius: 4px; color: white; font-weight: bold; cursor: pointer; flex: 1; text-align: center; border: 2px solid transparent; }
        .priority-group .btn.selected { border: 2px solid #333; }
        .btn-faible { background-color: #408f50; }
        .btn-normale { background-color: #f8ce4f; color: #333;}
        .btn-urgente { background-color: #b65962;}
        .btn-submit { background-color: #65cff6; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1.1em; margin-top: 30px; display: flex; justify-content: center; align-items: center; }
        .btn-cancel { background-color: #f44336; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1.1em; margin-top: 10px; display: flex; justify-content: center; align-items: center; }
        .navigation { margin-bottom: 20px; }
        .nav-btn { background-color: #961096; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .error-message { background-color: #F8D7DA; color: #721C24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #F5C6CB; }
    </style>
</head>

<body>
    <div class="container">

        <div class="navigation">
            <a href="../liste-reclam.php" class="nav-btn">← Retour à la liste</a>
        </div>

        <h2>&#9998;&nbsp;Modifier la Réclamation #<?php echo htmlspecialchars($reclamation_id); ?></h2>

        <?php echo $message_soumission; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

            <input type="hidden" id="reclamation-id" name="id" value="<?php echo htmlspecialchars($reclamation_id); ?>">

            <div class="section-header">&#9432; Informations Personnelles</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?php echo $db_nom; ?>">
                </div>

                <div class="form-group">
                    <label class="required">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required value="<?php echo $db_prenom; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Téléphone</label>
                    <div class="tel-group">
                        <span class="tel-prefix">+216</span>
                        <input type="text" id="telephone" name="telephone" class="tel-input" required value="<?php echo $db_telephone; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo $db_email; ?>">
                </div>
            </div>

            <div class="section-header">&#9906; Localisation</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Gouvernorat</label>
                    <select id="gouvernorat" name="gouvernorat" required onchange="updateDelegations(this.value)">
                        <option value="">-- Choisir --</option>
                        <?php
                        foreach (array_keys($data) as $gouv) {
                            $selected = ($gouvernorat_selectionne == $gouv) ? 'selected' : '';
                            echo "<option value='{$gouv}' {$selected}>{$gouv}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required">Délégation</label>
                    <select id="delegation" name="delegation" required>
                        <option value="">-- Choisir d'abord le gouvernorat --</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" id="ville" name="ville" value="<?php echo $db_ville; ?>">
                </div>

                <div class="form-group">
                    <label class="required">Position GPS (Adresse Texte)</label>
                    <div style="display:flex;">
                        <input type="text" id="position-gps" name="positionGPS" required value="<?php echo $db_position_gps; ?>">
                        <button type="button" class="btn-localize" id="btn-localiser" onclick="getLocalisation()">&#9906; Localiser</button>
                    </div>
                </div>
            </div>

            <div class="section-header">&#9776; Détails de la Réclamation</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Priorité</label>
                    <div class="priority-group">
                        <button type="button" class="btn btn-faible" data-value="Faible">Faible</button>
                        <button type="button" class="btn btn-normale" data-value="Normale">Normale</button>
                        <button type="button" class="btn btn-urgente" data-value="Urgente">Urgente</button>
                    </div>
                    <input type="hidden" id="priorite" name="priorite" value="<?php echo $db_priorite; ?>">
                </div>
                
                <div class="form-group">
                    <label>Statut</label>
                    <select id="statut" name="statut">
                        <?php
                        $statuts = ["Nouveau", "En cours", "Résolu", "Fermé"];
                        foreach ($statuts as $s) {
                            $selected = ($db_statut == $s) ? 'selected' : '';
                            echo "<option value='{$s}' {$selected}>{$s}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width:100%;">
                    <label class="required">Description détaillée</label>
                    <textarea id="description" name="description" required><?php echo $db_description; ?></textarea>
                </div>
            </div>

            <button class="btn-submit" type="submit">&#9998;&nbsp;Modifier la réclamation</button>
            <button type="button" class="btn-cancel" onclick="location.href='../liste-reclam.php'">Annuler</button>

        </form>
    </div>

<script>
    // --- Script JavaScript pour l'interactivité ---

    // Données des délégations encodées depuis le PHP
    const dataJs = <?php echo $dataJs; ?>;
    const delegationSelect = document.getElementById('delegation');
    const gouvernoratSelect = document.getElementById('gouvernorat');
    const initialDelegationValue = "<?php echo $delegation_selectionnee; ?>";
    const initialPriorite = "<?php echo $db_priorite; ?>";
    const villeInput = document.getElementById('ville');

    function updateDelegations(gouvernorat, initialValue = null) {
        delegationSelect.innerHTML = '<option value="">-- Choisir une délégation --</option>';
        if (gouvernorat && dataJs[gouvernorat]) {
            dataJs[gouvernorat].forEach(delegation => {
                const option = document.createElement('option');
                option.value = delegation;
                option.textContent = delegation;
                // Si l'initialValue est définie (au chargement) et correspond, on sélectionne
                if (delegation === initialValue) {
                    option.selected = true;
                }
                delegationSelect.appendChild(option);
            });
        }
    }
    
    // Mise à jour de l'affichage initial au chargement de la page
    document.addEventListener('DOMContentLoaded', () => {
        const selectedGouvernorat = gouvernoratSelect.value;
        if (selectedGouvernorat) {
            // Initialisation avec la valeur de la DB/POST
            updateDelegations(selectedGouvernorat, initialDelegationValue); 
        }

        // Gestion de l'état sélectionné pour les boutons de priorité
        const prioriteBtns = document.querySelectorAll('.priority-group .btn');
        prioriteBtns.forEach(btn => {
            if (btn.getAttribute('data-value') === initialPriorite) {
                btn.classList.add('selected');
            }
        });
    });

    // Écouteurs d'événements pour les boutons de priorité
    const prioriteBtns = document.querySelectorAll('.priority-group .btn');
    const prioriteInput = document.getElementById('priorite');

    prioriteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            prioriteBtns.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            prioriteInput.value = this.getAttribute('data-value');
        });
    });

    /**
     * MODIFICATION ICI : Fonction de géolocalisation pour obtenir l'adresse en texte (Reverse Geocoding).
     */
    function getLocalisation() {
        const gpsInput = document.getElementById('position-gps');
        
        gpsInput.value = 'Localisation en cours...';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // 1. Appel au service de Géo-codage Inverse (Nominatim OpenStreetMap)
                    const apiUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`;
                    
                    fetch(apiUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('API Nominatim non disponible');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.display_name) {
                                // 2. Afficher l'adresse complète dans le champ Position GPS
                                gpsInput.value = data.display_name;

                                // Optionnel : Tenter de remplir le champ "Ville"
                                if (data.address.city) {
                                    villeInput.value = data.address.city;
                                } else if (data.address.town) {
                                    villeInput.value = data.address.town;
                                } else if (data.address.village) {
                                    villeInput.value = data.address.village;
                                }
                                
                            } else {
                                // Si l'API ne trouve pas d'adresse
                                gpsInput.value = `Coordonnées: ${lat}, ${lon} (Adresse non trouvée)`;
                            }
                        })
                        .catch(error => {
                            // Erreur de communication avec l'API
                            console.error('Erreur API Nominatim:', error);
                            gpsInput.value = `Erreur API: ${lat}, ${lon} (Adresse non récupérée)`;
                            alert("Erreur lors de la récupération de l'adresse textuelle. Les coordonnées brutes ont été enregistrées.");
                        });

                },
                (error) => {
                    // Erreur de géolocalisation du navigateur
                    let errorMessage = "Erreur: Refusé/Indisponible";
                    if (error.code === 1) { // PERMISSION_DENIED
                        errorMessage = "Erreur: Accès refusé par l'utilisateur.";
                    }
                    alert("Erreur de Localisation: Veuillez autoriser la géolocalisation pour obtenir l'adresse.");
                    gpsInput.value = errorMessage;
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