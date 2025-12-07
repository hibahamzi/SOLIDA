<?php
session_start();

// $deals doit être fourni par DealController->index()
$deals = $deals ?? [];

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
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/public/backoffice/assets/css/admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SOLIDA</h2>
        <p>Panneau d'Administration</p>
    </div>

    <!-- Bloc admin -->
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
            <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>
        <li>
            <a href="#" class="active">
                <i class="fas fa-tags"></i>
                <span>Deals</span>
            </a>
        </li>
    </ul>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="top-bar">
        <h1>Gestion des deals</h1>
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
                <!-- Bouton Ajouter : va vers action=create -->
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=create"
                   class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
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
                                <td><?= htmlspecialchars($d['prixInitial']) ?></td>
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
                                    <!-- Edit -->
                                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=edit&idDeal=<?= $d['idDeal'] ?>"
                                       class="btn btn-small">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <!-- Accepter -->
                                    <?php if (($d['statut'] ?? '') !== 'accepte'): ?>
                                        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=accept&idDeal=<?= $d['idDeal'] ?>"
                                           class="btn btn-small"
                                           style="background:#2ecc71; color:#fff;"
                                           onclick="return confirm('Accepter ce deal ?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Delete -->
                                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=delete&idDeal=<?= $d['idDeal'] ?>"
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