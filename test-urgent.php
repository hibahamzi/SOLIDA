<?php
// Script de test pour vérifier les réclamations urgentes
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/controllers/ReclamationController.php';

$controller = new ReclamationController();

echo "<h2>Test des Réclamations Urgentes</h2>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#4CAF50;color:white;}</style>";

// Test 1: Récupérer toutes les réclamations
echo "<h3>1. Toutes les réclamations</h3>";
$all = $controller->getReclamation();
echo "<p>Total: " . count($all) . " réclamations</p>";

// Test 2: Récupérer les réclamations urgentes
echo "<h3>2. Réclamations urgentes (méthode getUrgentReclamations)</h3>";
$urgent = $controller->getUrgentReclamations();
echo "<p><strong>Nombre trouvé: " . count($urgent) . "</strong></p>";

if (count($urgent) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Priorité</th><th>Statut</th><th>Date</th></tr>";
    foreach ($urgent as $r) {
        echo "<tr>";
        echo "<td>" . ($r['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($r['nom'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($r['prenom'] ?? 'N/A') . "</td>";
        echo "<td><strong style='color:red;'>" . htmlspecialchars($r['priorite'] ?? 'N/A') . "</strong></td>";
        echo "<td>" . htmlspecialchars($r['statut'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($r['date'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>Aucune réclamation urgente trouvée!</p>";
}

// Test 3: Requête SQL directe
echo "<h3>3. Test SQL direct</h3>";
try {
    $conn = config::getConnexion();
    $sql = "SELECT id, nom, prenom, priorite, statut, date FROM reclamations WHERE LOWER(priorite) LIKE '%urgent%'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $direct_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Requête SQL directe: " . count($direct_results) . " résultats</strong></p>";
    
    if (count($direct_results) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Priorité</th><th>Statut</th><th>Date</th></tr>";
        foreach ($direct_results as $r) {
            $statut_lower = strtolower(trim($r['statut'] ?? ''));
            $is_resolved = in_array($statut_lower, ['résolu', 'clôturé', 'resolu', 'cloture', 'traité', 'traite', 'traitée', 'traitee']);
            $row_style = $is_resolved ? "style='background:#ffcccc;'" : "style='background:#ccffcc;'";
            
            echo "<tr $row_style>";
            echo "<td>" . ($r['id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($r['nom'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($r['prenom'] ?? 'N/A') . "</td>";
            echo "<td><strong style='color:red;'>" . htmlspecialchars($r['priorite'] ?? 'N/A') . "</strong></td>";
            echo "<td>" . htmlspecialchars($r['statut'] ?? 'N/A') . ($is_resolved ? " <span style='color:red;'>(RÉSOLU)</span>" : " <span style='color:green;'>(ACTIF)</span>") . "</td>";
            echo "<td>" . htmlspecialchars($r['date'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>Aucune réclamation avec 'urgent' dans la priorité!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Erreur: " . $e->getMessage() . "</p>";
}

// Test 4: Vérifier toutes les priorités uniques
echo "<h3>4. Toutes les priorités dans la base</h3>";
try {
    $conn = config::getConnexion();
    $sql = "SELECT DISTINCT priorite, COUNT(*) as count FROM reclamations GROUP BY priorite";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $priorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Priorité</th><th>Nombre</th></tr>";
    foreach ($priorites as $p) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($p['priorite'] ?? 'N/A') . "</td>";
        echo "<td>" . ($p['count'] ?? 0) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Erreur: " . $e->getMessage() . "</p>";
}

?>



