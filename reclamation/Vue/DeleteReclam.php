<?php
// -------------------------------
// 🔧 Connexion à MySQL
// -------------------------------
$conn = new mysqli("localhost", "root", "", "reclamations_db");
$conn->set_charset("utf8");

$alert = "";
$reclamation = null;

// ----------------------------------------------
// 🔍 RECHERCHE (quand on clique "Rechercher")
// ----------------------------------------------
if (isset($_POST['search'])) {
    $searchId = $_POST['search-id'];
    $searchEmail = $_POST['search-email'];

    if (!empty($searchEmail)) {
        $sql = "SELECT * FROM reclamations WHERE id=? AND email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $searchId, $searchEmail);
    } else {
        $sql = "SELECT * FROM reclamations WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $searchId);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $reclamation = $result->fetch_assoc();

    if ($reclamation) {
        $alert = "<div class='alert alert-success'>Réclamation trouvée ! Vérifiez avant suppression.</div>";
    } else {
        $alert = "<div class='alert alert-danger'>Aucune réclamation trouvée.</div>";
    }
}

// -----------------------------------------------------
// 🗑️ SUPPRESSION (quand on clique "Supprimer")
// -----------------------------------------------------
if (isset($_POST['delete'])) {
    if ($_POST['confirmation'] !== "oui") {
        $alert = "<div class='alert alert-danger'>Vous devez confirmer la suppression.</div>";
    } else {
        $deleteId = $_POST['reclamation-id'];
        $sql = "DELETE FROM reclamations WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $deleteId);

        if ($stmt->execute()) {
            $alert = "<div class='alert alert-success'>Réclamation supprimée avec succès !</div>";
            $reclamation = null;
        } else {
            $alert = "<div class='alert alert-danger'>Erreur lors de la suppression.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supprimer une Réclamation</title>

<style>
/* 🔥 Ton CSS reste 100% identique — rien n'a été changé */
<?php echo file_get_contents("style.css"); ?> 
/* Si ton CSS est dans ce même fichier, colle ton CSS ici */
</style>

</head>
<body>

<div class="container">

    <div class="navigation">
        <a href="../index.html" class="nav-btn">🏠 Accueil</a>
        <a href="liste-reclam.php" class="nav-btn">📋 Liste des réclamations</a>
    </div>

    <h2>🗑️ Supprimer une Réclamation</h2>

    <!-- 🔔 Affichage du message -->
    <div id="alert-message">
        <?= $alert ?>
    </div>

    <!-- 🔍 FORM RECHERCHE -->
    <div class="section-header">🔍 Rechercher une Réclamation</div>

    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label class="required">ID de la réclamation</label>
                <input type="text" name="search-id" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="text" name="search-email">
            </div>
        </div>

        <button type="submit" name="search" class="btn-submit" style="background-color:#65cff6;">🔍 Rechercher</button>
    </form>

    <!-- 📋 AFFICHAGE DES DÉTAILS SI TROUVÉ -->
    <?php if ($reclamation): ?>
    <div id="reclamation-info">
        <div class="section-header">📋 Détails de la Réclamation</div>

        <div class="reclamation-details">
            <div class="detail-row"><div class="detail-label">ID:</div><div class="detail-value"><?= $reclamation['id'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Nom:</div><div class="detail-value"><?= $reclamation['nom'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Prénom:</div><div class="detail-value"><?= $reclamation['prenom'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Téléphone:</div><div class="detail-value">+216 <?= $reclamation['telephone'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Email:</div><div class="detail-value"><?= $reclamation['email'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Gouvernorat:</div><div class="detail-value"><?= $reclamation['gouvernorat'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Priorité:</div><div class="detail-value"><?= $reclamation['priorite'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Statut:</div><div class="detail-value"><?= $reclamation['statut'] ?></div></div>
            <div class="detail-row"><div class="detail-label">Date:</div><div class="detail-value"><?= $reclamation['date'] ?></div></div>
        </div>

        <!-- 🗑️ FORM SUPPRESSION -->
        <form method="POST">
            <input type="hidden" name="reclamation-id" value="<?= $reclamation['id'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Confirmer la suppression</label>
                    <select name="confirmation" required>
                        <option value="">-- Choisir une option --</option>
                        <option value="oui">Oui, je confirme la suppression</option>
                        <option value="non">Non, annuler</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="delete" class="btn-submit">🗑️ Supprimer définitivement</button>
        </form>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
