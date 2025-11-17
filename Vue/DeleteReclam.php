<?php
// ------------------------------
// 🔧 Afficher les erreurs (pour debug) - À désactiver en production
// ------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------------------
// 🔧 Connexion MySQL
// ------------------------------
// Assurez-vous que les informations de connexion sont correctes
$conn = new mysqli("localhost", "root", "", "reclamations_db");
$conn->set_charset("utf8");

// Vérifier la connexion
if ($conn->connect_error) {
    // Rediriger vers la page de liste avec un message d'erreur si la connexion échoue
    header("Location: ../liste-reclam.php?error=db_connect_failed");
    exit();
}

// ------------------------------
// 🔑 Traitement de la suppression
// ------------------------------

// 1. Vérifier si l'ID est passé dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Utiliser intval pour s'assurer que l'ID est un entier (sécurité supplémentaire)
    $id_a_supprimer = intval($_GET['id']);

    // 2. Préparer la requête de suppression sécurisée
    // IMPORTANT : Utilisation des requêtes préparées pour prévenir l'injection SQL
    $sql = "DELETE FROM reclamations WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    // Lier l'ID (le 'i' indique que la variable est un entier)
    $stmt->bind_param("i", $id_a_supprimer);

    // 3. Exécuter la requête
    if ($stmt->execute()) {
        // Suppression réussie
        // Rediriger vers la page de liste avec le statut 'deleted'
        header("Location: ../liste-reclam.php?success=deleted");
        exit();
    } else {
        // Erreur lors de l'exécution (ex: ID non trouvé ou problème de permission)
        // Rediriger vers la page de liste avec un message d'erreur
        header("Location: ../liste-reclam.php?error=sql_execution_failed");
        exit();
    }
    
    $stmt->close();
} else {
    // Aucun ID spécifié dans l'URL (tentative d'accès direct)
    header("Location: ../liste-reclam.php?error=no_id_specified");
    exit();
}

// Fermer la connexion
$conn->close();
?>