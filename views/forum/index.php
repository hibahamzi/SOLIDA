<div class="container mt-5">
    <h2>Liste des forums</h2>

    <a href="index.php?controller=ForumController&action=create" class="btn btn-primary mb-3">
        Nouveau forum
    </a>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Catégorie</th>
            <th>Discussion G.</th>
            <th>Discussion P.</th>
            <th>Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($forums as $forum): ?>
            <tr>
                <td><?= $forum['id_forum'] ?></td>
                <td><?= htmlspecialchars($forum['categorie']) ?></td>
                <td><?= htmlspecialchars($forum['discussion_g']) ?></td>
                <td><?= htmlspecialchars($forum['discussion_p']) ?></td>

                <td>
                    <!-- ********* IMPORTANT ********* -->
                    <a href="index.php?controller=ForumController&action=edit&id=<?= $forum['id_forum'] ?>"
                       class="btn btn-warning btn-sm">
                        Modifier
                    </a>

                    <a href="index.php?controller=ForumController&action=delete&id=<?= $forum['id_forum'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Supprimer ?')">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
