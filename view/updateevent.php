<?php
// solida/view/updateevent.php - Gestion complète de la modification d'un événement (UPDATE)

// Inclure la connexion à la base de données
include '../config.php'; 

$event_data = null; // Contiendra les données de l'événement à modifier
$message = '';      // Message de succès ou d'erreur

// --- A. TRAITEMENT DE LA MISE À JOUR (MÉTHODE POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupérer l'ID de l'événement à partir du champ caché
    $id_event = (int)$_POST['event-id']; 
    
    // Récupérer les données mises à jour
    $titre = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? null; 
    $heure = $_POST['time'] ?? '00:00:00';
    $lieu = $_POST['location'] ?? '';
    $categorie = $_POST['category'] ?? 'non-classifié';
    $statut = $_POST['statut'] ?? 'Approche'; 

    // Vérification de base
    if (empty($titre) || empty($date) || empty($lieu)) {
        $message = "❌ Erreur : Le titre, la date et le lieu sont obligatoires.";
    } else {
        
        // Requête de mise à jour (UPDATE) avec requêtes préparées
        $sql_update = "UPDATE evenements SET 
                        titre = ?, description = ?, date = ?, heure = ?, lieu = ?, categorie = ?, statut = ?
                       WHERE id_event = ?";

        $stmt = $conn->prepare($sql_update);
        // "sssssssi" signifie : 7 chaînes (s) suivies de l'ID qui est un entier (i)
        $stmt->bind_param("sssssssi", $titre, $description, $date, $heure, $lieu, $categorie, $statut, $id_event);

        if ($stmt->execute()) {
            $success_message = "✅ Événement (ID: $id_event) mis à jour avec succès !";
            $stmt->close();
            $conn->close();
            
            // Redirection vers la liste des événements après succès
            header("Location: listevent.php?msg=" . urlencode($success_message));
            exit();
        } else {
            $message = "❌ Erreur lors de la mise à jour: " . $stmt->error;
            $stmt->close();
        }
    }
}

// --- B. CHARGEMENT INITIAL DES DONNÉES (MÉTHODE GET) ---

// Déterminer l'ID à charger (soit via GET, soit l'ID traité par POST si l'update a échoué)
$id_to_load = isset($_GET['id']) ? (int)$_GET['id'] : (isset($id_event) ? $id_event : 0);

if ($id_to_load > 0) {
    
    // Requête pour sélectionner l'événement
    $sql_select = "SELECT * FROM evenements WHERE id_event = ?";
    $stmt = $conn->prepare($sql_select);
    $stmt->bind_param("i", $id_to_load);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $event_data = $result->fetch_assoc();
    } else {
        // Redirection si l'ID n'existe pas en base
        $stmt->close();
        $conn->close();
        header("Location: listevent.php?msg=" . urlencode("❌ Événement non trouvé pour l'ID spécifié."));
        exit();
    }
    $stmt->close();
} else {
     // Redirection si aucun ID n'est fourni
     $conn->close();
     header("Location: listevent.php?msg=" . urlencode("❌ ID d'événement non spécifié."));
     exit();
}
$conn->close(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESTION - Modifier Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --color-primary-green-shop: #4CAF50;
            --color-secondary-green: #28a745; 
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-border: #ccc;
            --color-background-light: #f4f7f9;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--color-background-light);
            color: var(--color-text-dark);
        }

        header {
            background-color: var(--color-primary-green-shop);
            color: var(--color-text-light);
            padding: 20px 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-container { padding: 20px 40px; max-width: 800px; margin: 20px auto; }
        .crud-form { background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
        .form-group label { font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input, .form-group select, .form-group textarea { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1em; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background-color 0.3s; margin-right: 10px; text-decoration: none; display: inline-block; }
        .btn-primary { background-color: #4CAF50; color: white; }
        .btn-cancel { background-color: #6c757d; color: white; }
        
        .alert-error {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <h1>GESTION | Modifier Événement (ID: <?php echo htmlspecialchars($id_to_load); ?>)</h1>
        </div>
    </header>

    <div class="admin-container">
        
        <?php if ($message): ?>
            <div class="alert-error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="crud-form">
            <h3 id="form-title">Modification de l'Événement</h3>

            <form action="updateevent.php" method="POST">
                <input type="hidden" id="event-id" name="event-id" value="<?php echo htmlspecialchars($event_data['id_event']); ?>">

                <div class="form-group">
                    <label for="title">Titre de l'événement</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($event_data['titre']); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($event_data['description']); ?></textarea>
                </div>

                <div class="form-group" style="display: flex; flex-direction: row; gap: 20px;">
                    <div style="flex-grow: 1;">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($event_data['date']); ?>">
                    </div>
                    <div style="flex-grow: 1;">
                        <label for="time">Heure</label>
                        <input type="time" id="time" name="time" required value="<?php echo htmlspecialchars($event_data['heure']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Lieu</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($event_data['lieu']); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" required>
                        <option value="sport" <?php if ($event_data['categorie'] == 'sport') echo 'selected'; ?>>Sport</option>
                        <option value="culture" <?php if ($event_data['categorie'] == 'culture') echo 'selected'; ?>>Culture & Arts</option>
                        <option value="ecologie" <?php if ($event_data['categorie'] == 'ecologie') echo 'selected'; ?>>Écologie & Solidarité</option>
                        <option value="formation" <?php if ($event_data['categorie'] == 'formation') echo 'selected'; ?>>Formation & Ateliers</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" required>
                        <option value="Approche" <?php if ($event_data['statut'] == 'Approche') echo 'selected'; ?>>Approche</option>
                        <option value="En Cours" <?php if ($event_data['statut'] == 'En Cours') echo 'selected'; ?>>En Cours</option>
                        <option value="Terminée" <?php if ($event_data['statut'] == 'Terminée') echo 'selected'; ?>>Terminée</option>
                        <option value="Annulé" <?php if ($event_data['statut'] == 'Annulé') echo 'selected'; ?>>Annulé</option>
                    </select>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                    <a href="listevent.php" class="btn btn-cancel" style="text-decoration: none;"><i class="fas fa-times-circle"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>