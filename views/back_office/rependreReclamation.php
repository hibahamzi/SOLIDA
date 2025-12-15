<?php

require_once __DIR__ . '/../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

require_once __DIR__ . '/../../controllers/ReclamationController.php';

// 🔧 Afficher les erreurs (pour debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// CHARGEMENT DE PHPMailer (IMPORTANT : DOIT ÊTRE ICI)
// ============================================
// Définis le chemin ABSOLU vers PHPMailer
$phpmailerPath = __DIR__ . '/../../lib/PHPMailer/';

// Vérifie si PHPMailer existe
if (!file_exists($phpmailerPath . 'src/PHPMailer.php')) {
    die("❌ ERREUR: PHPMailer n'est pas installé. 
         Télécharge-le depuis: https://github.com/PHPMailer/PHPMailer
         Et place-le dans: " . $phpmailerPath);
}

// Charge PHPMailer
require_once $phpmailerPath . 'src/PHPMailer.php';
require_once $phpmailerPath . 'src/SMTP.php';
require_once $phpmailerPath . 'src/Exception.php';

// MAINTENANT tu peux utiliser 'use'
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialisation des variables
$reclamationController = new ReclamationController($pdo);
$message_soumission = '';
$reclamation = null;
$id = null;

// Variables spécifiques à la réponse
$new_statut = '';
$response_detaillee = '';

// Variable pour l'erreur de saisie de la réponse
$error_response_detaillee = ''; 

// ============================================
// FONCTION D'ENVOI D'EMAIL (CORRIGÉE)
// ============================================
function sendComplaintEmail($userEmail, $userName, $subject, $htmlBody) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings (MÊME CONFIGURATION QUE POUR LES ÉVÉNEMENTS)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'boubakriines11@gmail.com';
        $mail->Password = 'epkp tebq xnpf ibah';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0; // Mettre à 2 pour déboguer
        
        // Recipients
        $mail->setFrom('boubakriines11@gmail.com', 'SOLIDA - Support Réclamations');
        $mail->addAddress($userEmail, $userName);
        $mail->addReplyTo('boubakriines11@gmail.com', 'SOLIDA Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        
        // Alternative plain text version
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        
        // Envoi
        if ($mail->send()) {
            error_log("✅ Email envoyé pour réclamation à: {$userEmail}");
            return true;
        } else {
            error_log("❌ Échec PHPMailer pour réclamation: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("❌ Exception email réclamation: " . $e->getMessage());
        return false;
    }
}

// ============================================
// 1. CHARGEMENT DES DONNÉES EXISTANTES (GET)
// ============================================
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $reclamation = $reclamationController->getReclamationById($id);

    if (!$reclamation) {
        // Redirection si la réclamation n'existe pas
        header('Location: BackofficeReclamations.php');
        exit;
    }
    
    // Initialisation des champs de réponse à partir des données existantes
    $existing_response = $reclamationController->getResponseByReclamationId($id);
    
    $response_detaillee = $existing_response['reponse'] ?? '';
    $new_statut = $reclamation['statut'] ?? 'Nouveau';

} else {
    // Redirection si ID manquant
    header('Location: BackofficeReclamations.php');
    exit;
}

// ============================================
// 2. GESTION DE LA SOUMISSION (POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $new_statut = $_POST['statut'] ?? 'En Cours';
    $response_detaillee = trim($_POST['response_detaillee'] ?? '');
    
    // Contrôle de Saisie
    $validation_ok = true;
    
    if (empty($response_detaillee)) {
        $error_response_detaillee = "❌ La réponse est obligatoire.";
        $validation_ok = false;
    } elseif (strlen($response_detaillee) < 10) {
        $error_response_detaillee = "❌ La réponse doit contenir au moins 10 caractères.";
        $validation_ok = false;
    } 

    if ($validation_ok) {
        try {
            // 🚀 Enregistre la réponse
            $success = $reclamationController->addAdminResponse(
                $id, 
                $response_detaillee, 
                $new_statut,
                date('Y-m-d H:i:s')
            );

            if ($success) {
                // Recharge les données
                $reclamation = $reclamationController->getReclamationById($id);
                
                // --- ENVOI AUTOMATIQUE D'EMAIL --- 
                $recipient_email = trim($reclamation['email'] ?? '');
                $recipient_name = trim(($reclamation['nom'] ?? '') . ' ' . ($reclamation['prenom'] ?? 'Client'));
                
                if (!empty($recipient_email) && filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                    $subject = "✅ Réponse à votre réclamation #{$id} - SOLIDA";
                    
                    // Génère le template
                    $htmlBody = generateComplaintEmailTemplate(
                        $recipient_name,
                        $id,
                        $reclamation['description_detaillee'] ?? '',
                        $response_detaillee,
                        $new_statut
                    );
                    
                    // Envoie l'email
                    $email_sent = sendComplaintEmail($recipient_email, $recipient_name, $subject, $htmlBody);
                    
                    $redirect_message = $email_sent ? 'responded_and_emailed' : 'responded_no_email';
                    error_log("📧 Résultat réclamation #{$id}: " . ($email_sent ? "EMAIL ENVOYÉ" : "EMAIL ÉCHOUÉ"));
                    
                } else {
                    $redirect_message = 'responded_no_email';
                    error_log("❌ Email invalide réclamation #{$id}: " . $recipient_email);
                }
                
                // Redirection
                header('Location: BackofficeReclamations.php?success=responded_and_emailed');
                exit;
                
            } else {
                $message_soumission = '<div class="error-message">❌ Erreur lors de l\'enregistrement de la réponse.</div>';
            }
        } catch (Exception $e) {
            $message_soumission = '<div class="error-message">❌ Erreur Serveur: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// ============================================
// FONCTION POUR GÉNÉRER LE TEMPLATE EMAIL
// ============================================
function generateComplaintEmailTemplate($clientName, $complaintId, $problemDesc, $adminResponse, $newStatus) {
    $currentDate = date('d/m/Y à H:i');
    
    // Couleur du statut
    $statusColor = '#4CAF50';
    if ($newStatus == 'Rejetée') $statusColor = '#f44336';
    if ($newStatus == 'En Cours') $statusColor = '#ff9800';
    if ($newStatus == 'Nouveau') $statusColor = '#2196f3';
    
    return "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial, sans-serif; background-color: #f7f9fc; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
            <div style='background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 30px 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>SOLIDA - Support Réclamations</h1>
                <p style='margin: 5px 0 0 0; opacity: 0.9;'>Réponse à votre réclamation</p>
            </div>
            
            <div style='padding: 30px 20px;'>
                <h2 style='color: #333; margin-bottom: 20px; font-size: 20px;'>
                    Bonjour " . htmlspecialchars($clientName) . ",
                </h2>
                
                <p style='color: #666; line-height: 1.6; margin-bottom: 20px;'>
                    Nous vous informons que notre équipe a traité votre réclamation <strong>#" . $complaintId . "</strong>.
                </p>
                
                <div style='background-color: #f0f8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #4CAF50;'>
                    <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                        <div>
                            <strong style='color: #333;'>Numéro :</strong>
                            <span style='color: #4CAF50; font-weight: bold;'>#" . $complaintId . "</span>
                        </div>
                        <div>
                            <strong style='color: #333;'>Statut :</strong>
                            <span style='color: #ffffff; background-color: " . $statusColor . "; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;'>
                                " . htmlspecialchars($newStatus) . "
                            </span>
                        </div>
                    </div>
                    <div style='margin-top: 10px;'>
                        <strong style='color: #333;'>Date :</strong>
                        <span style='color: #666;'>" . $currentDate . "</span>
                    </div>
                </div>
                
                <div style='background-color: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 25px;'>
                    <h3 style='color: #333; margin-top: 0; margin-bottom: 15px; font-size: 18px;'>
                        📝 Réponse de notre équipe
                    </h3>
                    <div style='color: #444; line-height: 1.6; background-color: white; padding: 15px; border-radius: 5px; border: 1px solid #e0e0e0;'>
                        " . nl2br(htmlspecialchars($adminResponse)) . "
                    </div>
                </div>
                
                <div style='background-color: #fff8e1; border-radius: 8px; padding: 15px; border-left: 4px solid #ffc107;'>
                    <p style='color: #856404; margin: 0; font-size: 14px;'>
                        <strong>ℹ️ Note :</strong> 
                        Pour toute question supplémentaire, répondez directement à cet email.
                    </p>
                </div>
            </div>
            
            <div style='background-color: #f1f1f1; padding: 20px; text-align: center; border-top: 1px solid #ddd;'>
                <p style='color: #666; margin: 0 0 10px 0; font-size: 14px;'>
                    <strong>Service Client SOLIDA</strong><br>
                    Email : support@solida.tn
                </p>
                <p style='color: #999; margin: 0; font-size: 12px;'>
                    © " . date('Y') . " SOLIDA - Tous droits réservés.
                </p>
            </div>
        </div>
    </body>
    </html>";
}
?>

<!-- LE RESTE DE TON HTML (FORMULAIRE) RESTE EXACTEMENT LE MÊME -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre à la Réclamation #<?php echo htmlspecialchars($id); ?> - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    
    <style>
        :root { --color-primary: #4CAF50; --color-light: #E8F5E9; --color-dark: #388E3C; --color-text: #333; --color-error: #F44336; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .form-container { width: 100%; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 5px solid var(--color-primary); }
        h2 { color: var(--color-dark); font-size: 28px; margin-bottom: 30px; text-align: center; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; }
        h3 { color: var(--color-dark); font-size: 18px; margin-top: 30px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--color-light); font-weight: 600; }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; display: flex; flex-direction: column; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--color-text); }
        .label-required:after { content: " *"; color: var(--color-error); }
        input[type="text"], input[type="email"], textarea, select { padding: 12px 15px; border: 1px solid #BDBDBD; border-radius: 6px; width: 100%; box-sizing: border-box; font-family: inherit; font-size: 14px; transition: border-color 0.3s, box-shadow 0.3s; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1); }
        input:disabled, textarea:disabled, select:disabled { background-color: #f8f9fa; color: #6c757d; border-color: #e9ecef; cursor: not-allowed; }
        .error-field { border-color: var(--color-error) !important; box-shadow: 0 0 0 1px rgba(244, 67, 54, 0.25); }
        .submit-btn { width: 100%; background: linear-gradient(to right, var(--color-primary), var(--color-dark)); color: white; padding: 16px 20px; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 30px; display: flex; justify-content: center; align-items: center; transition: all 0.3s ease; gap: 10px; }
        .submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08); }
        .submit-btn:disabled { background: #cccccc; cursor: not-allowed; transform: none; box-shadow: none; }
        .error-message { background-color: #f8d7da; color: var(--color-error); padding: 15px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #f5c6cb; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .error-message:before { content: "⚠️"; }
        .field-error-message { color: var(--color-error); font-size: 13px; margin-top: 5px; font-weight: 500; display: flex; align-items: center; gap: 5px; }
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .status-Nouveau { background-color: #e3f2fd; color: #1565c0; }
        .status-EnCours { background-color: #fff3e0; color: #ef6c00; }
        .status-Traitée { background-color: #e8f5e9; color: #2e7d32; }
        .status-Rejetée { background-color: #ffebee; color: #c62828; }
        .back-link { display: inline-flex; align-items: center; gap: 5px; margin-top: 20px; color: var(--color-primary); text-decoration: none; font-weight: 500; transition: color 0.3s; }
        .back-link:hover { color: var(--color-dark); text-decoration: underline; }
        .info-box { background-color: #f0f7ff; border-left: 4px solid #2196f3; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .info-box p { margin: 5px 0; color: #0d47a1; font-size: 14px; }
        @media (max-width: 768px) { 
            .form-row { flex-direction: column; gap: 15px; } 
            .form-container { padding: 25px; margin: 10px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php 
        $pageTitle = 'Répondre à la Réclamation';
        include 'includes/topbar.php'; 
        ?>
        
        <div class="container">
            <div class="form-container">
    <h2><span style="font-size: 32px;">📝</span> Répondre à la Réclamation #<?php echo htmlspecialchars($id); ?></h2>
    
    <?php if ($message_soumission): ?>
        <?php echo $message_soumission; ?>
    <?php endif; ?>

    <div class="info-box">
        <p><strong>💡 Information :</strong> Votre réponse sera enregistrée et un email sera envoyé automatiquement au demandeur.</p>
        <p><small>Utilisation de PHPMailer avec Gmail SMTP</small></p>
    </div>

    <form method="POST" id="responseForm">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <h3>📋 Détails du Demandeur</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom Complet</label>
                <input type="text" id="nom" value="<?php echo htmlspecialchars(($reclamation['nom'] ?? '') . ' ' . ($reclamation['prenom'] ?? '')); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="email">Contact</label>
                <input type="text" id="email" value="<?php echo htmlspecialchars(($reclamation['email'] ?? '') . ' / ' . ($reclamation['telephone'] ?? '')); ?>" disabled>
            </div>
        </div>

        <h3>📍 Localisation</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="gouvernorat">Gouvernorat</label>
                <input type="text" id="gouvernorat" value="<?php echo htmlspecialchars($reclamation['gouvernorat'] ?? ''); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="delegation">Délégation</label>
                <input type="text" id="delegation" value="<?php echo htmlspecialchars($reclamation['delegation'] ?? ''); ?>" disabled>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ville">Ville</label>
                <input type="text" id="ville" value="<?php echo htmlspecialchars($reclamation['ville'] ?? ''); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="date_demande">Date de création</label>
                <input type="text" id="date_demande" value="<?php echo isset($reclamation['date']) ? date('d/m/Y', strtotime($reclamation['date'])) : 'N/A'; ?>" disabled>
            </div>
        </div>

        <div class="form-group">
            <label for="description_detaillee">📝 Description du Problème</label>
            <textarea id="description_detaillee" rows="5" disabled><?php echo htmlspecialchars($reclamation['description_detaillee'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Priorité</label>
                <input type="text" value="<?php echo htmlspecialchars($reclamation['priorite'] ?? 'Non définie'); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Statut actuel</label>
                <div style="margin-top: 8px;">
                    <span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($reclamation['statut'] ?? 'Nouveau')); ?>">
                        <?php echo htmlspecialchars($reclamation['statut'] ?? 'Nouveau'); ?>
                    </span>
                </div>
            </div>
        </div>

        <hr style="border: 0; height: 1px; background: linear-gradient(to right, transparent, #ccc, transparent); margin: 30px 0;">

        <h3>📤 Réponse Administrative</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="statut" class="label-required">Nouveau statut</label>
                <select id="statut" name="statut" required style="background-color: #f8f9fa;">
                    <option value="En Cours" <?php echo ($new_statut == 'En Cours') ? 'selected' : ''; ?>>🔄 En Cours</option>
                    <option value="Traitée" <?php echo ($new_statut == 'Traitée') ? 'selected' : ''; ?>>✅ Traitée</option>
                    <option value="Rejetée" <?php echo ($new_statut == 'Rejetée') ? 'selected' : ''; ?>>❌ Rejetée</option>
                    <option value="Nouveau" <?php echo ($new_statut == 'Nouveau') ? 'selected' : ''; ?>>📋 Nouveau</option>
                </select>
            </div>
            <div class="form-group">
                <label style="color: transparent;">.</label>
                <div style="font-size: 12px; color: #666; padding-top: 8px;">
                    Le nouveau statut sera visible par le demandeur
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="response_detaillee" class="label-required">Votre réponse détaillée</label>
            <textarea 
                id="response_detaillee" 
                name="response_detaillee" 
                rows="8" 
                required 
                placeholder="Écrivez ici votre réponse officielle au demandeur. Cette réponse sera envoyée par email."
                class="<?php echo !empty($error_response_detaillee) ? 'error-field' : ''; ?>"
                style="resize: vertical; min-height: 150px;"
            ><?php echo htmlspecialchars($response_detaillee); ?></textarea>
            
            <?php if (!empty($error_response_detaillee)): ?>
                <div id="response-error-php" class="field-error-message">
                    <span>❌</span> <?php echo $error_response_detaillee; ?>
                </div>
            <?php endif; ?>
            
            <div id="response-error-js" class="field-error-message" style="display: none;">
                <span>❌</span> La réponse doit contenir au moins 10 caractères.
            </div>
            
            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                <span id="char-count"><?php echo strlen($response_detaillee); ?></span> caractères (minimum : 10)
            </div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <span style="font-size: 18px;">📧</span> 
            <span>Envoyer la réponse par email</span>
        </button>
        
        <a href="BackofficeReclamations.php" class="back-link">
            <span style="font-size: 18px;">←</span> Retour à la liste des réclamations
        </a>
    </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('response_detaillee');
        const errorJs = document.getElementById('response-error-js');
        const errorPhp = document.getElementById('response-error-php');
        const submitBtn = document.getElementById('submitBtn');
        const charCount = document.getElementById('char-count');
        const MIN_LENGTH = 10;

        function updateCharCount() {
            const length = textarea.value.trim().length;
            charCount.textContent = length;
            
            if (length < MIN_LENGTH) {
                charCount.style.color = '#f44336';
            } else if (length < 50) {
                charCount.style.color = '#ff9800';
            } else {
                charCount.style.color = '#4caf50';
            }
        }

        function validateResponse() {
            const length = textarea.value.trim().length;
            const isValid = length >= MIN_LENGTH;

            if (errorPhp) {
                errorPhp.style.display = 'none';
            }

            if (!isValid) {
                errorJs.style.display = 'flex';
                textarea.classList.add('error-field');
                submitBtn.disabled = true;
            } else {
                errorJs.style.display = 'none';
                textarea.classList.remove('error-field');
                submitBtn.disabled = false;
            }
            
            updateCharCount();
            return isValid;
        }

        textarea.addEventListener('input', validateResponse);
        validateResponse();
        
        const form = document.getElementById('responseForm');
        form.addEventListener('submit', function(event) {
            if (!validateResponse()) {
                event.preventDefault();
                textarea.style.animation = 'shake 0.5s';
                setTimeout(() => { textarea.style.animation = ''; }, 500);
                textarea.focus();
            }
        });

        const style = document.createElement('style');
        style.textContent = `@keyframes shake { 0%,100%{transform:translateX(0);} 10%,30%,50%,70%,90%{transform:translateX(-5px);} 20%,40%,60%,80%{transform:translateX(5px);} }`;
        document.head.appendChild(style);
    });
</script>

</body>
</html>