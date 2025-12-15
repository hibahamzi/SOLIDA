<?php
// app/views/commentaire/edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $old should be set by CommentaireController::edit()
// If not set, try to get from GLOBALS or initialize
if (!isset($old)) {
    $old = $GLOBALS['old'] ?? null;
}
if (!isset($fieldErrors)) {
    $fieldErrors = $GLOBALS['fieldErrors'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Commentaire - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- CSS admin -->
    <link rel="stylesheet" href="../back_office/assets/css/admin.css">
</head>
<body>
<?php
// Calculate correct paths for includes
$viewDir = __DIR__; // views/commentaire
$backOfficeDir = dirname($viewDir) . '/back_office'; // views/back_office
$includesPath = $backOfficeDir . '/includes';

// Ensure paths are correct
if (!file_exists($includesPath . '/sidebar.php')) {
    // Try alternative path
    $includesPath = __DIR__ . '/../back_office/includes';
}
?>
<?php include $includesPath . '/sidebar.php'; ?>
<div class="main-content">
    <?php 
    $pageTitle = 'Modifier un Commentaire';
    include $includesPath . '/topbar.php'; 
    ?>
        
        <!-- Content Area -->
        <div class="content-area">

            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #2ecc71; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (!$old || !isset($old['id_commentaire'])): ?>
                <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Commentaire introuvable ou non défini.
                </div>
                <a href="../back_office/dashboard.php?section=commentaire"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste des commentaires
                </a>
            <?php else: ?>

                <?php
                    $idSafe        = (int)($old['id_commentaire'] ?? 0);
                    $idForumSafe   = htmlspecialchars($old['id_forum'] ?? '');
                    $idAuteurSafe  = htmlspecialchars($old['id_auteur'] ?? '');
                    $dateSafe      = htmlspecialchars($old['date_commentaire'] ?? '');
                    $contenuSafe   = htmlspecialchars($old['contenu'] ?? '');
                ?>

                <!-- Bloc d'info -->
                <div class="table-container" style="margin-bottom: 25px;">
                    <div class="table-header">
                        <h2>Informations du commentaire #<?php echo $idSafe; ?></h2>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p><strong>ID commentaire :</strong> #<?php echo $idSafe; ?></p>
                        <p><strong>ID forum :</strong> <?php echo $idForumSafe ?: '-'; ?></p>
                        <p><strong>ID auteur :</strong> <?php echo $idAuteurSafe ?: '-'; ?></p>
                        <p>
                            <strong>Date :</strong>
                            <i class="far fa-calendar" style="margin-right: 5px;"></i>
                            <?php 
                            if (!empty($dateSafe)) {
                                try {
                                    $date = new DateTime($dateSafe);
                                    echo $date->format('d/m/Y H:i');
                                } catch (Exception $e) {
                                    echo htmlspecialchars($dateSafe);
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Formulaire d'édition -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Modifier le contenu</h2>
                    </div>

                    <form action="../back_office/dashboard.php?section=commentaire&action=update" method="POST" style="padding: 20px;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_commentaire" value="<?php echo $idSafe; ?>">

                        <div style="margin-bottom: 20px;">
                            <label for="contenu" style="display:block; margin-bottom:8px; font-weight:600; color:#212934;">
                                Contenu du commentaire
                            </label>
                            <textarea
                                id="contenu"
                                name="contenu"
                                rows="6"
                                style="
                                    width: 100%;
                                    padding: 10px 12px;
                                    border: 1px solid #ddd;
                                    border-radius: 6px;
                                    font-size: 14px;
                                    resize: vertical;
                                "
                                required
                            ><?php echo $contenuSafe; ?></textarea>
                            <small style="display:block; margin-top:6px; color:#6c757d; font-size:12px;">
                                Modifiez le texte du commentaire. Veuillez rester respectueux et clair.
                            </small>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 10px;">
                            <a href="../back_office/dashboard.php?section=commentaire"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

            <?php endif; ?>
        </div>
        
    </div>
    
</body>
</html>