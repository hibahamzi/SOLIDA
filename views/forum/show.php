<!doctype html><html><head><meta charset="utf-8"><title>Voir Forum</title></head><body>
<h1>Forum #<?= htmlspecialchars($forum['id_forum']) ?></h1>
<p><strong>Catégorie:</strong> <?= htmlspecialchars($forum['categorie']) ?></p>
<p><strong>Discussion G:</strong><br><?= nl2br(htmlspecialchars($forum['discussion_g'])) ?></p>
<p><strong>Discussion P:</strong><br><?= nl2br(htmlspecialchars($forum['discussion_p'])) ?></p>
<hr>
<h2>Commentaires</h2>
<?php if(!empty($commentaires)): foreach($commentaires as $c): ?>
    <div style="border:1px solid #ccc;padding:6px;margin:6px 0;">
        <small><?= htmlspecialchars($c['nom_prenom'] ?? 'Anonyme') ?> — <?= htmlspecialchars($c['date_commentaire'] ?? '') ?></small>
        <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
    </div>
<?php endforeach; else: ?>
    <p>Aucun commentaire.</p>
<?php endif; ?>
<h3>Ajouter commentaire</h3>
<form method="post" action="index.php?action=commentaire_add">
    <input type="hidden" name="id_forum" value="<?= $forum['id_forum'] ?>">
    <label>Votre id (auteur): <input name="id_auteur" required></label><br>
    <label>Contenu: <textarea name="contenu" required></textarea></label><br>
    <button type="submit">Ajouter</button>
</form>
<p><a href="index.php?action=forum_index">Retour liste</a></p>
</body></html>