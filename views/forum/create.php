<?php
// views/forum/create.php - Context-aware: works when included from front office or accessed directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect context: front office (included by index.php) or direct access
$currentPath = $_SERVER['PHP_SELF'];
$isFrontOfficeInclude = strpos($currentPath, '/front_office/index.php') !== false || 
                        (isset($GLOBALS['old']) && strpos($currentPath, '/front_office/') !== false);

// Get variables
$old = $old ?? $GLOBALS['old'] ?? [];
$fieldErrors = $fieldErrors ?? $GLOBALS['fieldErrors'] ?? [];

// If not included from front office, render full HTML structure
if (!$isFrontOfficeInclude):
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Nouveau Sujet - SOLIDA Forum</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../front_office/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../front_office/assets/css/templatemo.css">
    <link rel="stylesheet" href="../front_office/assets/css/custom.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../front_office/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer onerror="console.warn('reCAPTCHA could not be loaded');"></script>
</head>
<body>
<?php include '../front_office/includes/navbar.php'; ?>
<div class="container py-5">
    <h1 class="h1 text-success">
        <i class="fas fa-plus-circle me-3"></i>Créer un Nouveau Sujet
    </h1>
    <p class="lead">Partagez vos idées et lancez une nouvelle discussion.</p>
</div>
<?php else: ?>
<!-- Included from front office - just content -->
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <h1 class="h1 text-success mb-4 text-center">
                <i class="fas fa-plus-circle me-3"></i>Créer un Nouveau Sujet
            </h1>
            <p class="lead mb-4 text-center">Partagez vos idées et lancez une nouvelle discussion.</p>
<?php endif; ?>

    <style>
        .forum-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            <?php if ($isFrontOfficeInclude): ?>
            margin: 0 auto 60px auto;
            max-width: 900px;
            width: 100%;
            <?php else: ?>
            margin: 20px 0 60px 0;
            <?php endif; ?>
        }
        .forum-tabs { margin-bottom: 20px; border-bottom: 2px solid #dee2e6; display: flex; }
        .forum-tabs button {
            background: none;
            border: none;
            padding: 12px 20px;
            font-size: 1.05em;
            cursor: pointer;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            margin-right: 15px;
            transition: all 0.2s;
            font-weight: 600;
        }
        .forum-tabs button.active { color: #28a745; border-bottom: 3px solid #28a745; }
        .forum-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        .action-button {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .action-button:hover { background-color: #218838; color: white; text-decoration: none; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #212934; }
        .form-control {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 14px; transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none; border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.15);
        }
        textarea.form-control { resize: vertical; min-height: 100px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border: none; border-radius: 6px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.3s; text-decoration: none;
        }
        .btn-primary { background: #28a745; color: white; }
        .btn-primary:hover { background: #218838; color: #fff; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .char-count { font-size: 12px; color: #6c757d; margin-top: 5px; }
        .error-message { color: #e74c3c; font-size: 12px; margin-top: 5px; display: none; }
        .form-control.error { border-color: #e74c3c; }
        .success-message {
            background: #d4edda; color: #155724;
            padding: 10px; border-radius: 4px; margin-bottom: 15px; display: none;
        }
        .loading { display: none; text-align: left; padding: 10px 0; }
        .spinner {
            border: 3px solid #f3f3f3; border-top: 3px solid #28a745;
            border-radius: 50%; width: 20px; height: 20px;
            animation: spin 1s linear infinite; display: inline-block; margin-right: 10px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .recaptcha-box {
            margin: 25px 0 10px 0;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        .recaptcha-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #212934;
        }
    </style>

    <div class="forum-container">
        <div class="forum-tabs">
            <button id="tab-general" class="active" type="button">Forum Général</button>
            <button id="tab-private" type="button">Discussions Privées</button>
        </div>

        <div class="forum-controls">
        <a href="<?php echo $isFrontOfficeInclude ? 'index.php?section=forum' : '../front_office/index.php?section=forum'; ?>" class="action-button">
                <i class="fas fa-arrow-left"></i> Retour au Forum
            </a>
            <span style="font-size: 13px; color:#6c757d;">
            Les champs marqués d'un <strong>*</strong> sont obligatoires.
            </span>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px;border-radius:4px;margin-bottom:15px;border:1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div id="successMessage" class="success-message">
            <i class="fas fa-check-circle"></i> Sujet créé avec succès !
        </div>
        <div id="loading" class="loading">
            <div class="spinner"></div> Création en cours...
        </div>

    <!-- Formulaire : envoi vers index.php -->
    <form id="forumForm" action="<?php echo $isFrontOfficeInclude ? 'index.php?section=forum' : '../front_office/index.php?section=forum'; ?>" method="POST">
        <input type="hidden" name="action" value="store">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div class="form-group">
                    <label for="categorie" class="form-label">Catégorie *</label>
                    <select class="form-control" id="categorie" name="categorie" required>
                        <option value="">Choisir une catégorie</option>
                    <option value="general" <?php echo (isset($old['categorie']) && $old['categorie'] === 'general') ? 'selected' : ''; ?>>Discussion générale</option>
                    <option value="technique" <?php echo (isset($old['categorie']) && $old['categorie'] === 'technique') ? 'selected' : ''; ?>>Problème technique</option>
                    <option value="aide" <?php echo (isset($old['categorie']) && $old['categorie'] === 'aide') ? 'selected' : ''; ?>>Demande d'aide</option>
                    <option value="suggestion" <?php echo (isset($old['categorie']) && $old['categorie'] === 'suggestion') ? 'selected' : ''; ?>>Suggestion</option>
                    <option value="annonce" <?php echo (isset($old['categorie']) && $old['categorie'] === 'annonce') ? 'selected' : ''; ?>>Annonce</option>
                    <option value="projet" <?php echo (isset($old['categorie']) && $old['categorie'] === 'projet') ? 'selected' : ''; ?>>Projet étudiant</option>
                    </select>
                    <div id="categorieError" class="error-message">
                    <?php echo isset($fieldErrors['categorie']) ? htmlspecialchars($fieldErrors['categorie']) : 'Veuillez sélectionner une catégorie'; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type_discussion" class="form-label">Type de discussion</label>
                    <select class="form-control" id="type_discussion" name="type_discussion">
                        <option value="general">Discussion générale</option>
                        <option value="detaillee">Discussion détaillée</option>
                        <option value="urgence">Question urgente</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="discussion_g" class="form-label">Discussion Générale *</label>
                <textarea class="form-control" id="discussion_g" name="discussion_g" rows="4"
                      maxlength="500" required><?php echo htmlspecialchars($old['discussion_g'] ?? ''); ?></textarea>
            <div class="char-count" id="count_g"><?php echo mb_strlen($old['discussion_g'] ?? ''); ?>/500 caractères</div>
                <div id="discussionGError" class="error-message">
                <?php echo isset($fieldErrors['discussion_g']) ? htmlspecialchars($fieldErrors['discussion_g']) : 'La discussion générale doit contenir entre 10 et 500 caractères'; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="discussion_p" class="form-label">Discussion Précise *</label>
                <textarea class="form-control" id="discussion_p" name="discussion_p" rows="6"
                      maxlength="2000" required><?php echo htmlspecialchars($old['discussion_p'] ?? ''); ?></textarea>
            <div class="char-count" id="count_p"><?php echo mb_strlen($old['discussion_p'] ?? ''); ?>/2000 caractères</div>
                <div id="discussionPError" class="error-message">
                <?php echo isset($fieldErrors['discussion_p']) ? htmlspecialchars($fieldErrors['discussion_p']) : 'La discussion précise doit contenir entre 20 et 2000 caractères'; ?>
                </div>
            </div>

            <!-- reCAPTCHA -->
            <div class="recaptcha-box">
                <div class="recaptcha-title">
                    <i class="fas fa-shield-alt"></i> Vérification de sécurité
                </div>
            <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                <div id="recaptchaError" class="error-message">
                    Veuillez cocher la case "Je ne suis pas un robot".
                </div>
            </div>

            <div style="display:flex;gap:15px;justify-content:flex-end;border-top:1px solid #e9ecef;padding-top:25px;">
            <a href="<?php echo $isFrontOfficeInclude ? 'index.php?section=forum' : '../front_office/index.php?section=forum'; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" id="submitBtn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer la Discussion
                </button>
            </div>
        </form>
    </div>

    <script>
        const discussionG = document.getElementById('discussion_g');
        const discussionP = document.getElementById('discussion_p');
        const countG = document.getElementById('count_g');
        const countP = document.getElementById('count_p');
        const form = document.getElementById('forumForm');
        const submitBtn = document.getElementById('submitBtn');
        const loading = document.getElementById('loading');

        function updateCount(el, counter, max) {
            const len = el.value.length;
            counter.textContent = `${len}/${max} caractères`;
        }
        discussionG.addEventListener('input', () => updateCount(discussionG, countG, 500));
        discussionP.addEventListener('input', () => updateCount(discussionP, countP, 2000));

        function validateDiscussionG() {
            const v = discussionG.value.trim();
            const err = document.getElementById('discussionGError');
            const bad = v.length < 10 || v.length > 500;
            err.style.display = bad ? 'block' : 'none';
            discussionG.classList.toggle('error', bad);
            return !bad;
        }
        function validateDiscussionP() {
            const v = discussionP.value.trim();
            const err = document.getElementById('discussionPError');
            const bad = v.length < 20 || v.length > 2000;
            err.style.display = bad ? 'block' : 'none';
            discussionP.classList.toggle('error', bad);
            return !bad;
        }
        function validateCategorie() {
            const cat = document.getElementById('categorie');
            const err = document.getElementById('categorieError');
            const bad = !cat.value;
            err.style.display = bad ? 'block' : 'none';
            cat.classList.toggle('error', bad);
            return !bad;
        }

        function validateRecaptcha() {
            const err = document.getElementById('recaptchaError');
        // If reCAPTCHA is not loaded, skip validation (optional feature)
            if (typeof grecaptcha === 'undefined') {
            console.warn('reCAPTCHA non chargé - validation ignorée');
            return true; // Allow form submission without reCAPTCHA
            }
            const response = grecaptcha.getResponse();
            const bad = response.length === 0;
            err.style.display = bad ? 'block' : 'none';
            return !bad;
        }

        form.addEventListener('submit', e => {
            e.preventDefault();
        if (!(validateCategorie() && validateDiscussionG() && validateDiscussionP())) return;
        // Only validate reCAPTCHA if it's loaded
        if (typeof grecaptcha !== 'undefined' && !validateRecaptcha()) return;
            submitBtn.disabled = true;
            loading.style.display = 'block';
            setTimeout(() => form.submit(), 300);
        });
    </script>

<?php if ($isFrontOfficeInclude): ?>
        </div>
    </div>
</section>
<?php else: ?>
<?php include '../front_office/includes/footer.php'; ?>
</body>
</html>
<?php endif; ?>
