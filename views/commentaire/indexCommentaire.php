<?php
// app/views/commentaire/indexCommentaire.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $comments and $error should be set by CommentaireController::adminIndex()
// If not set, initialize
if (!isset($comments)) {
    $comments = [];
}
if (!isset($error)) {
    $error = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commentaires - SOLIDA Admin</title>
    
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
    $pageTitle = 'Gestion des Commentaires';
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

            <?php if (isset($error) && $error): ?>
                <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Table des commentaires -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Liste des commentaires</h2>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($comments)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Forum</th>
                                <th>Auteur</th>
                                <th>Contenu</th>
                                <th>Date</th>
                                <th>Parent</th>
                                <th>Signalé</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                            <?php
                                // Handle both arrays and objects
                                $id_commentaire = is_array($comment) ? ($comment['id_commentaire'] ?? 0) : (method_exists($comment, 'getIdCommentaire') ? $comment->getIdCommentaire() : 0);
                                $id_forum = is_array($comment) ? ($comment['id_forum'] ?? 0) : (method_exists($comment, 'getIdForum') ? $comment->getIdForum() : 0);
                                $id_auteur = is_array($comment) ? ($comment['id_auteur'] ?? 'N/A') : (method_exists($comment, 'getIdAuteur') ? $comment->getIdAuteur() : 'N/A');
                                $contenu = is_array($comment) ? ($comment['contenu'] ?? '') : (method_exists($comment, 'getContenu') ? $comment->getContenu() : '');
                                $date_commentaire = is_array($comment) ? ($comment['date_commentaire'] ?? '') : (method_exists($comment, 'getDateCommentaire') ? $comment->getDateCommentaire() : '');
                                $signale = is_array($comment) ? ($comment['signale'] ?? 0) : (method_exists($comment, 'getSignale') ? $comment->getSignale() : 0);
                                $forum_titre = is_array($comment) ? ($comment['forum_titre'] ?? '') : '';
                                $auteur_nom = is_array($comment) ? ($comment['auteur_nom'] ?? 'Anonyme') : '';
                            ?>
                            <tr>
                                <td><?php echo (int)$id_commentaire; ?></td>
                                <td>
                                    <?php if (!empty($forum_titre)): ?>
                                        <a href="../front_office/index.php?section=forum&action=show&id=<?php echo (int)$id_forum; ?>" 
                                           target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars(mb_strlen($forum_titre) > 30 ? mb_substr($forum_titre, 0, 30) . '...' : $forum_titre); ?>
                                        </a>
                                    <?php else: ?>
                                        Forum #<?php echo (int)$id_forum; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($auteur_nom ?: ($id_auteur !== 'N/A' ? $id_auteur : 'Anonyme')); ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars(mb_strlen($contenu) > 100 ? mb_substr($contenu, 0, 100) . '...' : $contenu);
                                    ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px;"></i>
                                    <?php 
                                    if (!empty($date_commentaire)) {
                                        try {
                                            $d = new DateTime($date_commentaire);
                                            echo $d->format('d/m/Y H:i');
                                        } catch (Exception $e) {
                                            echo htmlspecialchars($date_commentaire);
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $parent_id = is_array($comment) ? ($comment['parent_id'] ?? null) : (method_exists($comment, 'getParentId') ? $comment->getParentId() : null);
                                    if (!empty($parent_id)): ?>
                                        <span class="badge bg-info">Réponse #<?php echo (int)$parent_id; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Commentaire principal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($signale == 1): ?>
                                        <span class="badge bg-danger">Oui</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="../back_office/dashboard.php?section=commentaire&action=edit&id=<?php echo (int)$id_commentaire; ?>"
                                           class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../back_office/dashboard.php?section=commentaire&action=toggleSignal&id=<?php echo (int)$id_commentaire; ?>" 
                                           class="btn btn-sm btn-<?php echo ($signale == 1) ? 'success' : 'warning'; ?>" 
                                           title="<?php echo ($signale == 1) ? 'Désignaler' : 'Signaler'; ?>">
                                            <i class="fas fa-flag"></i>
                                        </a>
                                        <a href="../back_office/dashboard.php?section=commentaire&action=delete&id=<?php echo (int)$id_commentaire; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');"
                                           title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                            <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                            Aucun commentaire trouvé
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
</body>
</html>