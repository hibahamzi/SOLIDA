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
    die("Connexion échouée : " . $conn->connect_error);
}

// Charger toutes les réclamations
$sql = "SELECT * FROM reclamations ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Erreur SQL : " . $conn->error);
}

$reclamations = $result->fetch_all(MYSQLI_ASSOC);
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
        .status-nouveau { background-color: #2196F3; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-en-cours { background-color: #FF9800; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-resolu, .status-résolu { background-color: #4CAF50; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-ferme, .status-fermé { background-color: #9E9E9E; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; }
    </style>
</head>
<body>

<div class="container">
  <h2>📋 Liste des Réclamations</h2>
   <a href="Vue/Addreclam.php" class="btn-add">+ Ajouter une réclamation</a> <table>
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
                <td colspan="10" style="text-align:center;">Aucune réclamation trouvée</td>
            </tr>
        <?php else: ?>
            <?php foreach ($reclamations as $r): ?>
                <?php
                $statut = isset($r['statut']) ? $r['statut'] : 'inconnu';
                $statutClass = "status-" . strtolower(str_replace([' ', 'é'], ['-', 'e'], $statut));
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
                        <a href="../vue/Updatereclam.php?id=<?= $r['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="../vue/DeleteReclam.php?id=<?= $r['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
