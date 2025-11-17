<!doctype html><html><head><meta charset="utf-8"><title>Commentaires</title></head><body>
<h1>Commentaires (liste)</h1>
<?php foreach($comments as $c): ?>
    <div style="border:1px solid #ddd;padding:8px;margin:6px 0;">
        <strong><?= htmlspecialchars($c['nom_prenom'] ?? 'Anonyme') ?></strong>
        <small><?= htmlspecialchars($c['date_commentaire'] ?? '') ?></small>
        <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
    </div>
<?php endforeach; ?>
<a href="index.php?action=forum_index">Retour</a>
</body></html>