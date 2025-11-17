<?php
// deleteevent.php - Traitement de la suppression d'un événement

// Inclure la connexion à la base de données (config.php est un niveau au-dessus)
include '../config.php'; 

$message = '';

// 1. Vérifier si un ID d'événement est passé dans l'URL et est numérique
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id_event = (int)$_GET['id']; // Assurer que l'ID est un entier
    
    // 2. Préparer la requête SQL de suppression (DELETE)
    $sql = "DELETE FROM evenements WHERE id_event = ?";
    
    // Utilisation de requêtes préparées pour la sécurité
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_event); // "i" pour integer (entier)

    // 3. Exécuter la requête
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "✅ Événement (ID: $id_event) supprimé avec succès !";
        } else {
            $message = "⚠️ Erreur : Aucun événement trouvé avec l'ID $id_event.";
        }
    } else {
        $message = "❌ Erreur lors de la suppression : " . $stmt->error;
    }

    $stmt->close();
    
} else {
    $message = "❌ Erreur : ID d'événement non spécifié ou invalide.";
}

$conn->close();

// 4. Redirection vers la page de liste avec le message approprié
// urlencode() est utilisé pour s'assurer que le message est correctement transmis dans l'URL
header("Location: listevent.php?msg=" . urlencode($message));
exit(); // Terminer l'exécution du script après la redirection
?>