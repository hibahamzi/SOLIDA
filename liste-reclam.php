<?php
// ------------------------------
// 🔧 Afficher les erreurs (pour debug)
// ------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------------------
// 🔧 Connexion MySQL
// ------------------------------
$conn = new mysqli("localhost", "root", "", "reclamations_db");
$conn->set_charset("utf8");

// Vérifier la connexion
if ($conn->connect_error) {
    // Arrêter l'exécution et afficher une erreur de connexion fatale
    die("Connexion échouée : " . $conn->connect_error);
}

// ------------------------------
// 📊 Chargement des réclamations
// ------------------------------
$sql = "SELECT * FROM reclamations ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    // Afficher une erreur SQL si la requête échoue
    die("Erreur SQL : " . $conn->error);
}

// Récupérer toutes les lignes dans un tableau associatif
$reclamations = $result->fetch_all(MYSQLI_ASSOC);

// Fermer la connexion
$conn->close();

// ------------------------------
// 💬 Messages de notification (après redirection)
// ------------------------------
$message = '';
$messageType = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'added') {
        $message = 'La nouvelle réclamation a été ajoutée avec succès !';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'updated') {
        $message = 'La réclamation a été mise à jour avec succès !';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'deleted') {
        $message = 'La réclamation a été supprimée avec succès.';
        $messageType = 'success';
    }
} elseif (isset($_GET['error'])) {
    $message = 'Une erreur est survenue lors de l\'opération.';
    $messageType = 'error';
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Réclamations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            display: flex;
            align-items: center;
            color: #961096;
            margin-bottom: 20px;
            border-bottom: 2px solid #6903769c;
            padding-bottom: 10px;
        }
        .btn-add {
            background-color: #65cff6;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #961096;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        /* Styles pour le statut */
        .status-nouveau { background-color: #2196F3; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-en-cours { background-color: #FF9800; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-resolu, .status-résolu { background-color: #4CAF50; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-ferme, .status-fermé { background-color: #9E9E9E; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        
        /* Styles pour les messages d'alerte */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
            font-weight: bold;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📋 Liste des Réclamations</h2>
    
    <?php if ($message): // Affichage du message de succès ou d'erreur ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <a href="Vue/Addreclam.php" class="btn-add">+ Ajouter une réclamation</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Gouvernorat</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($reclamations)): ?>
            <tr>
                <td colspan="10" style="text-align:center; padding: 20px; color: #f44336;">
                    ❌ **Aucune réclamation trouvée** dans la base de données.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($reclamations as $r): ?>
                <?php
                // Utiliser 'inconnu' comme statut par défaut si la colonne manque ou est vide
                $statut = isset($r['statut']) ? $r['statut'] : 'Inconnu';
                
                // Générer la classe CSS à partir du statut (ex: "En cours" devient "status-en-cours")
                $statutClass = "status-" . strtolower(str_replace([' ', 'é', '-'], ['-', 'e', ''], $statut));
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['id']) ?></td>
                    <td><?= htmlspecialchars($r['nom']) ?></td>
                    <td><?= htmlspecialchars($r['prenom']) ?></td>
                    <td>+216 <?= htmlspecialchars($r['telephone']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars($r['gouvernorat']) ?></td>
                    <td><?= htmlspecialchars($r['priorite']) ?></td>
                    <td><span class="<?= $statutClass ?>"><?= htmlspecialchars($statut) ?></span></td>
                    <td><?= htmlspecialchars($r['date']) ?></td>
                    <td class="action-buttons">
                        <a href="Vue/Updatereclam.php?id=<?= $r['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="Vue/DeleteReclam.php?id=<?= $r['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>