<?php
// views/forum/index.php - Context-aware: works for both front office and back office
// $forums, $categories, $selectedCategorie, $selectedSort, $commentsByForum should be set by the controller

// Detect context: front office (included by index.php) or back office (direct access or included by dashboard)
$currentPath = $_SERVER['PHP_SELF'];
$isFrontOfficeInclude = (strpos($currentPath, '/front_office/index.php') !== false) || 
                        (isset($GLOBALS['forums']) && strpos($currentPath, '/front_office/') !== false);
$isBackOfficeContext = strpos($currentPath, '/back_office/') !== false || 
                       (strpos($currentPath, '/forum/index.php') !== false && !$isFrontOfficeInclude);

// Get variables
$forums = $forums ?? $GLOBALS['forums'] ?? [];
$categories = $categories ?? $GLOBALS['categories'] ?? [];
$selectedCategorie = $selectedCategorie ?? $GLOBALS['selectedCategorie'] ?? '';
$selectedSort = $selectedSort ?? $GLOBALS['selectedSort'] ?? 'date_desc';
$commentsByForum = $commentsByForum ?? $GLOBALS['commentsByForum'] ?? [];

// If back office context, include full structure
if ($isBackOfficeContext && !$isFrontOfficeInclude):
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - SOLIDA Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../../back_office/assets/css/admin.css">
</head>
<body>
<?php
    $viewDir = __DIR__;
    $backOfficeDir = dirname($viewDir) . '/back_office';
    $includesPath = $backOfficeDir . '/includes';
    include $includesPath . '/sidebar.php';
?>
<div class="main-content">
    <?php 
    $pageTitle = 'Gestion du Forum';
    include $includesPath . '/topbar.php'; 
    ?>
    <div class="content-area">
<?php else: ?>
<!-- Front Office Forum Index - Content only (navbar/footer already included by index.php) -->
<section class="container py-5">
<?php endif; ?>

<style>
.forum-container {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
}
.forum-tabs {
    margin-bottom: 20px;
    border-bottom: 2px solid #dee2e6;
    display: flex;
}
.forum-tabs button {
    background: none;
    border: none;
    padding: 12px 20px;
    font-size: 1.1em;
    cursor: pointer;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    margin-right: 15px;
    transition: all 0.2s;
    font-weight: 600;
}
.forum-tabs button.active {
    color: #28a745;
    border-bottom: 3px solid #28a745;
}
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
.action-button:hover {
    background-color: #218838;
    color: white;
    text-decoration: none;
}
.thread-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}
.thread-header {
    display: flex;
    align-items: center;
}
.thread-item:hover {
    background-color: #f8f9fa;
}
.thread-icon {
    font-size: 1.5em;
    color: #28a745;
    width: 50px;
    text-align: center;
}
.thread-main-info {
    flex-grow: 1;
}
.thread-title-link {
    color: #343a40;
    font-weight: bold;
    text-decoration: none;
    font-size: 1.1em;
}
.thread-title-link:hover {
    color: #28a745;
}
.thread-meta-info {
    font-size: 0.85em;
    color: #6c757d;
    margin-top: 5px;
}
.thread-stats {
    width: 260px;
    text-align: right;
    font-size: 0.9em;
    color: #495057;
}
.thread-stats span {
    display: block;
    margin-bottom: 5px;
}
.like-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid #28a745;
    color: #28a745;
    font-size: 0.85em;
    text-decoration: none;
}
.like-btn:hover {
    background: #28a745;
    color: white;
    text-decoration: none;
}
/* Commentaires */
.comments-block {
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px dashed #dee2e6;
}
.comment-item {
    margin-top: 8px;
    padding: 8px 10px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 0.9em;
}
.comment-item.reply {
    margin-left: 25px;
    background: #fdfdfd;
}
.comment-meta {
    font-size: 0.8em;
    color: #6c757d;
}
.comment-text {
    margin-top: 4px;
}
.reply-link, .add-comment-link {
    font-size: 0.85em;
    color: #28a745;
    cursor: pointer;
    margin-right: 10px;
    text-decoration: none;
}
.reply-link:hover, .add-comment-link:hover {
    text-decoration: underline;
}
.reply-form {
    margin-top: 8px;
}
.floating-help-chat {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    background-color: #ffc107;
    color: #343a40;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}
.floating-help-chat:hover {
    background-color: #e0a800;
    transform: scale(1.05);
}
</style>

<div class="forum-container">
    <?php if ($isFrontOfficeInclude): ?>
    <div class="floating-help-chat" onclick="alert('Ouverture du Chat d\\'Aide...')">
        <i class="fas fa-question-circle"></i>
    </div>
    <?php endif; ?>
    
    <div class="forum-tabs">
        <button id="tab-general" class="active" type="button" onclick="changeForumType('general')">
            <i class="fas fa-globe"></i> Forum Général
        </button>
        <?php if ($isFrontOfficeInclude): ?>
        <button id="tab-private" type="button" onclick="changeForumType('private')">
            <i class="fas fa-lock"></i> Discussions Privées
        </button>
        <?php endif; ?>
    </div>
    
    <div class="forum-controls">
        <a href="<?php 
            if ($isBackOfficeContext && !$isFrontOfficeInclude) {
                echo '../../front_office/index.php?section=forum&action=create';
            } else {
                // Front office context - path relative to front_office/index.php
                echo 'index.php?section=forum&action=create';
            }
        ?>" class="action-button">
            <i class="fas fa-plus"></i> Nouvelle Discussion
        </a>
        <div>
            <label for="filter-category" style="margin-right:10px;">Catégorie :</label>
            <form method="GET" action="<?php echo $isBackOfficeContext && !$isFrontOfficeInclude ? '../../front_office/index.php' : 'index.php'; ?>" style="display:inline;">
                <input type="hidden" name="section" value="forum">
                <select id="filter-category" name="categorie" class="form-select" style="display:inline-block;width:auto;" onchange="this.form.submit()">
                    <option value="all" <?php echo $selectedCategorie === '' || $selectedCategorie === 'all' ? 'selected' : ''; ?>>Toutes les Catégories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selectedCategorie === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst($cat)); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    
    <div id="thread-list">
        <?php if (empty($forums)): ?>
        <p style="text-align:center;padding:20px;color:#6c757d;">
            Aucune discussion pour le moment. Soyez le premier à créer un sujet !
        </p>
        <?php else: ?>
        <?php foreach ($forums as $forum): ?>
        <?php
            // Handle both arrays and objects
            $fid = is_array($forum) ? (int)($forum['id_forum'] ?? 0) : (method_exists($forum, 'getIdForum') ? (int)$forum->getIdForum() : 0);
            $categorie = is_array($forum) ? ($forum['categorie'] ?? '') : (method_exists($forum, 'getCategorie') ? $forum->getCategorie() : '');
            $discussion_g = is_array($forum) ? ($forum['discussion_g'] ?? '') : (method_exists($forum, 'getDiscussionG') ? $forum->getDiscussionG() : '');
            $discussion_p = is_array($forum) ? ($forum['discussion_p'] ?? '') : (method_exists($forum, 'getDiscussionP') ? $forum->getDiscussionP() : '');
            $date_creation = is_array($forum) ? ($forum['date_creation'] ?? '') : (method_exists($forum, 'getDateCreation') ? $forum->getDateCreation() : '');
            $likes = is_array($forum) ? (int)($forum['likes'] ?? 0) : (method_exists($forum, 'getLikes') ? (int)$forum->getLikes() : 0);
            $nb_commentaires = is_array($forum) ? (int)($forum['nb_commentaires'] ?? 0) : 0;
            $auteur_nom = is_array($forum) ? ($forum['auteur_nom'] ?? 'Anonyme') : '';
            
            // Get comments for this forum
            $forumComments = $commentsByForum[$fid] ?? [];
        ?>
        <div class="thread-item">
            <div class="thread-header">
                <div class="thread-icon">
                    <i class="fas fa-thumbtack"></i>
                </div>
                <div class="thread-main-info">
                    <a href="<?php 
                        if ($isBackOfficeContext && !$isFrontOfficeInclude) {
                            echo '../../front_office/index.php?section=forum&action=show&id=' . $fid;
                        } else {
                            // Front office context - path relative to front_office/index.php
                            echo 'index.php?section=forum&action=show&id=' . $fid;
                        }
                    ?>" class="thread-title-link">
                        <?php echo htmlspecialchars($discussion_g); ?>
                    </a>
                    <div class="thread-meta-info">
                        Catégorie : <strong><?php echo htmlspecialchars(ucfirst($categorie)); ?></strong>
                        <?php if (!empty($auteur_nom)): ?>
                        &nbsp;|&nbsp; Par : <strong><?php echo htmlspecialchars($auteur_nom); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="thread-stats">
                    <span>
                        <i class="fas fa-calendar-alt"></i>
                        <?php 
                        if (!empty($date_creation)) {
                            try {
                                $date = new DateTime($date_creation);
                                echo $date->format('d/m/Y H:i');
                            } catch (Exception $e) {
                                echo htmlspecialchars($date_creation);
                            }
                        } else {
                            echo 'Date inconnue';
                        }
                        ?>
                    </span>
                    <span>
                        <i class="fas fa-comments"></i> <?php echo $nb_commentaires; ?> commentaire(s)
                    </span>
                    <span>
                        <a href="<?php 
                            if ($isBackOfficeContext && !$isFrontOfficeInclude) {
                                echo '../../front_office/index.php?section=forum&action=like&id=' . $fid;
                            } else {
                                // Front office context - path relative to front_office/index.php
                                echo 'index.php?section=forum&action=like&id=' . $fid;
                            }
                        ?>" class="like-btn">
                            <i class="fas fa-thumbs-up"></i> <?php echo $likes; ?> Like(s)
                        </a>
                    </span>
                </div>
            </div>
            
            <!-- Bloc commentaires + ajout / réponses -->
            <div class="comments-block">
                <!-- bouton Ajouter un commentaire -->
                <p>
                    <a class="add-comment-link" href="#" onclick="toggleAddComment(<?= $fid; ?>); return false;">
                        <i class="fas fa-plus"></i> Ajouter un commentaire
                    </a>
                </p>
                
                <!-- Formulaire d'ajout de commentaire (masqué par défaut) -->
                <form class="reply-form" id="add_comment_form_<?= $fid; ?>" style="display:none;" 
                      action="<?php echo $isBackOfficeContext && !$isFrontOfficeInclude ? '../../front_office/index.php?section=forum' : 'index.php?section=forum'; ?>" 
                      method="POST">
                    <input type="hidden" name="action" value="store_comment">
                    <input type="hidden" name="id_forum" value="<?= $fid; ?>">
                    <div class="mb-2">
                        <textarea class="form-control" name="contenu" rows="3" placeholder="Écrire un commentaire..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAddComment(<?= $fid; ?>);">
                        Annuler
                    </button>
                </form>

                <?php if (empty($forumComments)): ?>
                    <p style="font-size:0.9em;color:#6c757d;">Aucun commentaire pour l'instant.</p>
                <?php else: ?>
                    <?php
                    // commentaires racine (parent_id NULL)
                    foreach ($forumComments as $c) {
                        if (!isset($c['parent_id']) || $c['parent_id'] === null) {
                            $cid = (int)$c['id_commentaire'];
                            ?>
                            <div class="comment-item">
                                <div class="comment-meta">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($c['auteur_nom'] ?? 'Auteur #' . (int)$c['id_auteur']); ?>
                                    &nbsp;|&nbsp;
                                    <i class="fas fa-clock"></i>
                                    <?php 
                                    if (!empty($c['date_commentaire'])) {
                                        try {
                                            $date = new DateTime($c['date_commentaire']);
                                            echo $date->format('d/m/Y H:i');
                                        } catch (Exception $e) {
                                            echo htmlspecialchars($c['date_commentaire']);
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="comment-text">
                                    <?php echo nl2br(htmlspecialchars($c['contenu'] ?? '')); ?>
                                </div>
                                <div>
                                    <span class="reply-link" onclick="toggleReplyArea(<?= $fid; ?>, <?= $cid; ?>);">
                                        Répondre
                                    </span>
                                </div>

                                <!-- zone de réponse inline pour ce commentaire -->
                                <form class="reply-form" id="reply_form_<?= $fid; ?>_<?= $cid; ?>" style="display:none;"
                                      action="<?php echo $isBackOfficeContext && !$isFrontOfficeInclude ? '../../front_office/index.php?section=forum' : 'index.php?section=forum'; ?>" 
                                      method="POST">
                                    <input type="hidden" name="action" value="store_comment">
                                    <input type="hidden" name="id_forum" value="<?= $fid; ?>">
                                    <input type="hidden" name="parent_id" value="<?= $cid; ?>">
                                    <div class="mb-2">
                                        <textarea class="form-control" name="contenu" rows="2" placeholder="Écrire une réponse..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-paper-plane"></i> Répondre
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleReplyArea(<?= $fid; ?>, <?= $cid; ?>);">
                                        Annuler
                                    </button>
                                </form>

                                <?php
                                // réponses de ce commentaire
                                foreach ($forumComments as $rep) {
                                    if ((int)$rep['parent_id'] === $cid) {
                                        ?>
                                        <div class="comment-item reply">
                                            <div class="comment-meta">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($rep['auteur_nom'] ?? 'Auteur #' . (int)$rep['id_auteur']); ?>
                                                &nbsp;|&nbsp;
                                                <i class="fas fa-clock"></i>
                                                <?php 
                                                if (!empty($rep['date_commentaire'])) {
                                                    try {
                                                        $date = new DateTime($rep['date_commentaire']);
                                                        echo $date->format('d/m/Y H:i');
                                                    } catch (Exception $e) {
                                                        echo htmlspecialchars($rep['date_commentaire']);
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div class="comment-text">
                                                <?php echo nl2br(htmlspecialchars($rep['contenu'] ?? '')); ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function changeForumType(type) {
    document.getElementById('tab-general').classList.toggle('active', type === 'general');
    <?php if ($isFrontOfficeInclude): ?>
    document.getElementById('tab-private').classList.toggle('active', type === 'private');
    <?php endif; ?>
}

// Affiche / cache le formulaire d'ajout de commentaire
function toggleAddComment(id_forum) {
    const formId = 'add_comment_form_' + id_forum;
    const form = document.getElementById(formId);
    if (!form) return;
    
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Affiche / cache le formulaire de réponse pour un commentaire précis
function toggleReplyArea(id_forum, id_commentaire) {
    const formId = 'reply_form_' + id_forum + '_' + id_commentaire;
    const form = document.getElementById(formId);
    if (!form) return;

    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php if ($isBackOfficeContext && !$isFrontOfficeInclude): ?>
    </div>
    </div>
</body>
</html>
<?php else: ?>
</section>
<?php endif; ?>
