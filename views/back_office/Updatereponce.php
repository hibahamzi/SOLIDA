<?php
// Assurez-vous d'inclure votre contrôleur et de gérer l'erreur de chemin (Config.php)
require_once __DIR__ . '/../../controllers/ReclamationController.php';

$reclamationController = new ReclamationController();
$message = '';

// --- 1. Gérer la soumission du formulaire de modification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_reponse'])) {
    $id_reponse = $_POST['id_reponse'];
    $nouveau_contenu = $_POST['contenu_reponse'];
    
    $result = $reclamationController->modifierReponseReclamation($id_reponse, $nouveau_contenu);
    $message = $result['message'];
    
    // Rediriger ou rafraîchir pour voir la modification
    if ($result['success']) {
        header("Location: votre_page_reclamations.php?success=reponse_modifiee");
        exit();
    }
}

// --- 2. Récupérer la réponse existante pour pré-remplir le formulaire ---
if (!isset($_GET['id_reponse']) || !is_numeric($_GET['id_reponse'])) {
    die("ID de réponse manquant ou invalide.");
}

$id_reponse_a_modifier = $_GET['id_reponse'];
// Utilisez la fonction getReponseById que nous avons ajoutée au modèle
$reponse_existante = $reclamationController->reclamationModel->getReponseById($id_reponse_a_modifier);

if (!$reponse_existante) {
    die("Réponse introuvable.");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la Réponse</title>
    <!-- Inclure vos CSS ici -->
</head>
<body>

    <h1>Modifier la Réponse à la Réclamation #<?php echo htmlspecialchars($reponse_existante['id_reclamation']); ?></h1>

    <?php if (!empty($message)): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="id_reponse" value="<?php echo htmlspecialchars($reponse_existante['id_reponse']); ?>">
        
        <label for="contenu_reponse">Contenu de la Réponse :</label>  

        <textarea id="contenu_reponse" name="contenu_reponse" rows="10" cols="50" required><?php echo htmlspecialchars($reponse_existante['contenu_reponse']); ?></textarea>  
  

        
        <button type="submit" name="modifier_reponse">Enregistrer les Modifications</button>
        <a href="votre_page_reclamations.php">Annuler</a>
    </form>

</body>
</html>
