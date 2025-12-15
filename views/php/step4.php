<?php
// Start output buffering to prevent "headers already sent" errors
if (!ob_get_level()) {
    ob_start();
}

session_start();
// Inclusion des contrôleurs et modèles nécessaires
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/donc.php';
require_once __DIR__ . '/../../models/don.php';

// Helper function to generate correct URLs based on context
function getDonsUrl($path = '', $params = []) {
    // Calculate base path dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base path (remove /views/php if present)
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $scriptPath = str_replace('/views/php', '', $scriptPath);
    $scriptPath = str_replace('\\views\\php', '', $scriptPath);
    
    // Build base URL
    $base = $protocol . '://' . $host . $scriptPath . '/views/front_office/index.php?section=dons';
    
    if ($path === 'step-type' || $path === '') {
        return $base;
    } elseif ($path === 'step4') {
        return $base . '&step=4';
    } elseif ($path === 'step5') {
        // Return both routing URL and direct URL as fallback
        return $base . '&step=5';
    } elseif ($path === 'step5-direct') {
        // Direct URL to step5.php (fallback)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        $scriptPath = str_replace('/views/php', '', $scriptPath);
        $scriptPath = str_replace('\\views\\php', '', $scriptPath);
        return $protocol . '://' . $host . $scriptPath . '/views/php/step5.php';
    } elseif ($path === 'association2') {
        $id = $params['id'] ?? '';
        return $base . '&association_id=' . $id;
    }
    return '#';
}

function getHomeUrl() {
    $isRouted = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'index.php') !== false);
    if ($isRouted) {
        return '../front_office/index.php';
    }
    return 'index.php';
}

// Get data from POST (when coming from association2) or from session
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['don_type']) && isset($_POST['association_id']) && !isset($_POST['amount']) && !isset($_POST['bloodType']) && !isset($_POST['foodTypes'])) {
    // Coming from association2.php - set session variables from POST
    $_SESSION['don_type'] = strtolower(trim($_POST['don_type']));
    $_SESSION['association_id'] = (int)$_POST['association_id'];
    $_SESSION['association_name'] = $_POST['association_name'] ?? 'Association Inconnue';
    $_SESSION['country'] = $_POST['country'] ?? null;
}

$donType = strtolower(trim($_SESSION['don_type'] ?? ''));
$selectedCountry = $_SESSION['country'] ?? null;
$associationId = $_SESSION['association_id'] ?? null;
$associationName = $_SESSION['association_name'] ?? 'Association Inconnue';

// Vérification des données
if (!$donType || !$associationId || !$selectedCountry) {
    header("Location: " . getDonsUrl('step-type'));
    exit();
}

$typeLabel = match ($donType) {
    'sang' => 'Sang',
    'argent' => 'Argent',
    'nourriture' => 'Nourriture',
    default => 'Type Inconnu'
};

$error = '';
$captchaError = '';

// =========================================================================================
// AJOUT POUR LE CAPTCHA MÉTIER AVANCÉ - Début
// =========================================================================================

// Définition des questions/réponses spécifiques au contexte Solida
$captchaQuestions = [
    'sang' => [
        ['question' => "Si vous donnez du sang, quelle est l'abréviation de votre groupe sanguin (deux lettres, ex: AB) ?", 'type' => 'bloodType', 'field' => 'bloodType'],
        ['question' => "Quelle est la lettre la plus courante dans les groupes sanguins (A, B ou O) ?", 'answer' => 'O'],
        ['question' => "Le don de sang nécessite d'avoir quel type de liquide dans les veines ?", 'answer' => 'sang'],
    ],
    'argent' => [
        ['question' => "Si vous donnez 10€, combien de pièces de 5€ cela représente-t-il ?", 'answer' => '2'],
        ['question' => "Quel symbole monétaire représente le dollar (un caractère) ?", 'answer' => '$'],
        ['question' => "Si vous donnez de l'argent, quel type de transaction effectuez-vous ?", 'answer' => 'don'],
    ],
    'nourriture' => [
        ['question' => "Quel est l'aliment de base souvent stocké dans un sac, qui nécessite de l'eau pour cuire ?", 'answer' => 'riz'],
        ['question' => "Les pâtes sont-elles un aliment sec ou liquide ?", 'answer' => 'sec'],
        ['question' => "Si vous donnez 3 conserves et 2 paquets de pâtes, combien d'articles donnez-vous ?", 'answer' => '5'],
    ],
];

// Initialiser le CAPTCHA seulement si on n'est pas en train de soumettre le formulaire
// OU si le CAPTCHA n'est pas déjà initialisé dans la session
if ((!isset($_POST['amount']) && !isset($_POST['bloodType']) && !isset($_POST['foodTypes'])) || 
    (!isset($_SESSION['captcha_type_check']) && !isset($_SESSION['captcha_answer']))) {
    // Sélectionner le jeu de questions en fonction du type de don
    $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture']; 
    
    // Choisir une question aléatoire
    $captchaKey = array_rand($currentQuestions);
    $selectedCaptcha = $currentQuestions[$captchaKey];
    
    // Stocker la réponse attendue en session pour vérification
    // Si c'est un champ spécifique (bloodType), on stocke le type, sinon on stocke la réponse
    if (isset($selectedCaptcha['type'])) {
        $_SESSION['captcha_type_check'] = $selectedCaptcha['type'];
        $_SESSION['captcha_answer'] = null; // On vérifie le champ lui-même
    } else {
        $_SESSION['captcha_type_check'] = null;
        $_SESSION['captcha_answer'] = strtolower($selectedCaptcha['answer']);
    }
} else {
    // Si on soumet le formulaire ET le CAPTCHA est déjà initialisé, récupérer la question depuis les questions
    // pour l'affichage (mais garder les valeurs de session pour la validation)
    $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture'];
    
    // Trouver la question correspondante dans le tableau pour l'affichage
    $selectedCaptcha = null;
    foreach ($currentQuestions as $q) {
        if (isset($_SESSION['captcha_type_check']) && $_SESSION['captcha_type_check'] === 'bloodType' && isset($q['type']) && $q['type'] === 'bloodType') {
            $selectedCaptcha = $q;
            break;
        } elseif (!isset($_SESSION['captcha_type_check']) && isset($q['answer']) && strtolower($q['answer']) === $_SESSION['captcha_answer']) {
            $selectedCaptcha = $q;
            break;
        }
    }
    
    // Si on n'a pas trouvé, utiliser la première question comme fallback
    if (!$selectedCaptcha) {
        $selectedCaptcha = $currentQuestions[array_key_first($currentQuestions)];
    }
}

// Nettoyage de la réponse utilisateur après une erreur
if (isset($_SESSION['captcha_error'])) {
    $captchaError = $_SESSION['captcha_error'];
    unset($_SESSION['captcha_error']);
}

// =========================================================================================
// AJOUT POUR LE CAPTCHA MÉTIER AVANCÉ - Fin
// =========================================================================================

// TRAITEMENT DE LA SOUMISSION DU FORMULAIRE
// Check if this is a form submission - be very lenient to catch all cases
$isFormSubmission = $_SERVER["REQUEST_METHOD"] == "POST" && (
    isset($_POST['form_submitted']) ||
    isset($_POST['amount']) || 
    isset($_POST['bloodType']) || 
    isset($_POST['foodTypes']) ||
    isset($_POST['don_type']) ||
    isset($_POST['don_type_display']) ||
    isset($_POST['don_type_hidden']) ||
    isset($_POST['captcha_answer']) ||
    (isset($_POST['association_name_display']) || isset($_POST['country_display']))
);

error_log("=== FORM SUBMISSION CHECK ===");
error_log("POST check - Method: " . $_SERVER["REQUEST_METHOD"]);
error_log("POST check - Has amount: " . (isset($_POST['amount']) ? 'yes (' . $_POST['amount'] . ')' : 'no'));
error_log("POST check - Has bloodType: " . (isset($_POST['bloodType']) ? 'yes (' . $_POST['bloodType'] . ')' : 'no'));
error_log("POST check - Has foodTypes: " . (isset($_POST['foodTypes']) ? 'yes (' . print_r($_POST['foodTypes'], true) . ')' : 'no'));
error_log("POST check - Has don_type: " . (isset($_POST['don_type']) ? 'yes (' . $_POST['don_type'] . ')' : 'no'));
error_log("POST check - Has don_type_display: " . (isset($_POST['don_type_display']) ? 'yes (' . $_POST['don_type_display'] . ')' : 'no'));
error_log("POST check - Has don_type_hidden: " . (isset($_POST['don_type_hidden']) ? 'yes (' . $_POST['don_type_hidden'] . ')' : 'no'));
error_log("POST check - Has captcha_answer: " . (isset($_POST['captcha_answer']) ? 'yes' : 'no'));
error_log("POST check - Is form submission: " . ($isFormSubmission ? 'yes' : 'no'));
error_log("Full POST: " . print_r($_POST, true));

if ($isFormSubmission) {
    // Update session with display field values if provided
    if (isset($_POST['don_type_display']) && !empty($_POST['don_type_display'])) {
        $_SESSION['don_type'] = strtolower(trim($_POST['don_type_display']));
    }
    if (isset($_POST['association_name_display']) && !empty($_POST['association_name_display'])) {
        $_SESSION['association_name'] = trim($_POST['association_name_display']);
    }
    if (isset($_POST['country_display']) && !empty($_POST['country_display'])) {
        $_SESSION['country'] = trim($_POST['country_display']);
    }
    
    // Update variables from POST or session
    $donType = strtolower(trim($_POST['don_type'] ?? $_POST['don_type_display'] ?? $_SESSION['don_type'] ?? ''));
    $associationName = $_POST['association_name_display'] ?? $_POST['association_name'] ?? $_SESSION['association_name'] ?? 'Association Inconnue';
    $selectedCountry = $_POST['country_display'] ?? $_POST['country'] ?? $_SESSION['country'] ?? null;
    $associationId = $_POST['association_id'] ?? $_SESSION['association_id'] ?? null;
    
    // This is the actual donation form submission
    $valide = true;
    $captchaValid = false; // Initialisation pour éviter 'Undefined variable'
    
    // =========================================================================================
    // VÉRIFICATION DU CAPTCHA AVANCÉ (CORRIGÉE ET SÉCURISÉE) - Début
    // =========================================================================================
    
    // Vérifier si le CAPTCHA est initialisé dans la session
    if (!isset($_SESSION['captcha_type_check']) && !isset($_SESSION['captcha_answer'])) {
        // CAPTCHA non initialisé, l'initialiser maintenant
        $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture'];
        $captchaKey = array_rand($currentQuestions);
        $selectedCaptcha = $currentQuestions[$captchaKey];
        
        if (isset($selectedCaptcha['type'])) {
            $_SESSION['captcha_type_check'] = $selectedCaptcha['type'];
            $_SESSION['captcha_answer'] = null;
        } else {
            $_SESSION['captcha_type_check'] = null;
            $_SESSION['captcha_answer'] = strtolower($selectedCaptcha['answer']);
        }
    }
    
    $userAnswer = strtolower(trim($_POST['captcha_answer'] ?? ''));

    // Debug: Log CAPTCHA validation attempt
    error_log("CAPTCHA Validation - User answer: '$userAnswer', Session answer: '" . ($_SESSION['captcha_answer'] ?? 'NULL') . "', Type check: " . ($_SESSION['captcha_type_check'] ?? 'NULL'));

    if (isset($_SESSION['captcha_type_check']) && $_SESSION['captcha_type_check'] === 'bloodType') {
        // Cas 1: La question demandait de vérifier un champ existant (bloodType)
        $bloodType = trim($_POST['bloodType'] ?? '');
        if (!empty($bloodType)) {
            $captchaValid = true;
            error_log("CAPTCHA validated via bloodType: $bloodType");
        } else {
            error_log("CAPTCHA failed: bloodType is empty");
        }
    } elseif (isset($_SESSION['captcha_answer']) && $_SESSION['captcha_answer'] !== null && $_SESSION['captcha_answer'] !== '') {
        // Cas 2: La question demandait une réponse textuelle/numérique spécifique
        $expectedAnswer = strtolower(trim($_SESSION['captcha_answer']));
        $userAnswerClean = strtolower(trim($userAnswer));
        
        if ($userAnswerClean === $expectedAnswer) {
            $captchaValid = true;
            error_log("CAPTCHA validated: user answer matches expected answer");
        } else {
            error_log("CAPTCHA failed: user answer '$userAnswerClean' does not match expected '$expectedAnswer'");
        }
    } else {
        error_log("CAPTCHA failed: session answer not set or empty");
    }
    
    // ACTION CLÉ : BLOCAGE IMMÉDIAT EN CAS D'ÉCHEC DU CAPTCHA
    if (!$captchaValid) {
        $error = "Veuillez répondre correctement à l'énigme de sécurité pour finaliser le don.";
        $_SESSION['captcha_error'] = $error; // Stocker l'erreur pour l'affichage
        $valide = false;
        
        // Régénérer une nouvelle question CAPTCHA
        $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture'];
        $captchaKey = array_rand($currentQuestions);
        $selectedCaptcha = $currentQuestions[$captchaKey];
        
        if (isset($selectedCaptcha['type'])) {
            $_SESSION['captcha_type_check'] = $selectedCaptcha['type'];
            $_SESSION['captcha_answer'] = null;
        } else {
            $_SESSION['captcha_type_check'] = null;
            $_SESSION['captcha_answer'] = strtolower($selectedCaptcha['answer']);
        }
        
        // Ne pas rediriger - laisser le formulaire s'afficher avec l'erreur
        // Le script continuera et affichera l'erreur sur la page
    }
    // =========================================================================================
    // VÉRIFICATION DU CAPTCHA AVANCÉ (CORRIGÉE ET SÉCURISÉE) - Fin
    // =========================================================================================

    // --- Si le script arrive ici, le CAPTCHA est validé, nous continuons les autres vérifications ---
    
    // Données spécifiques ARGENT
    $montant = 0;
    if ($donType === 'argent') {
        $montant = (float)($_POST['amount'] ?? 0.0);
        if ($montant <= 0) {
            $error = "Le montant du don doit être supérieur à zéro.";
            $valide = false;
        }
    }

    // Données spécifiques SANG
    $groupeSanguin = null;
    if ($donType === 'sang') {
        $groupeSanguin = $_POST['bloodType'] ?? null;
        if (empty($groupeSanguin)) {
            $error = "Veuillez sélectionner votre groupe sanguin.";
            $valide = false;
        }
    }
    
    // Données spécifiques NOURRITURE
    $foodTypes = $_POST['foodTypes'] ?? [];
    $detailsNourriture = null;
    if ($donType === 'nourriture') {
        if (empty($foodTypes)) {
            $error = "Veuillez sélectionner au moins un type de nourriture.";
            $valide = false;
        } else {
            $detailsNourriture = implode(', ', $foodTypes);
        }
    }

    // Only proceed if CAPTCHA is valid AND other validations pass
    if ($captchaValid && $valide) {
        // ID utilisateur from session
        $idUtilisateur = $_SESSION['user_id'] ?? 1;
        
        // Préparation des données pour le don
        $montantDonne = ($donType === 'argent') ? $montant : null;
        $methodePaiement = ($donType === 'argent') ? 'Carte bancaire' : null;
        $numeroCarteToken = ($donType === 'argent') ? 'TOKEN_' . time() . '_' . rand(1000, 9999) : null;
        
        if ($donType === 'sang') {
            $aMaladie = isset($_POST['hasDisease']) ? 1 : 0;
            $detailsMaladie = isset($_POST['diseaseDetails']) ? trim($_POST['diseaseDetails']) : null;
        } else {
            $aMaladie = 0;
            $detailsMaladie = null;
        }
        
        // Création de l'objet Don
        $don = new Don(
            null,                    // id_don (auto-généré)
            $idUtilisateur,          // id_utilisateur
            $associationId,          // id_association
            $donType,                // type_don
            'En attente',            // statut
            $montantDonne,           // montant_donne
            $methodePaiement,        // methode_paiement
            $numeroCarteToken,       // numero_carte_token
            $groupeSanguin,          // groupe_sanguin
            $aMaladie,               // a_maladie
            $detailsMaladie,         // details_maladie
            $detailsNourriture       // details_nourriture
        );

        // DEBUG: Afficher les données pour vérification
        error_log("Tentative d'ajout de don:");
        error_log("- Type: " . $donType);
        error_log("- Association ID: " . $associationId);
        error_log("- Utilisateur ID: " . $idUtilisateur);
        
        // Ajout du don
        error_log("Creating DonC instance");
        try {
            $donC = new DonC();
            error_log("Calling ajouterDon method");
            error_log("Don object: type=" . $don->getTypeDon() . ", user=" . $don->getIdUtilisateur() . ", assoc=" . $don->getIdAssociation());
            $result = $donC->ajouterDon($don);
            error_log("ajouterDon returned: " . ($result ? 'true' : 'false'));
        } catch (Exception $e) {
            error_log("Exception in ajouterDon: " . $e->getMessage());
            $result = false;
        }
        
        if ($result) {
            // Succès - Préparer les détails pour la confirmation
            $_SESSION['confirmation_details'] = [
                'type' => $typeLabel,
                'association' => $associationName,
                'pays' => $selectedCountry,
                'date' => date('d/m/Y H:i:s'),
                'valeur' => match($donType) {
                    'argent' => 'Montant : ' . $montant . ' €',
                    'sang' => 'Groupe sanguin : ' . ($groupeSanguin ?? 'N/A'),
                    'nourriture' => 'Type : ' . ($detailsNourriture ?? 'N/A'),
                    default => ''
                }
            ];
            
            // Facultatif : Vider les sessions du captcha après succès
            unset($_SESSION['captcha_type_check']);
            unset($_SESSION['captcha_answer']);
            
            // DEBUG
            error_log("Don ajouté avec succès, redirection vers step5.php");
            
            // Redirect to step5
            // Use routing format first, with direct URL as fallback
            $redirectUrl = getDonsUrl('step5');
            
            // Also prepare direct URL as fallback
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
            $scriptPath = str_replace('/views/php', '', $scriptPath);
            $scriptPath = str_replace('\\views\\php', '', $scriptPath);
            $directUrl = $protocol . '://' . $host . $scriptPath . '/views/php/step5.php';
            
            error_log("Don ajouté avec succès. Redirection vers: " . $redirectUrl);
            error_log("Direct URL fallback: " . $directUrl);
            
            // FORCE REDIRECT - Try header first, then JavaScript fallback
            error_log("Sending redirect to: " . $redirectUrl);
            
            // Clear all output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Try header redirect first
            if (!headers_sent()) {
                header("Location: " . $redirectUrl, true, 302);
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                exit();
            } else {
                // Headers already sent, use JavaScript redirect with fallback
                error_log("WARNING: Headers already sent, using JavaScript redirect");
                echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirection...</title>';
                echo '<script>';
                echo 'console.log("Redirecting to: ' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '");';
                echo 'var redirectUrl = "' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '";';
                echo 'var directUrl = "' . htmlspecialchars($directUrl, ENT_QUOTES) . '";';
                echo 'window.location.replace(redirectUrl);';
                echo 'setTimeout(function() { if (window.location.href.indexOf("step5") === -1 && window.location.href.indexOf("step4") !== -1) { window.location.replace(directUrl); } }, 2000);';
                echo '</script>';
                echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '"></noscript>';
                echo '</head><body>';
                echo '<p>Redirection en cours... Si vous n\'êtes pas redirigé, <a href="' . htmlspecialchars($redirectUrl, ENT_QUOTES) . '">cliquez ici</a>.</p>';
                echo '</body></html>';
                exit();
            }
        } else {
            $error = "❌ ERREUR : Échec de l'enregistrement du don dans la base de données.";
            error_log("Échec de l'ajout du don - result was false");
            error_log("Don object details: " . print_r([
                'id_utilisateur' => $don->getIdUtilisateur(),
                'id_association' => $don->getIdAssociation(),
                'type_don' => $don->getTypeDon(),
                'statut' => $don->getStatut()
            ], true));
        }
    } else {
        error_log("Form validation failed - captchaValid: " . ($captchaValid ? 'true' : 'false') . ", valide: " . ($valide ? 'true' : 'false'));
        if (!$captchaValid) {
            error_log("CAPTCHA validation failed");
        }
        if (!$valide) {
            error_log("Other validation failed - error: " . ($error ?? 'unknown'));
        }
    }
} else {
    error_log("Not a form submission or missing required POST fields");
    error_log("POST data: " . print_r($_POST, true));
}

// Réinitialiser le CAPTCHA seulement si nécessaire (après traitement POST et si pas déjà défini)
// Ne PAS régénérer si le CAPTCHA est déjà dans la session (pour éviter de changer la question après une validation réussie mais échec autre)
if (!isset($selectedCaptcha) || empty($selectedCaptcha['question']) || $selectedCaptcha['question'] === 'Question de sécurité') {
    // Vérifier si le CAPTCHA est déjà dans la session - si oui, récupérer la question correspondante
    if (isset($_SESSION['captcha_type_check']) || (isset($_SESSION['captcha_answer']) && $_SESSION['captcha_answer'] !== null)) {
        $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture'];
        foreach ($currentQuestions as $q) {
            if (isset($_SESSION['captcha_type_check']) && $_SESSION['captcha_type_check'] === 'bloodType' && isset($q['type']) && $q['type'] === 'bloodType') {
                $selectedCaptcha = $q;
                break;
            } elseif (!isset($_SESSION['captcha_type_check']) && isset($q['answer']) && strtolower($q['answer']) === $_SESSION['captcha_answer']) {
                $selectedCaptcha = $q;
                break;
            }
        }
    }
    
    // Si toujours pas trouvé, initialiser un nouveau CAPTCHA
    if (!isset($selectedCaptcha) || empty($selectedCaptcha['question'])) {
        $currentQuestions = $captchaQuestions[$donType] ?? $captchaQuestions['nourriture'];
        $captchaKey = array_rand($currentQuestions);
        $selectedCaptcha = $currentQuestions[$captchaKey];
        
        if (isset($selectedCaptcha['type'])) {
            $_SESSION['captcha_type_check'] = $selectedCaptcha['type'];
            $_SESSION['captcha_answer'] = null;
        } else {
            $_SESSION['captcha_type_check'] = null;
            $_SESSION['captcha_answer'] = strtolower($selectedCaptcha['answer']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Étape 4 - Finalisez votre don - SOLIDA</title>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/templatemo.css">
    <link rel="stylesheet" href="../css/solida.css"> 
    <link rel="stylesheet" href="../css/event.css"> 
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../css/fontawesome.min.css">
    
    <style>
        /* Required field indicators */
        .form-label .text-danger {
            font-weight: bold;
        }
        
        /* Invalid field styling */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6 .4.4.4-.4m0 4.8-.4-.4-.4.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            padding-right: calc(1.5em + 0.75rem);
        }
        
        .form-control.is-invalid:focus,
        .form-select.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
        }
        
        /* Error message styling */
        .form-text.text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Required field highlight */
        .form-control:required:not(:disabled),
        .form-select:required:not(:disabled) {
            border-left: 3px solid #ffc107;
        }
        
        .form-control:required:not(:disabled):valid,
        .form-select:required:not(:disabled):valid {
            border-left-color: #28a745;
        }
    </style> 
</head>
<body>
    <?php include '../front_office/includes/navbar.php'; ?>

    <div id="step-form" class="container-fluid py-5">
        <img src="https://placehold.co/1200x300/e8f5e9/28a745?text=Finalisez+votre+don" class="img-fluid rounded mb-4" alt="Formulaire">

        <div class="container">
            <div class="text-center mb-4">
                <h2>Finalisez votre don</h2>
                <p class="text-muted mb-4">Veuillez remplir les informations ci-dessous pour finaliser votre don</p>
            </div>
            
            <!-- Informations du don -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 col-lg-8">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations du don</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Type de don -->
                                <div class="col-md-4">
                                    <label for="don_type_display" class="form-label">
                                        <i class="fas fa-heart text-danger"></i> Type de don <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="don_type_display" name="don_type_display" required>
                                        <option value="argent" <?= ($donType === 'argent' ? 'selected' : ''); ?>>Argent</option>
                                        <option value="sang" <?= ($donType === 'sang' ? 'selected' : ''); ?>>Sang</option>
                                        <option value="nourriture" <?= ($donType === 'nourriture' ? 'selected' : ''); ?>>Nourriture</option>
                                    </select>
                                    <div class="form-text">Type de don sélectionné</div>
                                </div>
                                
                                <!-- Association -->
                                <div class="col-md-4">
                                    <label for="association_name_display" class="form-label">
                                        <i class="fas fa-handshake text-primary"></i> Association <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="association_name_display" 
                                           name="association_name_display" 
                                           value="<?= htmlspecialchars($associationName); ?>" 
                                           required
                                           placeholder="Nom de l'association">
                                    <input type="hidden" name="association_name" value="<?= htmlspecialchars($associationName); ?>">
                                    <input type="hidden" name="association_id" value="<?= htmlspecialchars($associationId); ?>">
                                    <div class="form-text">Association bénéficiaire</div>
                                </div>
                                
                                <!-- Pays -->
                                <div class="col-md-4">
                                    <label for="country_display" class="form-label">
                                        <i class="fas fa-globe text-success"></i> Pays <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="country_display" 
                                           name="country_display" 
                                           value="<?= htmlspecialchars($selectedCountry); ?>" 
                                           required
                                           placeholder="Pays">
                                    <input type="hidden" name="country" value="<?= htmlspecialchars($selectedCountry); ?>">
                                    <div class="form-text">Pays de destination</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form method="POST" action="" id="donationForm">
                        <!-- Hidden field to ensure POST is detected -->
                        <input type="hidden" name="form_submitted" value="1">
                        <!-- Hidden fields will be updated by JavaScript from the display inputs -->
                        <input type="hidden" name="don_type" id="don_type_hidden" value="<?= htmlspecialchars($donType); ?>">
                        <input type="hidden" name="association_id" id="association_id_hidden" value="<?= htmlspecialchars($associationId); ?>">
                        <input type="hidden" name="association_name" id="association_name_hidden" value="<?= htmlspecialchars($associationName); ?>">
                        <input type="hidden" name="country" id="country_hidden" value="<?= htmlspecialchars($selectedCountry); ?>">
                        
                        <!-- CHAMP ARGENT -->
                        <div class="mb-4" id="amount-field" style="display: <?= ($donType === 'argent' ? 'block' : 'none'); ?>;">
                            <label for="amount" class="form-label">
                                <i class="fas fa-euro-sign"></i> Montant du don (€) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="1" class="form-control" 
                                       id="amount" name="amount" 
                                       value="<?= $donType === 'argent' ? '10.00' : '' ?>"
                                       placeholder="Entrez le montant">
                                <span class="input-group-text">€</span>
                            </div>
                            <div class="form-text">Minimum 1€. Votre don sera traité de manière sécurisée.</div>
                        </div>

                        <!-- CHAMP SANG -->
                        <div class="mb-4" id="blood-type-field" style="display: <?= ($donType === 'sang' ? 'block' : 'none'); ?>;">
                            <label for="bloodType" class="form-label">
                                <i class="fas fa-tint text-danger"></i> Groupe Sanguin <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="bloodType" name="bloodType">
                                <option value="">-- Sélectionnez votre groupe --</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="hasDisease" name="hasDisease" value="1">
                                <label class="form-check-label" for="hasDisease">
                                    <i class="fas fa-exclamation-circle"></i> J'ai été diagnostiqué(e) avec une maladie chronique/grave.
                                </label>
                            </div>
                            
                            <div class="mt-2" id="diseaseDetailsField" style="display: none;">
                                <label for="diseaseDetails" class="form-label">
                                    <i class="fas fa-info-circle"></i> Détails médicaux (Optionnel)
                                </label>
                                <textarea class="form-control" id="diseaseDetails" name="diseaseDetails" 
                                          rows="3" placeholder="Précisez si nécessaire..."></textarea>
                                <div class="form-text">Ces informations restent confidentielles.</div>
                            </div>
                        </div>

                        <!-- CHAMP NOURRITURE -->
                        <div class="mb-4" id="food-type-field" style="display: <?= ($donType === 'nourriture' ? 'block' : 'none'); ?>;">
                            <label class="form-label">
                                <i class="fas fa-utensils text-warning"></i> Type de nourriture <span class="text-danger">*</span>
                            </label>
                            <div class="border rounded p-3" id="foodTypesContainer" data-required="true">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="food1" name="foodTypes[]" value="Conserves">
                                    <label class="form-check-label" for="food1">
                                        <i class="fas fa-can"></i> Conserves (boîtes de conserve)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="food2" name="foodTypes[]" value="Pates/Riz">
                                    <label class="form-check-label" for="food2">
                                        <i class="fas fa-wheat-alt"></i> Pâtes/Riz/Céréales
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="food3" name="foodTypes[]" value="Eau">
                                    <label class="form-check-label" for="food3">
                                        <i class="fas fa-tint"></i> Eau en bouteille
                                    </label>
                                </div>
                            </div>
                            <div class="form-text mt-2 text-danger" id="foodTypesError" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i> Veuillez sélectionner au moins un type de nourriture.
                            </div>
                            <div class="form-text mt-2">Cochez au moins un type de nourriture que vous souhaitez donner.</div>
                        </div>

                        <!-- CAPTCHA SECTION -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-secondary">Sécurité : Énigme Anti-Robot</h5>
                                
                                <?php if (!empty($captchaError)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($captchaError); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="card p-3 bg-light border-warning">
                                    <label for="captcha_answer" class="form-label">
                                        <strong>Question de sécurité :</strong> <?= htmlspecialchars($selectedCaptcha['question']); ?> <span class="text-danger">*</span>
                                    </label>
                                    
                                    <?php if (!isset($selectedCaptcha['type'])): ?>
                                        <input type="text" 
                                               class="form-control" 
                                               id="captcha_answer" 
                                               name="captcha_answer" 
                                               required
                                               placeholder="Votre réponse ici (mot ou chiffre)"
                                               autocomplete="off"
                                               value=""
                                               aria-required="true">
                                        <div class="form-text text-danger mt-1" id="captchaError" style="display: none;">
                                            <i class="fas fa-exclamation-circle"></i> Veuillez répondre à la question de sécurité.
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="captcha_answer" value="">
                                        <p class="mb-0 text-muted small">
                                            * Cette question est validée par la réponse fournie dans le champ principal de don (groupe sanguin).
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- BOUTONS -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-5">
                            <a href="<?= getDonsUrl('association2', ['id' => $associationId]); ?>" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <a href="historique.php" class="btn btn-info btn-lg">
                                <i class="fas fa-history"></i> Voir mes dons passés
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check-circle"></i> Valider le don
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../front_office/includes/footer.php'; ?>
    
    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion de l'affichage des détails de maladie
        document.addEventListener('DOMContentLoaded', function() {
            const hasDisease = document.getElementById('hasDisease');
            const detailsField = document.getElementById('diseaseDetailsField');
            
            if (hasDisease && detailsField) {
                // Événement de changement
                hasDisease.addEventListener('change', function() {
                    detailsField.style.display = this.checked ? 'block' : 'none';
                });
                
                // Initialiser l'état
                detailsField.style.display = hasDisease.checked ? 'block' : 'none';
            }
            
            // Sync display inputs with hidden fields
            const donTypeDisplay = document.getElementById('don_type_display');
            const associationNameDisplay = document.getElementById('association_name_display');
            const countryDisplay = document.getElementById('country_display');
            
            const donTypeHidden = document.getElementById('don_type_hidden');
            const associationNameHidden = document.getElementById('association_name_hidden');
            const countryHidden = document.getElementById('country_hidden');
            
            // Function to update form fields visibility and enable/disable
            function updateFormFieldsVisibility(type) {
                const amountField = document.getElementById('amount-field');
                const bloodField = document.getElementById('blood-type-field');
                const foodField = document.getElementById('food-type-field');
                
                const amountInput = document.getElementById('amount');
                const bloodTypeSelect = document.getElementById('bloodType');
                const foodCheckboxes = document.querySelectorAll('input[name="foodTypes[]"]');
                const hasDiseaseCheckbox = document.getElementById('hasDisease');
                const diseaseDetailsTextarea = document.getElementById('diseaseDetails');
                
                // Hide all fields first and disable them
                if (amountField) {
                    amountField.style.display = 'none';
                    if (amountInput) {
                        amountInput.disabled = true;
                        amountInput.removeAttribute('required');
                    }
                }
                
                if (bloodField) {
                    bloodField.style.display = 'none';
                    if (bloodTypeSelect) {
                        bloodTypeSelect.disabled = true;
                        bloodTypeSelect.removeAttribute('required');
                    }
                    if (hasDiseaseCheckbox) hasDiseaseCheckbox.disabled = true;
                    if (diseaseDetailsTextarea) diseaseDetailsTextarea.disabled = true;
                }
                
                if (foodField) {
                    foodField.style.display = 'none';
                    foodCheckboxes.forEach(checkbox => {
                        checkbox.disabled = true;
                    });
                }
                
                // Show and enable relevant field
                if (type === 'argent' && amountField && amountInput) {
                    amountField.style.display = 'block';
                    amountInput.disabled = false;
                    amountInput.setAttribute('required', 'required');
                } else if (type === 'sang' && bloodField && bloodTypeSelect) {
                    bloodField.style.display = 'block';
                    bloodTypeSelect.disabled = false;
                    bloodTypeSelect.setAttribute('required', 'required');
                    if (hasDiseaseCheckbox) hasDiseaseCheckbox.disabled = false;
                    if (diseaseDetailsTextarea) diseaseDetailsTextarea.disabled = false;
                } else if (type === 'nourriture' && foodField) {
                    foodField.style.display = 'block';
                    foodCheckboxes.forEach(checkbox => {
                        checkbox.disabled = false;
                    });
                }
            }
            
            // Update hidden fields when display inputs change
            if (donTypeDisplay && donTypeHidden) {
                donTypeDisplay.addEventListener('change', function() {
                    donTypeHidden.value = this.value;
                    updateFormFieldsVisibility(this.value);
                });
            }
            
            if (associationNameDisplay && associationNameHidden) {
                associationNameDisplay.addEventListener('input', function() {
                    associationNameHidden.value = this.value;
                });
            }
            
            if (countryDisplay && countryHidden) {
                countryDisplay.addEventListener('input', function() {
                    countryHidden.value = this.value;
                });
            }
            
            // Force display of form fields based on initial donType
            const donType = '<?= $donType ?>';
            if (donType) {
                updateFormFieldsVisibility(donType);
            } else {
                // If no donType, ensure all fields are enabled (they'll be shown/hidden by type selection)
                const amountInput = document.getElementById('amount');
                const bloodTypeSelect = document.getElementById('bloodType');
                const foodCheckboxes = document.querySelectorAll('input[name="foodTypes[]"]');
                const hasDiseaseCheckbox = document.getElementById('hasDisease');
                const diseaseDetailsTextarea = document.getElementById('diseaseDetails');
                
                // Remove disabled attribute from all fields
                if (amountInput) amountInput.removeAttribute('disabled');
                if (bloodTypeSelect) bloodTypeSelect.removeAttribute('disabled');
                if (hasDiseaseCheckbox) hasDiseaseCheckbox.removeAttribute('disabled');
                if (diseaseDetailsTextarea) diseaseDetailsTextarea.removeAttribute('disabled');
                foodCheckboxes.forEach(checkbox => checkbox.removeAttribute('disabled'));
            }
            
            // Real-time validation feedback
            function validateForm() {
                // Get current donation type from display or hidden field
                let type = '';
                if (donTypeDisplay) {
                    type = donTypeDisplay.value;
                } else if (donTypeHidden) {
                    type = donTypeHidden.value;
                } else {
                    type = donType || '';
                }
                
                console.log('Validating form for type:', type);
                let isValid = true;
                
                // Validate display fields
                if (associationNameDisplay && !associationNameDisplay.value.trim()) {
                    associationNameDisplay.classList.add('is-invalid');
                    isValid = false;
                } else if (associationNameDisplay) {
                    associationNameDisplay.classList.remove('is-invalid');
                }
                
                if (countryDisplay && !countryDisplay.value.trim()) {
                    countryDisplay.classList.add('is-invalid');
                    isValid = false;
                } else if (countryDisplay) {
                    countryDisplay.classList.remove('is-invalid');
                }
                
                // Reset error messages
                document.getElementById('foodTypesError')?.style.setProperty('display', 'none');
                document.getElementById('captchaError')?.style.setProperty('display', 'none');
                
                if (type === 'argent') {
                    const amount = document.getElementById('amount');
                    if (amount && (!amount.value || parseFloat(amount.value) <= 0)) {
                        amount.classList.add('is-invalid');
                        isValid = false;
                    } else if (amount) {
                        amount.classList.remove('is-invalid');
                    }
                } else if (type === 'sang') {
                    const bloodType = document.getElementById('bloodType');
                    if (bloodType && !bloodType.value) {
                        bloodType.classList.add('is-invalid');
                        isValid = false;
                    } else if (bloodType) {
                        bloodType.classList.remove('is-invalid');
                    }
                } else if (type === 'nourriture') {
                    const checkboxes = document.querySelectorAll('input[name="foodTypes[]"]:checked');
                    if (checkboxes.length === 0) {
                        const errorDiv = document.getElementById('foodTypesError');
                        if (errorDiv) errorDiv.style.display = 'block';
                        isValid = false;
                    }
                }
                
                // Vérifier le CAPTCHA si nécessaire
                const captchaInput = document.getElementById('captcha_answer');
                if (captchaInput) {
                    // Check if CAPTCHA is required (visible and has required attribute)
                    const isCaptchaRequired = captchaInput.hasAttribute('required') || 
                                              (captchaInput.offsetParent !== null && captchaInput.type !== 'hidden');
                    
                    if (isCaptchaRequired && !captchaInput.value.trim()) {
                        captchaInput.classList.add('is-invalid');
                        const errorDiv = document.getElementById('captchaError');
                        if (errorDiv) errorDiv.style.display = 'block';
                        isValid = false;
                        console.log('CAPTCHA validation failed');
                    } else {
                        captchaInput.classList.remove('is-invalid');
                    }
                }
                
                console.log('Final validation result:', isValid);
                return isValid;
            }
            
            // Add real-time validation on input change
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                amountInput.addEventListener('input', validateForm);
            }
            
            const bloodTypeSelect = document.getElementById('bloodType');
            if (bloodTypeSelect) {
                bloodTypeSelect.addEventListener('change', validateForm);
            }
            
            const foodCheckboxes = document.querySelectorAll('input[name="foodTypes[]"]');
            foodCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', validateForm);
            });
            
            const captchaInput = document.getElementById('captcha_answer');
            if (captchaInput) {
                captchaInput.addEventListener('input', validateForm);
            }
            
            // Validation du formulaire
            const donationForm = document.getElementById('donationForm');
            if (donationForm) {
                donationForm.addEventListener('submit', function(e) {
                    console.log('Form submission started');
                    
                    // Get current donation type
                    const currentType = donTypeDisplay ? donTypeDisplay.value : donType;
                    console.log('Current donation type:', currentType);
                    
                    // Validate form
                    const isValid = validateForm();
                    console.log('Form validation result:', isValid);
                    
                    if (!isValid) {
                        e.preventDefault();
                        console.log('Form validation failed, preventing submission');
                        
                        // Show alert with validation errors
                        alert('Veuillez remplir tous les champs requis correctement.');
                        
                        // Scroll to first error
                        const firstError = document.querySelector('.is-invalid') || document.querySelector('[id$="Error"][style*="display: block"]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                    } else {
                        console.log('Form validation passed, submitting...');
                        
                        // Ensure hidden fields are updated before submission
                        if (donTypeDisplay && donTypeHidden) {
                            donTypeHidden.value = donTypeDisplay.value;
                        }
                        if (associationNameDisplay && associationNameHidden) {
                            associationNameHidden.value = associationNameDisplay.value;
                        }
                        if (countryDisplay && countryHidden) {
                            countryHidden.value = countryDisplay.value;
                        }
                        
                        // Afficher un message de chargement
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...';
                            submitBtn.disabled = true;
                        }
                        
                        // Allow form to submit
                        return true;
                    }
                });
            } else {
                console.error('Form with id "donationForm" not found!');
            }
        });
    </script>
</body>
</html>
