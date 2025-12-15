<?php
// Connexion à la base de données
require_once '../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

require_once '../../controllers/ReclamationController.php';
require_once '../../controllers/DealController.php';
require_once '../../controllers/ForumController.php';
require_once '../../controllers/CommentaireController.php';
require_once '../../controllers/donc.php';
require_once '../../controllers/associationc.php';
$reclamationController = new ReclamationController($pdo);
$dealController = new DealController($pdo);
$forumController = new ForumController($pdo);
$commentaireController = new CommentaireController($pdo);
$donC = new DonC();
$associationC = new AssociationC();

// Récupérer les statistiques
try {
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Compter les admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Récupérer les derniers utilisateurs inscrits
    $stmt = $pdo->query("SELECT fullname, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les statistiques des réclamations
    try {
        $stats = $reclamationController->getStatistics();
        $totalClaims = $stats['unresolved'] ?? 0;
    } catch (Exception $e) {
        $totalClaims = 0;
    }
    
    // Récupérer les réclamations pour affichage
    $allReclamations = $reclamationController->getReclamation();
    $reclamations = [];
    foreach ($allReclamations as $reclamation) {
        $id = $reclamation['id'];
        $response = $reclamationController->getResponseByReclamationId($id);
        if ($response) {
            $reclamation['has_response'] = true;
            $reclamation['reponse'] = $response['reponse'] ?? '';
            $reclamation['reponse_date'] = $response['date_creation'] ?? '';
        } else {
            $reclamation['has_response'] = false;
        }
        $reclamations[] = $reclamation;
    }
    
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Valeurs par défaut pour les autres statistiques
$totalEvents = 0; // À implémenter avec la table événements
$totalDonations = 0; // À implémenter avec la table dons

// Gérer l'action de réponse ou de visualisation
$action = $_GET['action'] ?? '';
$section = $_GET['section'] ?? '';
$selectedReclamationId = $_GET['id'] ?? null;
$selectedReclamation = null;
if ($selectedReclamationId && ($action === 'respond' || $action === 'view' || $action === 'view_response')) {
    foreach ($reclamations as $rec) {
        if ($rec['id'] == $selectedReclamationId) {
            $selectedReclamation = $rec;
            if ($action === 'view_response') {
                $response = $reclamationController->getResponseByReclamationId($selectedReclamationId);
                $selectedReclamation['response_detail'] = $response['reponse'] ?? '';
                $selectedReclamation['response_date'] = $response['date_creation'] ?? '';
                $selectedReclamation['response_statut'] = $response['statut'] ?? '';
            }
            break;
        }
    }
}

// Handle forum actions
if ($section === 'forum') {
    $forumAction = $_GET['action'] ?? '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            $forumController->update();
            exit;
        }
    } elseif ($forumAction === 'delete' && isset($_GET['id'])) {
        $forumController->delete();
        exit;
    } elseif ($forumAction === 'edit' && isset($_GET['id'])) {
        $forumController->edit();
        exit;
    } else {
        // List forums
        $forumController->adminIndex();
        exit;
    }
}

// Handle commentaire actions
if ($section === 'commentaire') {
    $commentAction = $_GET['action'] ?? '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update') {
            $commentaireController->update();
            exit;
        }
    } elseif ($commentAction === 'delete' && isset($_GET['id'])) {
        $commentaireController->delete();
        exit;
    } elseif ($commentAction === 'edit' && isset($_GET['id'])) {
        $commentaireController->edit();
        exit;
    } elseif ($commentAction === 'toggleSignal' && isset($_GET['id'])) {
        $commentaireController->toggleSignal();
        exit;
    } else {
        // List comments
        $commentaireController->adminIndex();
        exit;
    }
}

// Handle dons actions
if ($section === 'dons') {
    // Include the back office dons view
    $listeDons = $donC->listerTousDonsAvecDetails();
    $message = $_GET['msg'] ?? '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['don_action'])) {
        $donId = (int)$_POST['don_id'];
        $action = $_POST['don_action'];
        
        if ($action === 'valider' || $action === 'archiver') {
            $nouveauStatut = ($action === 'valider') ? 'Validé' : 'Archivé';
            $result = $donC->modifierStatutDon($donId, $nouveauStatut);
            
            if ($result) {
                $message = "Le don #$donId a été marqué comme **" . $nouveauStatut . "** avec succès.";
            } else {
                $message = "Erreur lors de la modification du statut du don #$donId.";
            }
            
            header('Location: dashboard.php?section=dons&msg=' . urlencode($message));
            exit();
        }
    }
    
    include __DIR__ . '/../php/backofficedons.php';
    exit;
}

// Handle associations actions
if ($section === 'associations') {
    // Include the back office associations view
    $listeAssociations = $associationC->listerAssociations();
    $message = $_GET['msg'] ?? '';
    $associationToEdit = null;
    
    if (isset($_GET['edit_asso_id'])) {
        $associationToEdit = $associationC->recupererAssociation((int)$_GET['edit_asso_id']);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asso_action'])) {
        $asso_action = $_POST['asso_action'];
        
        if ($asso_action === 'save') {
            require_once __DIR__ . '/../../models/association.php';
            $id = isset($_POST['asso_id']) ? (int)$_POST['asso_id'] : null;
            
            $association = new Association(
                $id,
                $_POST['name'],
                $_POST['description'],
                $_POST['address'] ?? 'Non spécifié',
                $_POST['country'],
                $_POST['type']
            );
            
            if ($id) {
                if ($associationC->modifierAssociation($association)) {
                    $message = "Association modifiée avec succès.";
                } else {
                    $message = "Erreur lors de la modification de l'association.";
                }
            } else {
                if ($associationC->ajouterAssociation($association)) {
                    $message = "Nouvelle association ajoutée avec succès.";
                } else {
                    $message = "Erreur lors de l'ajout de l'association.";
                }
            }
            
            header('Location: dashboard.php?section=associations&msg=' . urlencode($message));
            exit();
        } elseif ($asso_action === 'delete' && isset($_POST['asso_id'])) {
            $id = (int)$_POST['asso_id'];
            $result = $associationC->supprimerAssociation($id);
            
            if ($result) {
                $message = "L'association #$id a été supprimée avec succès.";
            } else {
                $message = "Erreur lors de la suppression de l'association #$id.";
            }
            
            header('Location: dashboard.php?section=associations&msg=' . urlencode($message));
            exit();
        }
    }
    
    include __DIR__ . '/../php/backofficedons.php';
    exit;
}

// Get deals data for deals section
$deals = [];
$sponsors = [];
$selectedSponsorId = null;
$dealStats = ['total' => 0, 'pending' => 0, 'accepted' => 0];
if ($section === 'deals') {
    $selectedSponsorId = isset($_GET['sponsor_id']) && $_GET['sponsor_id'] !== '' ? (int)$_GET['sponsor_id'] : null;
    $sort = $_GET['sort'] ?? '';
    
    // Get sponsors list
    $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
    $stmtSponsors = $pdo->query($sqlSponsors);
    $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);
    
    // Get deals
    $sql = "SELECT d.*, s.nomEntreprise
            FROM deals d
            JOIN sponsors s ON d.idSponsor = s.id";
    $params = [];
    
    if ($selectedSponsorId) {
        $sql .= " WHERE d.idSponsor = :idSponsor";
        $params[':idSponsor'] = $selectedSponsorId;
    }
    
    switch ($sort) {
        case 'deal_date_debut_recent':
            $sql .= " ORDER BY d.dateDebut DESC";
            break;
        case 'deal_periode_longue':
            $sql .= " ORDER BY d.periodeValidite DESC";
            break;
        case 'deal_montant_desc':
            $sql .= " ORDER BY d.prixInitial DESC";
            break;
        case 'deal_accept_date':
            $sql .= " ORDER BY d.dateAcceptation DESC";
            break;
        case 'deal_dispo':
            $sql .= " ORDER BY 
                        (CASE WHEN d.expire = 'NON' THEN 0 ELSE 1 END),
                        d.dateDebut DESC";
            break;
        case 'deal_expire':
            $sql .= " ORDER BY 
                        (CASE WHEN d.expire = 'OUI' THEN 0 ELSE 1 END),
                        d.dateDebut DESC";
            break;
        default:
            $sql .= " ORDER BY d.idDeal DESC";
            break;
    }
    
    if ($params) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $dealStats['total'] = count($deals);
    foreach ($deals as $d) {
        if (($d['statut'] ?? '') === 'en_attente') {
            $dealStats['pending']++;
        } elseif (($d['statut'] ?? '') === 'accepte') {
            $dealStats['accepted']++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Tableau de Bord';
        include 'includes/topbar.php'; 
        ?>
        
        <!-- Content Area -->
        <div class="content-area">
            
            <?php if (isset($error)): ?>
            <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] === 'response_sent'): ?>
            <div style="background: #2ecc71; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> Réponse envoyée avec succès!
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                
                <div class="stat-card users">
                    <div class="stat-header">
                        <h3>Utilisateurs</h3>
                        <div class="stat-icon users-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo isset($totalUsers) ? $totalUsers : '0'; ?></div>
                    <div class="stat-label">Total des utilisateurs inscrits</div>
                </div>
                
                <div class="stat-card events">
                    <div class="stat-header">
                        <h3>Événements</h3>
                        <div class="stat-icon events-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalEvents; ?></div>
                    <div class="stat-label">Événements actifs</div>
                </div>
                
                <div class="stat-card donations">
                    <div class="stat-header">
                        <h3>Dons</h3>
                        <div class="stat-icon donations-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalDonations; ?></div>
                    <div class="stat-label">Dons reçus ce mois</div>
                </div>
                
                <div class="stat-card claims">
                    <div class="stat-header">
                        <h3>Réclamations</h3>
                        <div class="stat-icon claims-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-number"><?php echo $totalClaims; ?></div>
                    <div class="stat-label">En attente de traitement</div>
                </div>
                
            </div>
            
            <!-- Recent Users Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Derniers Utilisateurs Inscrits</h2>
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Voir Tous
                    </a>
                </div>
                
                <div class="table-responsive">
                    <?php if (isset($recentUsers) && count($recentUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle" style="margin-right: 8px; color: #59ab6e;"></i>
                                    <?php echo htmlspecialchars($user['fullname']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px;"></i>
                                    <?php 
                                        $date = new DateTime($user['created_at']);
                                        echo $date->format('d/m/Y H:i'); 
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Aucun utilisateur trouvé
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reclamations Section -->
            <div class="table-container" style="margin-top: 30px;">
                <div class="table-header">
                    <h2>Gestion des Réclamations</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="dashboard.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">
                            <i class="fas fa-sync"></i> Actualiser
                        </a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <?php if (empty($reclamations)): ?>
                    <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Aucune réclamation trouvée
                    </p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom/Prénom</th>
                                <th>Email</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Réponse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reclamations as $rec): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rec['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($rec['nom'] . ' ' . $rec['prenom']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($rec['email'] ?? '—'); ?></td>
                                <td>
                                    <?php 
                                    $priorite = strtolower($rec['priorite'] ?? 'normale');
                                    $badgeClass = 'badge';
                                    if ($priorite === 'urgente') {
                                        $badgeClass = 'badge admin';
                                    }
                                    ?>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($rec['priorite'] ?? 'Normale'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($rec['statut'] ?? 'nouveau') === 'nouveau' ? 'user' : 'admin'; ?>">
                                        <?php echo htmlspecialchars($rec['statut'] ?? 'Nouveau'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($rec['date'])) {
                                        $date = new DateTime($rec['date']);
                                        echo $date->format('d/m/Y H:i');
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($rec['has_response'] ?? false): ?>
                                        <span class="badge admin" style="background: #2ecc71;">
                                            <i class="fas fa-check"></i> Répondu
                                        </span>
                                    <?php else: ?>
                                        <span class="badge user">
                                            <i class="fas fa-clock"></i> En attente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="dashboard.php?action=view&id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-small" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (!($rec['has_response'] ?? false)): ?>
                                        <a href="dashboard.php?action=respond&id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-small" 
                                           style="background: #2ecc71; color: white;"
                                           title="Répondre">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="dashboard.php?action=view_response&id=<?php echo $rec['id']; ?>" 
                                           class="btn btn-small" 
                                           style="background: #3498db; color: white;"
                                           title="Voir la réponse">
                                            <i class="fas fa-comment"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- View Reclamation Details -->
            <?php if ($action === 'view' && $selectedReclamation): ?>
            <div class="table-container" style="margin-top: 30px; padding: 25px;">
                <h3 style="color: #212934; margin-bottom: 20px;">
                    <i class="fas fa-eye" style="color: #59ab6e; margin-right: 10px;"></i>
                    Détails de la réclamation #<?php echo htmlspecialchars($selectedReclamation['id']); ?>
                </h3>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div>
                            <strong>Nom:</strong><br>
                            <?php echo htmlspecialchars($selectedReclamation['nom'] . ' ' . $selectedReclamation['prenom']); ?>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <?php echo htmlspecialchars($selectedReclamation['email'] ?? '—'); ?>
                        </div>
                        <div>
                            <strong>Téléphone:</strong><br>
                            <?php echo htmlspecialchars($selectedReclamation['telephone'] ?? '—'); ?>
                        </div>
                        <div>
                            <strong>Priorité:</strong><br>
                            <span class="badge <?php echo strtolower($selectedReclamation['priorite'] ?? 'normale') === 'urgente' ? 'admin' : ''; ?>">
                                <?php echo htmlspecialchars($selectedReclamation['priorite'] ?? 'Normale'); ?>
                            </span>
                        </div>
                        <div>
                            <strong>Statut:</strong><br>
                            <span class="badge <?php echo strtolower($selectedReclamation['statut'] ?? 'nouveau') === 'nouveau' ? 'user' : 'admin'; ?>">
                                <?php echo htmlspecialchars($selectedReclamation['statut'] ?? 'Nouveau'); ?>
                            </span>
                        </div>
                        <div>
                            <strong>Date:</strong><br>
                            <?php 
                            if (!empty($selectedReclamation['date'])) {
                                $date = new DateTime($selectedReclamation['date']);
                                echo $date->format('d/m/Y H:i');
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                        <div>
                            <strong>Gouvernorat:</strong><br>
                            <?php echo htmlspecialchars($selectedReclamation['gouvernorat'] ?? '—'); ?>
                        </div>
                        <div>
                            <strong>Délégation:</strong><br>
                            <?php echo htmlspecialchars($selectedReclamation['delegation'] ?? '—'); ?>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <strong>Description détaillée:</strong><br>
                        <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <?php echo nl2br(htmlspecialchars($selectedReclamation['description_detaillee'] ?? '—')); ?>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <?php if (!($selectedReclamation['has_response'] ?? false)): ?>
                    <a href="dashboard.php?action=respond&id=<?php echo $selectedReclamation['id']; ?>" 
                       class="btn btn-primary" 
                       style="flex: 1; text-align: center; text-decoration: none;">
                        <i class="fas fa-reply"></i> Répondre
                    </a>
                    <?php else: ?>
                    <a href="dashboard.php?action=view_response&id=<?php echo $selectedReclamation['id']; ?>" 
                       class="btn btn-primary" 
                       style="flex: 1; text-align: center; text-decoration: none; background: #3498db;">
                        <i class="fas fa-comment"></i> Voir la réponse
                    </a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- View Response -->
            <?php if ($action === 'view_response' && $selectedReclamation): ?>
            <div class="table-container" style="margin-top: 30px; padding: 25px;">
                <h3 style="color: #212934; margin-bottom: 20px;">
                    <i class="fas fa-comment" style="color: #59ab6e; margin-right: 10px;"></i>
                    Réponse à la réclamation #<?php echo htmlspecialchars($selectedReclamation['id']); ?>
                </h3>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="margin-bottom: 15px;">
                        <strong>Date de réponse:</strong><br>
                        <?php 
                        if (!empty($selectedReclamation['response_date'])) {
                            $date = new DateTime($selectedReclamation['response_date']);
                            echo $date->format('d/m/Y H:i');
                        } else {
                            echo '—';
                        }
                        ?>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong>Statut:</strong><br>
                        <span class="badge admin">
                            <?php echo htmlspecialchars($selectedReclamation['response_statut'] ?? '—'); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Réponse:</strong><br>
                        <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 10px;">
                            <?php echo nl2br(htmlspecialchars($selectedReclamation['response_detail'] ?? '—')); ?>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="dashboard.php?action=view&id=<?php echo $selectedReclamation['id']; ?>" 
                       class="btn btn-secondary" 
                       style="flex: 1; text-align: center; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour aux détails
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                        <i class="fas fa-list"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Response Modal/Form -->
            <?php if ($action === 'respond' && $selectedReclamation): ?>
            <div class="table-container" style="margin-top: 30px; padding: 25px;">
                <h3 style="color: #212934; margin-bottom: 20px;">
                    <i class="fas fa-reply" style="color: #59ab6e; margin-right: 10px;"></i>
                    Répondre à la réclamation #<?php echo htmlspecialchars($selectedReclamation['id']); ?>
                </h3>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="color: #212934; margin-bottom: 10px;">Détails de la réclamation:</h4>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($selectedReclamation['nom'] . ' ' . $selectedReclamation['prenom']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($selectedReclamation['email'] ?? '—'); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($selectedReclamation['description_detaillee'] ?? '—'); ?></p>
                    <p><strong>Priorité:</strong> <?php echo htmlspecialchars($selectedReclamation['priorite'] ?? 'Normale'); ?></p>
                </div>
                
                <form method="POST" action="rependreReclamation.php">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($selectedReclamation['id']); ?>">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #212934;">
                            Réponse détaillée:
                        </label>
                        <textarea name="response_detaillee" 
                                  rows="6" 
                                  style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit;"
                                  required></textarea>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #212934;">
                            Nouveau statut:
                        </label>
                        <select name="statut" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="En Cours">En cours</option>
                            <option value="Résolu">Résolu</option>
                            <option value="Clôturé">Clôturé</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-paper-plane"></i> Envoyer la réponse
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Deals Section -->
            <?php if ($section === 'deals'): ?>
            <div class="table-container" style="margin-top: 30px;">
                <div class="table-header">
                    <h2>Gestion des Deals</h2>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="deal/create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer un Deal
                        </a>
                        <a href="dashboard.php?section=deals" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">
                            <i class="fas fa-sync"></i> Actualiser
                        </a>
                    </div>
                </div>
                
                <!-- Deal Statistics -->
                <div class="stats-grid" style="margin-bottom: 30px;">
                    <div class="stat-card users">
                        <div class="stat-header">
                            <h3>Total Deals</h3>
                            <div class="stat-icon users-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo $dealStats['total']; ?></div>
                        <div class="stat-label">Deals enregistrés</div>
                    </div>
                    <div class="stat-card events">
                        <div class="stat-header">
                            <h3>En Attente</h3>
                            <div class="stat-icon events-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo $dealStats['pending']; ?></div>
                        <div class="stat-label">Deals en attente</div>
                    </div>
                    <div class="stat-card donations">
                        <div class="stat-header">
                            <h3>Acceptés</h3>
                            <div class="stat-icon donations-icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo $dealStats['accepted']; ?></div>
                        <div class="stat-label">Deals acceptés</div>
                    </div>
                </div>
                
                <!-- Filters and Sort -->
                <div class="table-container" style="margin-bottom: 20px; padding: 20px;">
                    <form method="GET" action="dashboard.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <input type="hidden" name="section" value="deals">
                        <select name="sponsor_id" class="form-control" style="min-width: 180px; padding: 8px;">
                            <option value="">Tous les sponsors</option>
                            <?php foreach ($sponsors as $sp): ?>
                            <option value="<?php echo (int)$sp['id']; ?>" <?php echo ($selectedSponsorId == $sp['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sp['nomEntreprise']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="sort" class="form-control" style="min-width: 200px; padding: 8px;">
                            <option value="">Tri par défaut</option>
                            <option value="deal_date_debut_recent" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_date_debut_recent') ? 'selected' : ''; ?>>Date de début (récentes)</option>
                            <option value="deal_periode_longue" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_periode_longue') ? 'selected' : ''; ?>>Période (longue)</option>
                            <option value="deal_montant_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_montant_desc') ? 'selected' : ''; ?>>Montant (décroissant)</option>
                            <option value="deal_accept_date" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_accept_date') ? 'selected' : ''; ?>>Date d'acceptation</option>
                            <option value="deal_dispo" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_dispo') ? 'selected' : ''; ?>>Disponibles</option>
                            <option value="deal_expire" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'deal_expire') ? 'selected' : ''; ?>>Expirés</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="dashboard.php?section=deals" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </a>
                    </form>
                </div>
                
                <!-- Deals Table -->
                <div class="table-responsive">
                    <?php if (empty($deals)): ?>
                    <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                        Aucun deal trouvé
                    </p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Intitulé</th>
                                <th>Sponsor</th>
                                <th>Prix Initial</th>
                                <th>Réduction</th>
                                <th>Date Début</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th>Expiré</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deals as $deal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($deal['idDeal']); ?></td>
                                <td><?php echo htmlspecialchars($deal['intitule']); ?></td>
                                <td><?php echo htmlspecialchars($deal['nomEntreprise']); ?></td>
                                <td><?php echo number_format($deal['prixInitial'] ?? $deal['prixinitial'] ?? 0, 2); ?> DT</td>
                                <td><?php echo number_format($deal['reduction'], 2); ?>%</td>
                                <td><?php echo !empty($deal['dateDebut']) ? date('d/m/Y', strtotime($deal['dateDebut'])) : '—'; ?></td>
                                <td><?php echo htmlspecialchars($deal['periodeValidite']); ?> jours</td>
                                <td>
                                    <span class="badge <?php echo ($deal['statut'] ?? '') === 'accepte' ? 'admin' : 'user'; ?>">
                                        <?php echo htmlspecialchars($deal['statut'] ?? 'en_attente'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($deal['expire'] ?? 'NON') === 'OUI' ? 'admin' : 'user'; ?>">
                                        <?php echo htmlspecialchars($deal['expire'] ?? 'NON'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="deal/show.php?idDeal=<?php echo $deal['idDeal']; ?>" 
                                           class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="deal/edit.php?idDeal=<?php echo $deal['idDeal']; ?>" 
                                           class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="deal/index.php?action=delete&idDeal=<?php echo $deal['idDeal']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce deal?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($section !== 'reclamations' && $section !== 'deals' && $section !== 'forum' && $section !== 'commentaire' && $section !== 'dons' && $section !== 'associations'): ?>
            <!-- Quick Actions -->
            <div style="margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                
                <div class="table-container" style="padding: 25px;">
                    <h3 style="color: #212934; margin-bottom: 15px; font-size: 18px;">
                        <i class="fas fa-chart-line" style="color: #59ab6e; margin-right: 10px;"></i>
                        Statistiques Rapides
                    </h3>
                    <p style="color: #bcbcbc; font-size: 14px; line-height: 1.8;">
                        <strong>Administrateurs:</strong> <?php echo isset($totalAdmins) ? $totalAdmins : '0'; ?><br>
                        <strong>Utilisateurs Standards:</strong> <?php echo isset($totalUsers) && isset($totalAdmins) ? ($totalUsers - $totalAdmins) : '0'; ?><br>
                        <strong>Plateforme:</strong> Active
                    </p>
                </div>
                
                <div class="table-container" style="padding: 25px;">
                    <h3 style="color: #212934; margin-bottom: 15px; font-size: 18px;">
                        <i class="fas fa-bell" style="color: #f39c12; margin-right: 10px;"></i>
                        Notifications
                    </h3>
                    <p style="color: #bcbcbc; font-size: 14px; line-height: 1.8;">
                        Bienvenue sur le panneau d'administration SOLIDA.<br>
                        Gérez efficacement tous les aspects de votre plateforme étudiante.
                    </p>
                </div>
                
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
</body>
</html>
