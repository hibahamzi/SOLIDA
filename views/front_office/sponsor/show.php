<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($sponsor)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../front_office/sign-in.php');
        exit();
    }
    
    require_once '../../../controllers/SponsorController.php';
    $controller = new SponsorController($pdo);
    $controller->show();
    // After calling show(), the controller includes this file again, so we exit here
    exit;
}

// If we reach here, the file was included by the controller
// $sponsor is already set by SponsorController->show()

// Detect context: admin dashboard or front office
// Check if accessed from front office index.php (via section parameter) or directly from sponsor directory
$isFrontOfficeInclude = isset($_GET['section']) && $_GET['section'] === 'sponsor';
$currentPath = $_SERVER['PHP_SELF'];
$isDirectAccess = strpos($currentPath, '/sponsor/show.php') !== false;
$isAdminContext = !$isFrontOfficeInclude && 
                  isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' &&
                  ($isDirectAccess || 
                   strpos($_SERVER['HTTP_REFERER'] ?? '', '/back_office/') !== false ||
                   strpos($_SERVER['HTTP_REFERER'] ?? '', '/sponsor/index.php') !== false);
?>
<?php if ($isAdminContext): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Détail du Sponsor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../back_office/assets/css/admin.css">
<?php else: ?>
<!-- Front office - will be included in index.php with navbar/footer -->
<style>
    .sponsor-detail-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        margin: 20px 0;
    }
    .sponsor-title {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .sponsor-meta {
        margin-bottom: 10px;
        font-size: 15px;
    }
    .sponsor-meta strong {
        font-weight: 600;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 13px;
        color: #fff;
    }
    .badge-active {
        background-color: #28a745;
    }
    .badge-inactive {
        background-color: #dc3545;
    }
    .sponsor-logo {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        background: #f1f1f1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .sponsor-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
</style>
<?php endif; ?>
<?php if ($isAdminContext): ?>
    <style>
        .sponsor-detail-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .sponsor-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .sponsor-meta {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .sponsor-meta strong {
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
            color: #fff;
        }
        .badge-active {
            background-color: #28a745;
        }
        .badge-inactive {
            background-color: #dc3545;
        }
        .sponsor-logo {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            background: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .sponsor-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <?php include '../../back_office/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Détail du Sponsor';
        include '../../back_office/includes/topbar.php'; 
        ?>

    <div class="content-area">

        <div class="table-container">
            <div class="table-header">
                <h2>Détail du Sponsor</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux sponsors
                </a>
            </div>

            <div class="sponsor-detail-card">
<?php else: ?>
<!-- Front Office Section - will be included in index.php -->
<section class="container py-5">
    <div class="row">
        <div class="col-12">
            <a href="index.php?section=sponsors" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour aux sponsors
            </a>
            <div class="sponsor-detail-card">
<?php endif; ?>
                <?php if ($sponsor->getLogoUrl()): ?>
                <div class="sponsor-logo">
                    <img src="<?= htmlspecialchars($sponsor->getLogoUrl(), ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
                </div>
                <?php endif; ?>

                <div class="sponsor-title">
                    <?= htmlspecialchars($sponsor->getNomEntreprise(), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="sponsor-meta">
                    <strong>Statut:</strong>
                    <?php
                    $statut = strtolower($sponsor->getStatut() ?? '');
                    $badgeClass = ($statut === 'actif' || $statut === 'active') ? 'badge-active' : 'badge-inactive';
                    ?>
                    <span class="status-badge <?= $badgeClass ?>">
                        <?= htmlspecialchars($sponsor->getStatut(), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="sponsor-meta">
                    <strong>Email:</strong>
                    <a href="mailto:<?= htmlspecialchars($sponsor->getEmailContact(), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($sponsor->getEmailContact(), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>

                <?php if ($sponsor->getTelephone()): ?>
                <div class="sponsor-meta">
                    <strong>Téléphone:</strong>
                    <?= htmlspecialchars($sponsor->getTelephone(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <?php if ($sponsor->getAdresse()): ?>
                <div class="sponsor-meta">
                    <strong>Adresse:</strong>
                    <?= htmlspecialchars($sponsor->getAdresse(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <div class="sponsor-meta">
                    <strong>Type de sponsoring:</strong>
                    <?= htmlspecialchars($sponsor->getTypeSponsoring(), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <?php if ($sponsor->getMontantEngage()): ?>
                <div class="sponsor-meta">
                    <strong>Montant engagé:</strong>
                    <?= htmlspecialchars($sponsor->getMontantEngage(), ENT_QUOTES, 'UTF-8') ?> DT
                </div>
                <?php endif; ?>

                <?php if ($sponsor->getDomaineActivite()): ?>
                <div class="sponsor-meta">
                    <strong>Domaine d'activité:</strong>
                    <?= htmlspecialchars($sponsor->getDomaineActivite(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <?php if ($sponsor->getDateDebutPartenaire()): ?>
                <div class="sponsor-meta">
                    <strong>Date début partenariat:</strong>
                    <?= htmlspecialchars($sponsor->getDateDebutPartenaire(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <?php if ($sponsor->getDateFinPartenaire()): ?>
                <div class="sponsor-meta">
                    <strong>Date fin partenariat:</strong>
                    <?= htmlspecialchars($sponsor->getDateFinPartenaire(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <?php if ($sponsor->getContratUrl()): ?>
                <div class="sponsor-meta">
                    <strong>Contrat:</strong>
                    <a href="<?= htmlspecialchars($sponsor->getContratUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Voir le contrat
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($isAdminContext): ?>
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <a href="edit.php?id=<?= $sponsor->getId() ?>" class="btn btn-primary">
                        <i class="fas fa-pen"></i> Modifier
                    </a>
                    <a href="index.php?action=delete&id=<?= $sponsor->getId() ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce sponsor ?');">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php if ($isAdminContext): ?>
        </div>
    </div>
    </div>
</body>
</html>
        <?php else: ?>
        </div>
    </div>
</div>
</section>
        <?php endif; ?>
