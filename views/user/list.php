<!doctype html><html><head><meta charset="utf-8"><title>Utilisateurs</title></head><body>
<h1>Utilisateurs</h1>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>ID</th><th>Nom</th><th>Email</th></tr>
<?php foreach($users as $u): ?>
<tr>
    <td><?= htmlspecialchars($u['id_utilisateur']) ?></td>
    <td><?= htmlspecialchars($u['nom_prenom']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<a href="index.php?action=forum_index">Retour</a>
</body></html>