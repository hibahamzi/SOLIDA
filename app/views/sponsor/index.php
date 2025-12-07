<?php
// Ici ton contrôleur doit déjà passer $sponsors (array d’objets Sponsor)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Gestion des sponsors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- Template admin -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/public/backoffice/assets/css/admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SOLIDA</h2>
        <p>Panneau d'Administration</p>
    </div>

    <!-- Bloc admin (simplifié) -->
    <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                AD
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="color: white; font-weight: 600; font-size: 14px;">Administrateur</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 12px;">admin@solida.com</div>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/PROJET_WEB_MVC_FINAL/public/backoffice/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de Bord</span>
            </a>
        </li>
        <li>
            <a href="#" class="active">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>
        <li>
            <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index">
                <i class="fas fa-tags"></i>
                <span>Deals</span>
            </a>
        </li>
        <!-- autres menus si besoin -->
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="top-bar">
        <h1>Gestion des sponsors</h1>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <span>Administrateur</span>
                <small>admin@solida.com</small>
            </div>
        </div>
    </div>

    <div class="content-area">

        <?php
        $sponsors = $sponsors ?? [];
        $total = count($sponsors);
        $actifs = 0;
        $inactifs = 0;
        foreach ($sponsors as $sp) {
            $st = strtolower((string)$sp->getStatut());
            if ($st === 'actif') $actifs++;
            elseif ($st === 'inactif') $inactifs++;
        }
        ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-header">
                    <h3>Total sponsors</h3>
                    <div class="stat-icon users-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $total ?></div>
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

            <div class="stat-card claims">
                <div class="stat-header">
                    <h3>Inactifs</h3>
                    <div class="stat-icon claims-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?= $inactifs ?></div>
                <div class="stat-label">Sponsors inactifs</div>
            </div>
        </div>

        <!-- Tableau sponsors -->
        <div class="table-container">
            <div class="table-header">
                <h2>Liste des sponsors</h2>
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=create"
                   class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
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
                            <th>Nom entreprise</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Montant (DT)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sponsors as $sp): ?>
                            <tr>
                                <td><?= htmlspecialchars($sp->getId()) ?></td>
                                <td><?= htmlspecialchars($sp->getNomEntreprise()) ?></td>
                                <td><?= htmlspecialchars($sp->getEmailContact()) ?></td>
                                <td><?= htmlspecialchars($sp->getTelephone()) ?></td>
                                <td><?= htmlspecialchars($sp->getTypeSponsoring()) ?></td>
                                <td><?= htmlspecialchars($sp->getMontantEngage() ?? '—') ?></td>
                                <td>
                                    <?php $st = strtolower((string)$sp->getStatut()); ?>
                                    <?php if ($st === 'actif'): ?>
                                        <span class="badge admin">Actif</span>
                                    <?php elseif ($st === 'inactif'): ?>
                                        <span class="badge user">Inactif</span>
                                    <?php else: ?>
                                        <span class="badge"><?= htmlspecialchars($sp->getStatut()) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=edit&id=<?= $sp->getId() ?>"
                                       class="btn btn-small">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=delete&id=<?= $sp->getId() ?>"
                                       class="btn btn-small btn-danger"
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