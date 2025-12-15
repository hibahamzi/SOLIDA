<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($sponsors)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../../front_office/sign-in.php');
        exit();
    }
    
    require_once '../../../controllers/SponsorController.php';
    $controller = new SponsorController($pdo);
    
    // Handle actions
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if ($action === 'delete' && isset($_GET['id'])) {
        $controller->delete();
        exit;
    }
    if ($action === 'update' && isset($_POST['id'])) {
        $controller->edit();
        exit;
    }
    if ($action === 'create' || (isset($_GET['action']) && $_GET['action'] === 'create')) {
        $controller->create();
        exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
        $controller->edit();
        exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'show' && isset($_GET['id'])) {
        $controller->show();
        exit;
    }
    
    $controller->index();
    // After calling index(), the controller includes this file again, so we exit here
    exit;
}

// Variables are now set (either by controller include or by direct access)
$sponsors = $sponsors ?? [];
$totalSponsors = count($sponsors);

// Récup sort (pour garder la valeur sélectionnée dans le <select>)
$sort = isset($sortVar) ? $sortVar : ($_GET['sort'] ?? '');

// Stats simples à partir du champ "statut"
$actifs = 0;
$inactifs = 0;
foreach ($sponsors as $sp) {
    if (!method_exists($sp, 'getStatut')) {
        continue;
    }
    $statut = strtolower($sp->getStatut() ?? '');
    if ($statut === 'actif' || $statut === 'active' || $statut === 'actifs') {
        $actifs++;
    } else {
        $inactifs++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Sponsors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome (icônes) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../back_office/assets/css/admin.css">
</head>
<body>

    <?php include '../../back_office/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Gestion des Sponsors';
        include '../../back_office/includes/topbar.php'; 
        ?>

    <div class="content-area">

        <!-- Cartes de stats -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-header">
                    <h3>Total sponsors</h3>
                    <div class="stat-icon users-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $totalSponsors ?></div>
                <div class="stat-label">Sponsors enregistrés</div>
            </div>

            <div class="stat-card events">
                <div class="stat-header">
                    <h3>Actifs</h3>
                    <div class="stat-icon events-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $actifs ?></div>
                <div class="stat-label">Sponsors actifs</div>
            </div>

            <div class="stat-card donations">
                <div class="stat-header">
                    <h3>Inactifs / autres</h3>
                    <div class="stat-icon donations-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $inactifs ?></div>
                <div class="stat-label">Autres statuts</div>
            </div>
        </div>

        <!-- Tableau des sponsors -->
        <div class="table-container">
            <div class="table-header">
                <h2>Liste des sponsors</h2>

                <div style="display:flex; gap:10px; align-items:center;">

                    <!-- TRI -->
                    <form method="get"
                          action="index.php"
                          style="display:flex; gap:8px; align-items:center; flex-wrap: wrap;">
                        <select name="sort" class="form-control" style="min-width:200px; padding:6px 8px;">
                            <option value="">Tri par défaut</option>
                            <option value="nom_asc"      <?= $sort === 'nom_asc' ? 'selected' : '' ?>>
                                Nom (A → Z)
                            </option>
                            <option value="montant_desc" <?= $sort === 'montant_desc' ? 'selected' : '' ?>>
                                Montant engagé (du + grand au + petit)
                            </option>
                        </select>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-sort"></i> Trier
                        </button>
                    </form>

                    <!-- Bouton Ajouter un sponsor -->
                    <a href="create.php"
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <?php if (empty($sponsors)): ?>
                    <p style="text-align:center; padding: 40px; color:#bcbcbc;">
                        <i class="fas fa-inbox" style="font-size:48px; display:block; margin-bottom:15px;"></i>
                        Aucun sponsor trouvé
                    </p>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Entreprise</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Domaine</th>
                            <th>Type de sponsoring</th>
                            <th>Montant (DT)</th>
                            <th>Statut</th>
                            <th style="width:150px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sponsors as $sp): ?>
                            <tr>
                                <td><?= htmlspecialchars($sp->getId(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getNomEntreprise(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getEmailContact(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getTelephone(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getDomaineActivite(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getTypeSponsoring(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($sp->getMontantEngage(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php
                                    $statut = htmlspecialchars($sp->getStatut(), ENT_QUOTES, 'UTF-8');
                                    if (strtolower($statut) === 'actif' || strtolower($statut) === 'active') {
                                        echo '<span class="badge admin">Actif</span>';
                                    } else {
                                        echo '<span class="badge">' . $statut . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <!-- Voir -->
                                    <a href="show.php?id=<?= $sp->getId() ?>"
                                       class="btn btn-small"
                                       title="Voir le sponsor">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Modifier -->
                                    <a href="edit.php?id=<?= $sp->getId() ?>"
                                       class="btn btn-small"
                                       title="Modifier">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <!-- Supprimer -->
                                    <a href="index.php?action=delete&id=<?= $sp->getId() ?>"
                                       class="btn btn-small btn-danger"
                                       title="Supprimer"
                                       onclick="return confirm('Supprimer ce sponsor ?');">
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