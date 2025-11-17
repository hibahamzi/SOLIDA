<div class="container mt-5">
    <h2>Modifier le Forum</h2>

    <form method="POST" action="index.php?controller=ForumController&action=update">

        <!-- TRÈS IMPORTANT !!! sinon update maykhdemch -->
        <input type="hidden" name="id" value="<?= $forum['id_forum'] ?>">

        <div class="form-group mb-3">
            <label>Catégorie</label>
            <input type="text" name="categorie" class="form-control"
                   value="<?= $forum['categorie'] ?>" required>
        </div>

        <div class="form-group mb-3">
            <label>Discussion Générale</label>
            <textarea name="discussion_g" class="form-control" required><?= $forum['discussion_g'] ?></textarea>
        </div>

        <div class="form-group mb-3">
            <label>Discussion Privée</label>
            <textarea name="discussion_p" class="form-control" required><?= $forum['discussion_p'] ?></textarea>
        </div>

        <button class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
