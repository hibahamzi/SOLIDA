<?php

require_once __DIR__ . '/../../controllers/ReclamationController.php';

// 🔧 Afficher les erreurs (pour debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialisation des variables
$reclamationController = new ReclamationController();
$message_soumission = '';
$reclamation = null;
$id = null;

// Variables spécifiques à la réponse
$new_statut = '';
$response_detaillee = '';

// ------------------------------
// FONCTION D'ENVOI D'EMAIL AMÉLIORÉE
// ------------------------------
function sendResponseEmail($recipient, $subject, $body, $senderEmail = 'noreply@solida.tn') {
    // Vérifier que la fonction mail() existe
    if (!function_exists('mail')) {
        error_log("La fonction mail() n'est pas disponible sur ce serveur");
        return false;
    }
    
    // Vérifier la configuration SMTP
    $smtp = ini_get('SMTP');
    $sendmail_from = ini_get('sendmail_from');
    
    // Si pas de configuration SMTP, utiliser l'email par défaut
    if (empty($sendmail_from)) {
        $sendmail_from = $senderEmail;
    }
    
    // Configuration améliorée des headers
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "From: " . $sendmail_from;
    $headers[] = "Reply-To: " . $senderEmail;
    $headers[] = "X-Mailer: PHP/" . phpversion();
    $headers[] = "X-Priority: 3";
    
    // Encodage du sujet pour UTF-8
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    
    // Tentative d'envoi avec gestion d'erreurs
    try {
        // Méthode 1 : Utiliser la fonction mail() native
        $result = @mail($recipient, $encoded_subject, $body, implode("\r\n", $headers));
        
        // Si mail() échoue, essayer avec des paramètres supplémentaires pour XAMPP
        if (!$result && !empty($sendmail_from)) {
            $additional_params = "-f" . $sendmail_from;
            $result = @mail($recipient, $encoded_subject, $body, implode("\r\n", $headers), $additional_params);
        }
        
        // Log pour débogage
        if ($result) {
            error_log("Email envoyé avec succès à: " . $recipient);
        } else {
            $error_msg = "Erreur envoi email à: " . $recipient;
            $error_msg .= " - SMTP: " . ($smtp ?: 'Non configuré');
            $error_msg .= " - From: " . ($sendmail_from ?: 'Non configuré');
            error_log($error_msg);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Exception lors de l'envoi d'email: " . $e->getMessage());
        return false;
    }
}

// ------------------------------
// 1. CHARGEMENT DES DONNÉES EXISTANTES (GET)
// LOGIQUE: Récupère la réclamation et la réponse existante (si elle existe)
// ------------------------------
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $reclamation = $reclamationController->getReclamationById($id);

    if (!$reclamation) {
        // Redirection si la réclamation n'existe pas
        header('Location: BackofficeReclamations.php');
        exit;
    }
    
    // Initialisation des champs de réponse à partir des données existantes (si déjà répondu)
    // Récupérer la réponse depuis la table reponadmin
    $existing_response = $reclamationController->getResponseByReclamationId($id);
    $response_detaillee = $existing_response['reponse'] ?? '';
    $new_statut = $reclamation['statut'] ?? 'Nouveau';

} else {
    // Redirection si ID manquant
    header('Location: BackofficeReclamations.php');
    exit;
}

// ------------------------------
// 2. GESTION DE LA SOUMISSION (POST) - Envoi de la Réponse
// LOGIQUE: 
// - Valide la réponse (min 10 caractères)
// - Appelle addAdminResponse() qui enregistre dans reponadmin
// - Envoie l'email au client
// - Redirige vers la liste avec message de succès
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $new_statut = $_POST['statut'] ?? 'En Cours';
    $response_detaillee = trim($_POST['response_detaillee'] ?? '');
    
    // Validation de la réponse
    if (empty($response_detaillee) || strlen($response_detaillee) < 10) {
        $message_soumission = '<div class="error-message">❌ La réponse doit contenir au moins 10 caractères.</div>';
    } else {
        try {
            // 🚀 Appel à la méthode du Contrôleur pour ajouter une réponse dans reponadmin
            $success = $reclamationController->addAdminResponse(
                $id, 
                $response_detaillee, 
                $new_statut,
                date('Y-m-d H:i:s') // Date de réponse
            );

            if ($success) {
                // Recharger les données de la réclamation pour avoir l'email à jour
                $reclamation = $reclamationController->getReclamationById($id);
                
                // Envoi de l'email au client - Essayer plusieurs champs possibles
                $recipient_email = '';
                
                // Essayer différents noms de colonnes possibles
                if (!empty($reclamation['email'])) {
                    $recipient_email = $reclamation['email'];
                } elseif (!empty($reclamation['client_email'])) {
                    $recipient_email = $reclamation['client_email'];
                } elseif (!empty($reclamation['Email'])) {
                    $recipient_email = $reclamation['Email'];
                } elseif (!empty($reclamation['EMAIL'])) {
                    $recipient_email = $reclamation['EMAIL'];
                }
                
                // Nettoyer l'email (supprimer les espaces)
                $recipient_email = trim($recipient_email);
                
                // Log pour débogage (peut être désactivé en production)
                error_log("Tentative d'envoi email à: " . $recipient_email . " pour réclamation #" . $id);
                
                if (!empty($recipient_email) && filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                    $subject = "Réponse à votre réclamation #{$id} - Statut: {$new_statut}";
                    $client_nom = $reclamation['nom'] ?? $reclamation['client_nom'] ?? '';
                    $client_prenom = $reclamation['prenom'] ?? $reclamation['client_prenom'] ?? '';
                    $client_name = trim($client_nom . ' ' . $client_prenom) ?: 'Cher Client';
                    
                    $email_body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                    </head>
                    <body style='font-family: Arial, sans-serif; background-color: #f7f7f7; padding: 20px; margin: 0;'>
                        <div style='max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-top: 5px solid #4CAF50;'>
                            <h2 style='color: #388E3C; margin-top: 0; font-size: 24px;'>Mise à Jour de votre Réclamation #{$id}</h2>
                            <p style='font-size:1.1em; color: #333; margin: 15px 0;'>Bonjour <strong>" . htmlspecialchars($client_name) . "</strong>,</p>
                            <p style='color: #666; line-height: 1.6; margin: 15px 0;'>Nous vous informons que votre réclamation a été mise à jour. Voici la réponse de l'administration :</p>
                            
                            <div style='background-color: #E8F5E9; padding: 15px; border-radius: 8px; border-left: 5px solid #4CAF50; margin: 20px 0;'>
                                <h3 style='margin-top: 0; color: #388E3C; font-size: 16px; font-weight: 600;'>Nouveau Statut:</h3>
                                <p style='font-weight: bold; color: " . ($new_statut == 'Traitée' ? '#388E3C' : ($new_statut == 'Rejetée' ? '#C62828' : '#FF6F00')) . "; font-size: 18px; margin: 5px 0;'>{$new_statut}</p>
                            </div>
                            
                            <div style='background-color: #F5F5F5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                                <h3 style='margin-top: 0; color: #388E3C; font-size: 16px; font-weight: 600;'>Réponse Détaillée :</h3>
                                <p style='white-space: pre-wrap; color: #333; line-height: 1.6; margin: 10px 0;'>" . nl2br(htmlspecialchars($response_detaillee)) . "</p>
                            </div>
                            
                            <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>
                            
                            <p style='margin-top: 30px; font-size: 0.9em; color: #777; text-align: center;'>
                                <strong>Service de Gestion des Réclamations</strong><br>
                                <small>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</small>
                            </p>
                        </div>
                    </body>
                    </html>";
                    
                    // Tentative d'envoi d'email
                    $email_sent = sendResponseEmail($recipient_email, $subject, $email_body);
                    
                    // Si l'envoi échoue, on peut quand même considérer que c'est un succès partiel
                    // car la réponse est enregistrée dans la base de données
                    $redirect_message = $email_sent ? 'responded_and_emailed' : 'responded_no_email';
                    header('Location: BackofficeReclamations.php?success=' . $redirect_message . '&id=' . $id);
                } else {
                    // Pas d'email valide ou email manquant
                    // Log pour débogage
                    error_log("Email invalide ou manquant pour réclamation #{$id}: " . $recipient_email);
                    header('Location: BackofficeReclamations.php?success=responded_no_email&id=' . $id);
                }
                exit;
            } else {
                $message_soumission = '<div class="error-message">❌ Erreur lors de l\'enregistrement de la réponse.</div>';
            }
        } catch (Exception $e) {
            $message_soumission = '<div class="error-message">❌ Erreur Serveur: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre à la Réclamation #<?php echo htmlspecialchars($id); ?></title>
    <style>
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
            padding: 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container { 
            width: 100%;
            max-width: 900px;
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

        input[type="text"]:disabled, input[type="email"]:disabled, textarea:disabled, select:disabled {
            background-color: #F5F5F5;
            color: #666;
            border-color: #E0E0E0;
            cursor: default;
        }
        
        input[type="text"], input[type="email"], textarea, select { 
            padding: 12px; 
            border: 1px solid var(--color-border); 
            border-radius: 6px; 
            width: 100%; 
            box-sizing: border-box;
        }

        .submit-btn { 
            width: 100%; background-color: var(--color-primary); color: white; 
            padding: 15px 20px; border: none; border-radius: 6px; 
            font-size: 1.1rem; font-weight: bold; cursor: pointer; 
            margin-top: 30px; display: flex; justify-content: center; 
            align-items: center; transition: background-color 0.3s; 
        }
        .submit-btn:hover { background-color: var(--color-dark); }
        .submit-btn span { margin-right: 10px; }

        .error-message { 
            background-color: #FFCDD2; color: var(--color-error); padding: 15px; 
            border-radius: 6px; margin-bottom: 20px; border: 1px solid #EF9A9A; 
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .status-Nouveau { background-color: #BBDEFB; color: #1565C0; }
        .status-EnCours { background-color: #FFF9C4; color: #FF6F00; }
        .status-Traitée { background-color: #C8E6C9; color: #388E3C; }
        .status-Rejetée { background-color: #FFCDD2; color: #C62828; }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: var(--color-dark);
            text-decoration: underline;
        }

        @media (max-width: 768px) { 
            .form-row { flex-direction: column; gap: 0; } 
            .form-container { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2><span>&#x1F5E3;</span> Répondre à la Réclamation #<?php echo htmlspecialchars($id); ?></h2>
    
    <?php echo $message_soumission; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <h3>Détails du Demandeur</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom Complet</label>
                <input type="text" id="nom" value="<?php echo htmlspecialchars(($reclamation['nom'] ?? $reclamation['client_nom'] ?? '') . ' ' . ($reclamation['prenom'] ?? $reclamation['client_prenom'] ?? '')); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="email">Contact</label>
                <input type="text" id="email" value="<?php echo htmlspecialchars(($reclamation['email'] ?? $reclamation['client_email'] ?? '') . ' / ' . ($reclamation['telephone'] ?? $reclamation['client_telephone'] ?? '')); ?>" disabled>
            </div>
        </div>

        <h3>Localisation et Statut Actuel</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="location">Localisation (Gouv./Dél.)</label>
                <input type="text" id="location" value="<?php echo htmlspecialchars($reclamation['gouvernorat'] . ' - ' . $reclamation['delegation']); ?>" disabled>
            </div>
             <div class="form-group">
                <label for="date_demande">Date et Priorité</label>
                <input type="text" id="date_demande" value="<?php echo htmlspecialchars(($reclamation['date'] ?? $reclamation['date_creation'] ?? '') . ' / Priorité: ' . ($reclamation['priorite'] ?? '')); ?>" disabled>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description_detaillee">Description du Problème</label>
            <textarea id="description_detaillee" rows="4" disabled><?php echo htmlspecialchars($reclamation['description_detaillee']); ?></textarea>
        </div>

        <p style="text-align: right; margin-top: 10px;">
            Statut actuel: 
            <span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($reclamation['statut'])); ?>">
                <?php echo htmlspecialchars($reclamation['statut']); ?>
            </span>
        </p>

        <hr style="border: 0; height: 1px; background-color: #ccc; margin: 30px 0;">

        <h3>Réponse et Action de l'Administration</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="statut">Nouveau Statut de la Réclamation *</label>
                <select id="statut" name="statut" required>
                    <?php
                    $statuts = ['Nouveau', 'En Cours', 'Traitée', 'Rejetée'];
                    foreach ($statuts as $statut) {
                        $selected = ($statut == $new_statut) ? 'selected' : '';
                        echo "<option value='{$statut}' {$selected}>{$statut}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                </div>
        </div>

        <div class="form-group">
            <label for="response_detaillee">Réponse Détaillée à l'Utilisateur *</label>
            <textarea id="response_detaillee" name="response_detaillee" rows="8" required placeholder="Saisissez ici la réponse officielle à envoyer au citoyen et les mesures prises."><?php echo htmlspecialchars($response_detaillee); ?></textarea>
        </div>

        <button type="submit" class="submit-btn">
            <span>&#x270D;</span> Valider et Envoyer la Réponse
        </button>
        
        <a href="BackofficeReclamations.php" class="back-link">← Retour à la liste</a>
    </form>
</div>
</body>
</html>