<?php
// listevent.php - Page principale d'administration (READ)

// Inclure la connexion. Nous supposons que config.php est un niveau au-dessus de 'view'
include '../config.php'; 

// Récupérer un message de succès (après un ajout, modification ou suppression)
$message = '';
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GESTION | Liste des Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* --- Styles CSS (basé sur votre design) --- */
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

        .logo h1 {
            margin: 0;
            font-size: 1.8em;
        }
        
        .admin-container {
            padding: 20px 40px;
            max-width: 1200px;
            margin: 20px auto;
        }
        
        h2 {
            color: var(--color-primary-green-shop);
            border-bottom: 3px solid var(--color-primary-green-shop);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--color-text-light);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .event-table th, .event-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .event-table th {
            background-color: var(--color-primary-green-shop);
            color: var(--color-text-light);
            font-weight: normal;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .event-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .event-table td:last-child {
            display: flex;
            gap: 5px;
        }
        
        .btn {
             padding: 6px 10px;
             border: none;
             border-radius: 5px;
             cursor: pointer;
             font-weight: bold;
             transition: background-color 0.3s;
             font-size: 0.9em;
             text-decoration: none; /* Pour les liens <a> qui agissent comme boutons */
             display: inline-block;
        }

        .btn-edit {
            background-color: #ffc107; /* Jaune */
            color: var(--color-text-dark);
        }

        .btn-delete {
            background-color: #dc3545; /* Rouge */
            color: var(--color-text-light);
        }
        
        .btn-add {
            background-color: var(--color-secondary-green);
            color: var(--color-text-light);
            padding: 10px 15px;
        }

        .alert-success {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <h1>GESTION | Liste des Événements</h1>
        </div>
    </header>

    <div class="admin-container">
        
        <?php if ($message): ?>
            <div class="alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px; text-align: right;">
            <a href="addevent.php" class="btn btn-add"><i class="fas fa-plus-circle"></i> Ajouter un nouvel événement</a>
        </div>
        
        <h2>Liste des Événements</h2>
        
        <table class="event-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Lieu</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="event-list-body">
                
                <?php
                // --- LOGIQUE PHP POUR LA LECTURE DES DONNÉES (READ) ---
                
                // Connexion à la base de données via config.php est déjà faite
                // Requête SQL pour sélectionner les données de la table 'evenements'
                $sql = "SELECT id_event, titre, date, lieu, categorie, statut FROM evenements ORDER BY date ASC, heure ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Boucle pour afficher chaque ligne d'événement
                    while($row = $result->fetch_assoc()) {
                        
                        // Formater la date pour l'affichage (JJ/MM/AAAA)
                        $formatted_date = date("d/m/Y", strtotime($row["date"]));
                        
                        echo "<tr>";
                        echo "<td>" . $row["id_event"] . "</td>";
                        echo "<td>" . htmlspecialchars($row["titre"]) . "</td>";
                        echo "<td>" . $formatted_date . "</td>";
                        echo "<td>" . htmlspecialchars($row["lieu"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["categorie"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["statut"]) . "</td>";
                        echo "<td>";
                        
                        // Boutons d'action : Modifier et Supprimer
                        echo "<a href='updateevent.php?id=" . $row["id_event"] . "' class='btn btn-edit'><i class='fas fa-edit'></i> Modifier</a>";
                        // Utilisation d'un confirm JavaScript pour la suppression
                        echo "<a href='deleteevent.php?id=" . $row["id_event"] . "' class='btn btn-delete' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cet événement ?\");'><i class='fas fa-trash'></i> Supprimer</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>Aucun événement n'est actuellement disponible dans la table 'evenements'.</td></tr>";
                }

                // Fermer la connexion à la base de données
                $conn->close();
                ?>
                
            </tbody>
        </table>
        
    </div>

</body>
</html>