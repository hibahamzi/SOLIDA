<?php
// addevent.php - Gestion complète de l'ajout (Formulaire + Traitement)

// Le chemin d'accès à config.php est un niveau au-dessus (../)
include '../config.php'; 

$message = ''; // Pour afficher un message d'erreur éventuel sur cette page

// --- 1. TRAITEMENT DE LA SOUMISSION DU FORMULAIRE (MÉTHODE POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération des données du formulaire
    $titre       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date        = $_POST['date'] ?? null; 
    $heure       = $_POST['time'] ?? '00:00:00';
    $lieu        = $_POST['location'] ?? '';
    $categorie   = $_POST['category'] ?? 'non-classifié';
    $statut      = $_POST['statut'] ?? 'Approche'; 

    // Vérification minimale des champs
    if (empty($titre) || empty($date) || empty($lieu)) {
        $message = "❌ Erreur : Le titre, la date et le lieu sont obligatoires.";
    } else {
        
        // Requête d'insertion (CREATE) avec requêtes préparées
        $sql = "INSERT INTO evenements (titre, description, date, heure, lieu, categorie, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        // "sssssss" signifie que nous lions 7 chaînes de caractères
        $stmt->bind_param("sssssss", $titre, $description, $date, $heure, $lieu, $categorie, $statut);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $success_message = "✅ Événement créé avec succès (ID: $new_id) !";
            
            $stmt->close();
            $conn->close();

            // 2. REDIRECTION vers la page de liste avec un message de succès
            header("Location: listevent.php?msg=" . urlencode($success_message));
            exit(); // Terminer le script après la redirection
        } else {
            $message = "❌ Erreur lors de l'insertion en base de données : " . $stmt->error;
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESTION | Ajouter un Événement</title>
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
            <h1>GESTION | Ajouter un Événement</h1>
        </div>
    </header>

    <div class="admin-container">
        
        <?php if ($message): ?>
            <div class="alert-error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="crud-form">
            <h3 id="form-title">Créer un Nouvel Événement</h3>
            
            <form id="event-form" action="addevent.php" method="POST"> 

                <div class="form-group">
                    <label for="title">Titre de l'événement</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>

                <div class="form-group" style="display: flex; flex-direction: row; gap: 20px;">
                    <div style="flex-grow: 1;">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    <div style="flex-grow: 1;">
                        <label for="time">Heure</label>
                        <input type="time" id="time" name="time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Lieu</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <select id="category" name="category" required>
                        <option value="">-- Choisir une catégorie --</option>
                        <option value="sport">Sport</option>
                        <option value="culture">Culture & Arts</option>
                        <option value="ecologie">Écologie & Solidarité</option>
                        <option value="formation">Formation & Ateliers</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" required>
                        <option value="Approche">Approche</option>
                        <option value="En Cours">En Cours</option>
                        <option value="Terminée">Terminée</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" id="save-button"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="listevent.php" class="btn btn-cancel" style="text-decoration: none;"><i class="fas fa-times-circle"></i> Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>