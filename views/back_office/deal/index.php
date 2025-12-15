<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($deals)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../front_office/sign-in.php');
        exit();
    }
    
    require_once '../../../controllers/DealController.php';
    $controller = new DealController($pdo);
    
    // Handle actions (both GET and POST)
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    if ($action === 'store') {
        $controller->store();
        exit;
    }
    if ($action === 'update') {
        $controller->update();
        exit;
    }
    if ($action === 'delete' && isset($_GET['idDeal'])) {
        $controller->delete();
        exit;
    }
    if ($action === 'deleteAll') {
        $controller->deleteAll();
        exit;
    }
    if ($action === 'accept' && isset($_GET['idDeal'])) {
        $controller->accept();
        exit;
    }
    if ($action === 'assistantIA') {
        $controller->assistantIA();
        exit;
    }
    
    // Get deals data directly instead of calling index() which would include this file again
    $selectedSponsorId = isset($_GET['sponsor_id']) && $_GET['sponsor_id'] !== '' ? (int)$_GET['sponsor_id'] : null;
    $sort = $_GET['sort'] ?? '';
    
    $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
    $stmtSponsors = $pdo->query($sqlSponsors);
    $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);
    
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
}

// Variables are now set (either by controller include or by direct access)
$deals             = $deals ?? [];
$sponsors          = $sponsors ?? [];
$selectedSponsorId = $selectedSponsorId ?? null;

// Pour garder le tri choisi (from GET or from controller)
$sort = isset($sortVar) ? $sortVar : ($_GET['sort'] ?? '');

// Calculate statistics
$totalDeals    = count($deals);
$pendingDeals  = 0;
$acceptedDeals = 0;
foreach ($deals as $d) {
    if (($d['statut'] ?? '') === 'en_attente') {
        $pendingDeals++;
    } elseif (($d['statut'] ?? '') === 'accepte') {
        $acceptedDeals++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Gestion des deals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- Template admin (même CSS que sponsors) -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Gestion des Deals';
        include '../includes/topbar.php'; 
        ?>

    <div class="content-area">

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-header">
                    <h3>Total deals</h3>
                    <div class="stat-icon users-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $totalDeals ?></div>
                <div class="stat-label">Deals enregistrés</div>
            </div>

            <div class="stat-card events">
                <div class="stat-header">
                    <h3>En attente</h3>
                    <div class="stat-icon events-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $pendingDeals ?></div>
                <div class="stat-label">Deals en attente</div>
            </div>

            <div class="stat-card donations">
                <div class="stat-header">
                    <h3>Acceptés</h3>
                    <div class="stat-icon donations-icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $acceptedDeals ?></div>
                <div class="stat-label">Deals acceptés</div>
            </div>
        </div>

        <!-- Tableau deals -->
        <div class="table-container">
            <div class="table-header">
                <h2>Liste des deals</h2>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">

                    <!-- FILTRE + TRI -->
                    <form method="get"
                          action="index.php"
                          style="display:flex; gap:8px; align-items:center; flex-wrap: wrap;">

                        <!-- Filtre sponsor -->
                        <select name="sponsor_id" class="form-control" style="min-width:180px; padding:6px 8px;">
                            <option value="">Tous les sponsors</option>
                            <?php foreach ($sponsors as $sp): ?>
                                <option value="<?= (int)$sp['id'] ?>"
                                    <?= ($selectedSponsorId == $sp['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sp['nomEntreprise'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Tri -->
                        <select name="sort" class="form-control" style="min-width:200px; padding:6px 8px;">
                            <option value="">Tri par défaut</option>
                            <option value="deal_date_debut_recent" <?= $sort === 'deal_date_debut_recent' ? 'selected' : '' ?>>
                                Date de début (les plus récentes)
                            </option>
                            <option value="deal_periode_longue" <?= $sort === 'deal_periode_longue' ? 'selected' : '' ?>>
                                Période de validité (la plus longue)
                            </option>
                            <option value="deal_montant_desc" <?= $sort === 'deal_montant_desc' ? 'selected' : '' ?>>
                                Prix initial (du + grand au + petit)
                            </option>
                            <option value="deal_accept_date" <?= $sort === 'deal_accept_date' ? 'selected' : '' ?>>
                                Date d’acceptation (récent d’abord)
                            </option>
                            <option value="deal_dispo" <?= $sort === 'deal_dispo' ? 'selected' : '' ?>>
                                D’abord non expirés
                            </option>
                            <option value="deal_expire" <?= $sort === 'deal_expire' ? 'selected' : '' ?>>
                                D’abord expirés
                            </option>
                        </select>

                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-filter"></i> Filtrer / Trier
                        </button>
                    </form>

                    <!-- Assistant IA -->
                    <a href="assistant_ia.php"
                       class="btn btn-secondary">
                        <i class="fas fa-robot"></i> Assistant IA
                    </a>

                    <!-- Ajouter -->
                    <a href="create.php"
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>

                    <!-- Supprimer tout -->
                    <a href="index.php?action=deleteAll"
                       class="btn btn-danger"
                       onclick="return confirm('Voulez-vous vraiment supprimer TOUTES les offres ? Cette action est irréversible.');">
                        <i class="fas fa-trash"></i> Supprimer tout
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <?php if (empty($deals)): ?>
                    <p style="text-align:center; padding: 40px; color:#bcbcbc;">
                        <i class="fas fa-inbox" style="font-size:48px; display:block; margin-bottom:15px;"></i>
                        Aucun deal trouvé
                    </p>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Intitulé</th>
                            <th>Sponsor</th>
                            <th>Prix initial (DT)</th>
                            <th>Réduction (%)</th>
                            <th>Date début</th>
                            <th>Période (jours)</th>
                            <th>Vues</th>
                            <th>Statut</th>
                            <th>Expire</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($deals as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['idDeal']) ?></td>
                                <td><?= htmlspecialchars($d['intitule']) ?></td>
                                <td><?= htmlspecialchars($d['nomEntreprise'] ?? '—') ?></td>

                                <!-- Prix initial sécurisé pour éviter Undefined array key + Deprecated -->
<td>
    <?php
    if (isset($d['prixInitial']) && $d['prixInitial'] !== null) {
        echo htmlspecialchars(
            number_format((float)$d['prixInitial'], 2, ',', ' ') . ' DT',
            ENT_QUOTES,
            'UTF-8'
        );
    } else {
        echo '—';
    }
    ?>
</td>

                                <td><?= htmlspecialchars($d['reduction']) ?></td>
                                <td><?= htmlspecialchars($d['dateDebut']) ?></td>
                                <td><?= htmlspecialchars($d['periodeValidite']) ?></td>
                                <td><?= (int)($d['click_count'] ?? 0) ?></td>
                                <td>
                                    <?php if (($d['statut'] ?? '') === 'en_attente'): ?>
                                        <span class="badge user">En attente</span>
                                    <?php elseif (($d['statut'] ?? '') === 'accepte'): ?>
                                        <span class="badge admin">Accepté</span>
                                    <?php else: ?>
                                        <span class="badge"><?= htmlspecialchars($d['statut'] ?? '—') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($d['expire'] ?? '') === 'OUI'): ?>
                                        <span class="badge admin" style="background:#e74c3c;">OUI</span>
                                    <?php else: ?>
                                        <span class="badge">NON</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Voir (œil) -->
                                    <a href="show.php?idDeal=<?= $d['idDeal'] ?>"
                                       class="btn btn-small"
                                       title="Voir le deal">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="edit.php?idDeal=<?= $d['idDeal'] ?>"
                                       class="btn btn-small">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <!-- Accepter -->
                                    <?php if (($d['statut'] ?? '') !== 'accepte'): ?>
                                        <a href="index.php?action=accept&idDeal=<?= $d['idDeal'] ?>"
                                           class="btn btn-small"
                                           style="background:#2ecc71; color:#fff;"
                                           onclick="return confirm('Accepter ce deal ?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Delete -->
                                    <a href="index.php?action=delete&idDeal=<?= $d['idDeal'] ?>"
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('Supprimer ce deal ?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

</body>
</html>