<?php
// views/forum/show.php - Front Office Forum Show
// This file is included by index.php, so navbar and footer are already included
// $forum and $comments should be set by the controller

// If accessed directly, redirect or set up
if (!isset($forum)) {
    // If not set, try to get from GLOBALS (set by controller)
    $forum = $GLOBALS['forum'] ?? null;
    $comments = $GLOBALS['comments'] ?? [];
}

if (!$forum) {
    echo '<div class="container py-5"><div class="alert alert-warning">Forum introuvable.</div></div>';
    return;
}
?>
<section class="container py-5">
    <div class="row">
        <div class="col-12">
            <a href="index.php?section=forum" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour au Forum
            </a>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Forum #<?= htmlspecialchars($forum['id_forum'] ?? '') ?></h4>
                    <small class="text-muted">
                        Catégorie: <strong><?= htmlspecialchars($forum['categorie'] ?? '') ?></strong>
                    </small>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($forum['discussion_g'] ?? '') ?></h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($forum['discussion_p'] ?? '')) ?></p>
                    <hr>
                    
                    <h6>Commentaires</h6>
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <small class="text-muted">
                                        <?= htmlspecialchars($c['auteur_nom'] ?? 'Anonyme') ?> — 
                                        <?= htmlspecialchars($c['date_commentaire'] ?? '') ?>
                                    </small>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($c['contenu'] ?? '')) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Aucun commentaire pour le moment.</p>
                    <?php endif; ?>
                    
                    <hr>
                    <h6>Ajouter un commentaire</h6>
                    <form method="post" action="index.php?section=forum">
                        <input type="hidden" name="action" value="store_comment">
                        <input type="hidden" name="id_forum" value="<?= (int)($forum['id_forum'] ?? 0) ?>">
                        <div class="mb-3">
                            <label for="contenu" class="form-label">Votre commentaire</label>
                            <textarea name="contenu" id="contenu" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Publier
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
