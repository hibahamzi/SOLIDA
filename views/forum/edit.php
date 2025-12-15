<?php
// app/views/forum/edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $forum doit être un tableau associatif venant de ForumController::edit()
if (!isset($forum) || !is_array($forum)) {
    $_SESSION['error'] = "Sujet introuvable.";
    header("Location: /forum_mvc_project1234/app/index.php?controller=ForumController&action=adminIndex");
    exit;
}

// Sécuriser les valeurs
$id_forum      = (int)$forum['id_forum'];
$categorie     = htmlspecialchars($forum['categorie'] ?? '', ENT_QUOTES, 'UTF-8');
$discussion_g  = htmlspecialchars($forum['discussion_g'] ?? '', ENT_QUOTES, 'UTF-8');
$discussion_p  = htmlspecialchars($forum['discussion_p'] ?? '', ENT_QUOTES, 'UTF-8');
$date_creation = $forum['date_creation'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer un Forum - SOLIDA Admin</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- CSS admin -->
    <link rel="stylesheet" href="../back_office/assets/css/admin.css">
</head>
<body>
<?php
// Calculate correct paths for includes
$viewDir = __DIR__; // views/forum
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
    $pageTitle = 'Éditer un Forum';
    include $includesPath . '/topbar.php'; 
    ?>

        <!-- Content -->
        <div class="content-area">

            <!-- Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background:#e74c3c;color:white;padding:12px 15px;border-radius:6px;margin-bottom:15px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="background:#2ecc71;color:white;padding:12px 15px;border-radius:6px;margin-bottom:15px;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">

                <div class="table-header">
                    <h2>Modifier le sujet</h2>
                    <a href="../back_office/dashboard.php?section=forum"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <!-- Infos sujet -->
                <div style="background:#f8f9fa;padding:15px;border-radius:6px;margin-bottom:20px;border-left:4px solid #59ab6e;">
                    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:14px;">
                        <div><strong>ID :</strong> #<?php echo $id_forum; ?></div>
                        <div>
                            <strong>Date de création :</strong>
                            <?php
                                if ($date_creation) {
                                    echo htmlspecialchars(date('d/m/Y H:i', strtotime($date_creation)));
                                } else {
                                    echo 'Non spécifiée';
                                }
                            ?>
                        </div>
                        <div>
                            <strong>Statut :</strong>
                            <span style="color:#59ab6e;font-weight:600;">
                                <i class="fas fa-circle" style="font-size:8px;"></i> Actif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'édition -->
                <form action="../back_office/dashboard.php?section=forum&action=update"
                      method="post"
                      class="form">
                    <input type="hidden" name="action" value="update">

                    <input type="hidden" name="id_forum" value="<?php echo $id_forum; ?>">

                    <!-- Catégorie -->
                    <div class="form-group">
                        <label for="categorie" class="form-label">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control" required>
                            <option value="">-- Choisir une catégorie --</option>
                            <option value="general"    <?php echo $categorie === 'general'    ? 'selected' : ''; ?>>Discussion générale</option>
                            <option value="technique"  <?php echo $categorie === 'technique'  ? 'selected' : ''; ?>>Problème technique</option>
                            <option value="aide"       <?php echo $categorie === 'aide'       ? 'selected' : ''; ?>>Demande d'aide</option>
                            <option value="suggestion" <?php echo $categorie === 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                            <option value="annonce"    <?php echo $categorie === 'annonce'    ? 'selected' : ''; ?>>Annonce</option>
                            <option value="projet"     <?php echo $categorie === 'projet'     ? 'selected' : ''; ?>>Projet étudiant</option>
                        </select>
                    </div>

                    <!-- Discussion générale -->
                    <div class="form-group">
                        <label for="discussion_g" class="form-label">Discussion générale</label>
                        <textarea id="discussion_g"
                                  name="discussion_g"
                                  class="form-control"
                                  rows="3"
                                  required><?php echo $discussion_g; ?></textarea>
                        <small style="color:#6c757d;font-size:12px;">
                            Résumé global du sujet (minimum 10 caractères).
                        </small>
                    </div>

                    <!-- Discussion principale -->
                    <div class="form-group">
                        <label for="discussion_p" class="form-label">Discussion principale</label>
                        <textarea id="discussion_p"
                                  name="discussion_p"
                                  class="form-control"
                                  rows="6"
                                  required><?php echo $discussion_p; ?></textarea>
                        <small style="color:#6c757d;font-size:12px;">
                            Détaillez le sujet (minimum 20 caractères).
                        </small>
                    </div>

                    <!-- Boutons -->
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                        <a href="../back_office/dashboard.php?section=forum"
                           class="btn btn-light">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>