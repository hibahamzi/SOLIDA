<!-- views/forum/create.php -->
<div class="container mt-4">
    <h2>Créer un nouveau sujet</h2>

    <!-- Zone des erreurs -->
    <div id="form-errors" style="color:red; margin-bottom:15px; font-weight:bold;"></div>

    <form id="forum-create-form" method="POST" 
          action="index.php?controller=ForumController&action=store" 
          novalidate>

        <div class="form-group mb-3">
            <label>Catégorie</label><br>
            <input type="text" name="categorie" id="categorie" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label>Discussion (général)</label><br>
            <input type="text" name="discussion_g" id="discussion_g" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label>Discussion (précis)</label><br>
            <textarea name="discussion_p" id="discussion_p" class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">Enregistrer</button>
    </form>
</div>

<script>
// ----- VALIDATION JS -----
document.getElementById('forum-create-form').addEventListener('submit', function(e) {

    let errors = [];
    let cat = document.getElementById('categorie').value.trim();
    let dg  = document.getElementById('discussion_g').value.trim();
    let dp  = document.getElementById('discussion_p').value.trim();

    if (cat === "") { errors.push("• La catégorie est obligatoire."); }
    if (dg === "")  { errors.push("• La discussion générale est obligatoire."); }
    if (dp === "")  { errors.push("• La discussion précise est obligatoire."); }

    if (errors.length > 0) {
        e.preventDefault(); // bloque l'envoi
        document.getElementById('form-errors').innerHTML = errors.join("<br>");
    }
});
</script>
