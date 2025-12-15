<?php
// generer_rapport.php - Rapport HTML adapté au thème
require_once __DIR__ . '/../../controllers/ReclamationController.php';

session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

$reclamationController = new ReclamationController();

try {
    $stats = $reclamationController->getStatistics();
    $reclamations = $reclamationController->getReclamation();
    $urgentReclamations = $reclamationController->getUrgentReclamations();
    
    // Créer le rapport HTML avec le thème du backoffice
    $html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rapport des Réclamations - SOLIDA Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: \'Roboto\', \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f7fa;
                color: #333;
                padding: 20px;
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                padding: 30px;
            }
            
            .header {
                text-align: center;
                margin-bottom: 40px;
                padding-bottom: 20px;
                border-bottom: 2px solid #4CAF50;
            }
            
            .header h1 {
                color: #4CAF50;
                font-size: 32px;
                margin-bottom: 10px;
                font-weight: 600;
            }
            
            .header p {
                color: #666;
                font-size: 14px;
            }
            
            .report-info {
                display: flex;
                justify-content: space-between;
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 30px;
                border-left: 4px solid #4CAF50;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 40px;
            }
            
            .stat-card {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                text-align: center;
                transition: transform 0.2s ease;
                border-top: 4px solid #4CAF50;
            }
            
            .stat-card:hover {
                transform: translateY(-3px);
            }
            
            .stat-card h3 {
                color: #4CAF50;
                font-size: 32px;
                margin-bottom: 8px;
                font-weight: 700;
            }
            
            .stat-card p {
                color: #666;
                font-size: 14px;
                font-weight: 500;
            }
            
            .stat-card.warning {
                border-top-color: #FFC107;
            }
            
            .stat-card.warning h3 {
                color: #FFC107;
            }
            
            .stat-card.danger {
                border-top-color: #F44336;
            }
            
            .stat-card.danger h3 {
                color: #F44336;
            }
            
            .stat-card.info {
                border-top-color: #2196F3;
            }
            
            .stat-card.info h3 {
                color: #2196F3;
            }
            
            .section-title {
                color: #2c3e50;
                margin: 30px 0 20px 0;
                padding-bottom: 10px;
                border-bottom: 2px solid #4CAF50;
                font-size: 22px;
                font-weight: 600;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            thead {
                background: linear-gradient(135deg, #4CAF50, #45a049);
            }
            
            th {
                padding: 16px 15px;
                text-align: left;
                font-weight: 600;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: white;
                border-bottom: 2px solid #dee2e6;
            }
            
            tbody tr {
                border-bottom: 1px solid #e9ecef;
                transition: background-color 0.2s ease;
            }
            
            tbody tr:hover {
                background-color: #f8f9fa;
            }
            
            td {
                padding: 16px 15px;
                font-size: 14px;
                color: #495057;
            }
            
            .badge {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .badge-success {
                background: #e8f5e9;
                color: #2e7d32;
            }
            
            .badge-warning {
                background: #fff3e0;
                color: #e65100;
            }
            
            .badge-danger {
                background: #ffebee;
                color: #c62828;
            }
            
            .badge-info {
                background: #e3f2fd;
                color: #1565c0;
            }
            
            .urgent-row {
                background: #ffebee !important;
                border-left: 4px solid #F44336;
            }
            
            .footer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
            
            .summary {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 30px 0;
                border-left: 4px solid #2196F3;
            }
            
            .summary h3 {
                color: #2c3e50;
                margin-bottom: 15px;
            }
            
            .summary ul {
                list-style: none;
                padding-left: 0;
            }
            
            .summary li {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
            }
            
            .summary li:last-child {
                border-bottom: none;
            }
            
            .summary i {
                margin-right: 10px;
                color: #4CAF50;
            }
            
            @media print {
                body {
                    background: white;
                    padding: 10px;
                }
                
                .container {
                    box-shadow: none;
                    padding: 10px;
                }
                
                .stat-card:hover {
                    transform: none;
                }
                
                .no-print {
                    display: none !important;
                }
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
                z-index: 1000;
            }
            
            .print-button:hover {
                background: #45a049;
            }
        </style>
    </head>
    <body>
        <!-- Bouton d\'impression -->
        <button class="print-button no-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer le Rapport
        </button>
        
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-file-alt"></i> Rapport des Réclamations</h1>
                <p>SOLIDA - Gestion des Réclamations</p>
                <div class="report-info">
                    <div>
                        <strong>Généré le:</strong> ' . date('d/m/Y à H:i') . '
                    </div>
                    <div>
                        <strong>Par:</strong> ' . htmlspecialchars($_SESSION['user_fullname'] ?? 'Administrateur') . '
                    </div>
                </div>
            </div>
            
            <!-- Statistiques principales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>' . $stats['total'] . '</h3>
                    <p>Réclamations totales</p>
                    <small>Depuis le début</small>
                </div>
                
                <div class="stat-card warning">
                    <h3>' . $stats['urgent'] . '</h3>
                    <p>Réclamations urgentes</p>
                    <small>Nécessitent une attention</small>
                </div>
                
                <div class="stat-card danger">
                    <h3>' . $stats['unresolved'] . '</h3>
                    <p>En attente</p>
                    <small>Non résolues</small>
                </div>
                
                <div class="stat-card info">
                    <h3>' . $stats['resolution_rate'] . '%</h3>
                    <p>Taux de résolution</p>
                    <small>Performance globale</small>
                </div>
                
                <div class="stat-card">
                    <h3>' . $stats['this_week'] . '</h3>
                    <p>Cette semaine</p>
                    <small>Nouvelles réclamations</small>
                </div>
                
                <div class="stat-card">
                    <h3>' . $stats['total_responses'] . '</h3>
                    <p>Réponses envoyées</p>
                    <small>Réponses fournies</small>
                </div>
            </div>
            
            <!-- Alertes importantes -->
            <div class="summary">
                <h3><i class="fas fa-exclamation-triangle"></i> Points d\'Attention</h3>
                <ul>';
    
    if ($stats['urgent'] > 0) {
        $html .= '
                    <li>
                        <i class="fas fa-bell"></i>
                        <strong>' . $stats['urgent'] . ' réclamation(s) urgente(s)</strong> nécessitent une attention immédiate
                    </li>';
    }
    
    if ($stats['unresolved'] > 0) {
        $html .= '
                    <li>
                        <i class="fas fa-clock"></i>
                        <strong>' . $stats['unresolved'] . ' réclamation(s) en attente</strong> de traitement ou de réponse
                    </li>';
    }
    
    if (count($reclamations) > 0) {
        $html .= '
                    <li>
                        <i class="fas fa-chart-line"></i>
                        Taux de résolution actuel: <strong>' . $stats['resolution_rate'] . '%</strong>
                    </li>';
    }
    
    $html .= '
                    <li>
                        <i class="fas fa-calendar-check"></i>
                        <strong>' . $stats['this_week'] . ' nouvelle(s) réclamation(s)</strong> cette semaine
                    </li>
                </ul>
            </div>
            
            <!-- Réclamations urgentes -->
            <h2 class="section-title"><i class="fas fa-exclamation-circle"></i> Réclamations Urgentes</h2>';
    
    if (count($urgentReclamations) > 0) {
        $html .= '
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($urgentReclamations as $reclamation) {
            $priority = strtolower($reclamation['priorite'] ?? '');
            $priorityClass = 'badge-danger';
            
            $status = strtolower($reclamation['statut'] ?? '');
            if (in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'traitée', 'traitee', 'resolved', 'closed'])) {
                $statusClass = 'badge-success';
            } elseif (in_array($status, ['en_cours', 'en cours', 'in_progress', 'in progress'])) {
                $statusClass = 'badge-warning';
            } elseif (in_array($status, ['nouveau', 'new'])) {
                $statusClass = 'badge-info';
            } else {
                $statusClass = 'badge-danger';
            }
            
            $html .= '
                    <tr class="urgent-row">
                        <td><strong>#' . htmlspecialchars($reclamation['id'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($reclamation['nom'] ?? '') . ' ' . htmlspecialchars($reclamation['prenom'] ?? '') . '</td>
                        <td>' . htmlspecialchars($reclamation['email'] ?? '') . '</td>
                        <td><span class="badge ' . $priorityClass . '">' . htmlspecialchars(ucfirst($reclamation['priorite'] ?? 'Urgente')) . '</span></td>
                        <td><span class="badge ' . $statusClass . '">' . htmlspecialchars(ucfirst($reclamation['statut'] ?? 'Nouveau')) . '</span></td>
                        <td>' . (!empty($reclamation['date']) ? date('d/m/Y', strtotime($reclamation['date'])) : '-') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>';
    } else {
        $html .= '
            <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 24px; color: #2e7d32; margin-bottom: 10px;"></i>
                <p>Aucune réclamation urgente à signaler</p>
            </div>';
    }
    
    // Dernières réclamations
    $html .= '
            <h2 class="section-title"><i class="fas fa-history"></i> Dernières Réclamations (20 plus récentes)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>';
    
    // Trier les réclamations par date (plus récentes d'abord)
    usort($reclamations, function($a, $b) {
        return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
    });
    
    $count = 0;
    foreach ($reclamations as $reclamation) {
        if ($count >= 20) break;
        
        $priority = strtolower($reclamation['priorite'] ?? '');
        if (strpos($priority, 'urgent') !== false || strpos($priority, 'haute') !== false) {
            $priorityClass = 'badge-danger';
        } elseif (strpos($priority, 'moyenne') !== false) {
            $priorityClass = 'badge-warning';
        } else {
            $priorityClass = 'badge-success';
        }
        
        $status = strtolower($reclamation['statut'] ?? '');
        if (in_array($status, ['résolu', 'resolu', 'traité', 'traite', 'traitée', 'traitee', 'resolved', 'closed'])) {
            $statusClass = 'badge-success';
        } elseif (in_array($status, ['en_cours', 'en cours', 'in_progress', 'in progress'])) {
            $statusClass = 'badge-warning';
        } elseif (in_array($status, ['nouveau', 'new'])) {
            $statusClass = 'badge-info';
        } else {
            $statusClass = 'badge-danger';
        }
        
        $rowClass = (strpos($priority, 'urgent') !== false || strpos($priority, 'haute') !== false) ? 'urgent-row' : '';
        
        $html .= '
                    <tr class="' . $rowClass . '">
                        <td><strong>#' . htmlspecialchars($reclamation['id'] ?? '') . '</strong></td>
                        <td>' . htmlspecialchars($reclamation['nom'] ?? '') . ' ' . htmlspecialchars($reclamation['prenom'] ?? '') . '</td>
                        <td>' . htmlspecialchars($reclamation['email'] ?? '') . '</td>
                        <td>' . htmlspecialchars($reclamation['telephone'] ?? '') . '</td>
                        <td><span class="badge ' . $priorityClass . '">' . htmlspecialchars(ucfirst($reclamation['priorite'] ?? 'Normal')) . '</span></td>
                        <td><span class="badge ' . $statusClass . '">' . htmlspecialchars(ucfirst($reclamation['statut'] ?? 'Nouveau')) . '</span></td>
                        <td>' . (!empty($reclamation['date']) ? date('d/m/Y', strtotime($reclamation['date'])) : '-') . '</td>
                    </tr>';
        
        $count++;
    }
    
    $html .= '
                </tbody>
            </table>
            
            <!-- Résumé analytique -->
            <div class="summary">
                <h3><i class="fas fa-chart-bar"></i> Analyse et Recommandations</h3>
                <ul>';
    
    // Recommandations basées sur les statistiques
    if ($stats['resolution_rate'] < 70) {
        $html .= '
                    <li>
                        <i class="fas fa-exclamation-circle" style="color: #FFC107;"></i>
                        <strong>Taux de résolution faible (' . $stats['resolution_rate'] . '%)</strong> - Considérez une révision des processus de traitement
                    </li>';
    }
    
    if ($stats['urgent'] > 5) {
        $html .= '
                    <li>
                        <i class="fas fa-bell" style="color: #F44336;"></i>
                        <strong>Nombre élevé de réclamations urgentes (' . $stats['urgent'] . ')</strong> - Priorisez le traitement de ces cas
                    </li>';
    }
    
    if ($stats['this_week'] > 10) {
        $html .= '
                    <li>
                        <i class="fas fa-trend-up" style="color: #2196F3;"></i>
                        <strong>Volume important cette semaine (' . $stats['this_week'] . ' nouvelles)</strong> - Augmentation significative de l\'activité
                    </li>';
    }
    
    $html .= '
                    <li>
                        <i class="fas fa-check-circle" style="color: #4CAF50;"></i>
                        <strong>' . $stats['total_responses'] . ' réponse(s) envoyée(s)</strong> - Bon niveau d\'engagement client
                    </li>
                    
                    <li>
                        <i class="fas fa-users" style="color: #9C27B0;"></i>
                        <strong>' . count($reclamations) . ' réclamation(s) au total</strong> - Base de données des réclamations
                    </li>
                </ul>
            </div>
            
            <div class="footer">
                <p><i class="fas fa-shield-alt"></i> Rapport confidentiel - SOLIDA Administration</p>
                <p>© ' . date('Y') . ' SOLIDA - Tous droits réservés | Document généré automatiquement</p>
                <p style="margin-top: 10px; font-size: 10px; color: #999;">
                    Ce rapport est destiné à un usage interne uniquement. Les données sont mises à jour en temps réel.
                </p>
            </div>
        </div>
        
        <script>
            // Fonction pour améliorer l\'impression
            document.addEventListener("DOMContentLoaded", function() {
                const printButton = document.querySelector(".print-button");
                if (printButton) {
                    printButton.addEventListener("click", function() {
                        window.print();
                    });
                }
            });
            
            // Ajouter la date d\'impression au pied de page
            window.onbeforeprint = function() {
                const footer = document.querySelector(".footer");
                if (footer) {
                    const printDate = document.createElement("p");
                    printDate.style.fontSize = "10px";
                    printDate.style.color = "#999";
                    printDate.innerHTML = "<i class=\'fas fa-print\'></i> Imprimé le: " + new Date().toLocaleString("fr-FR") + " | Page 1/1";
                    footer.appendChild(printDate);
                }
            };
        </script>
    </body>
    </html>';
    
    // Afficher le rapport
    echo $html;
    
} catch (Exception $e) {
    // En cas d'erreur, rediriger vers le backoffice avec un message d'erreur
    error_log("Erreur lors de la génération du rapport: " . $e->getMessage());
    header('Location: BackofficeReclamations.php?error=report_failed');
    exit();
}
?>