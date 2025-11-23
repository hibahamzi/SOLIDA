<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Gestion des deals</title>

    <!-- CSS BOOTSTRAP + TEMPLATE BACKOFFICE (même que sponsors) -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/templatemo.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/custom.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/fontawesome.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content-wrapper {
            padding: 20px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .badge {
            font-size: 0.8rem;
        }

        .table thead th {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR BACKOFFICE -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="py-4 px-3">
                <h4 class="text-white">SOLIDA Admin</h4>
                <p class="mb-0 text-muted">Backoffice</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php" class="nav-link">
                        <i class="fa fa-home me-2"></i> Dashboard général
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index"
                       class="nav-link">
                        <i class="fa fa-handshake-o me-2"></i> Gestion des sponsors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                       class="nav-link active">
                        <i class="fa fa-tags me-2"></i> Gestion des deals
                    </a>
                </li>
                <!-- ajoute ici d'autres menus si besoin -->
            </ul>
        </nav>

        <!-- CONTENU PRINCIPAL -->
        <main class="col-md-10 ms-sm-auto col-lg-10 content-wrapper">

            <!-- Bandeau top -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3 border-bottom pb-2">
                <h1 class="h3 page-title">Gestion des deals</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a class="btn btn-success"
                       href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=create">
                        <i class="fa fa-plus me-1"></i> Ajouter un deal
                    </a>
                </div>
            </div>

            <!-- Petits indicateurs (optionnels) -->
            <div class="row mb-4">
                <?php
                $total       = count($deals);
                $enAttente   = 0;
                $acceptes    = 0;

                foreach ($deals as $d) {
                    if ($d['statut'] === 'en_attente') {
                        $enAttente++;
                    } elseif ($d['statut'] === 'accepte') {
                        $acceptes++;
                    }
                }
                ?>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total deals</h6>
                            <h3><?= $total ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">En attente</h6>
                            <h3><?= $enAttente ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Acceptés</h6>
                            <h3><?= $acceptes ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLEAU DES DEALS -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liste des deals</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Intitulé</th>
                                <th>Sponsor</th>
                                <th>Prix initial</th>
                                <th>Réduction</th>
                                <th>Date début</th>
                                <th>Période (jours)</th>
                                <th>Statut</th>
                                <th>Expire (champ)</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($deals)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        Aucun deal enregistré pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($deals as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['idDeal']) ?></td>
                                        <td><?= htmlspecialchars($d['intitule']) ?></td>
                                        <td><?= htmlspecialchars($d['nomEntreprise']) ?></td>
                                        <td><?= htmlspecialchars($d['prixInitial']) ?> DT</td>
                                        <td><?= htmlspecialchars($d['reduction']) ?> %</td>
                                        <td><?= htmlspecialchars($d['dateDebut']) ?></td>
                                        <td><?= htmlspecialchars($d['periodeValidite']) ?></td>
                                        <td>
                                            <?php if ($d['statut'] === 'en_attente'): ?>
                                                <span class="badge bg-warning text-dark">En attente</span>
                                            <?php elseif ($d['statut'] === 'accepte'): ?>
                                                <span class="badge bg-success">Accepté</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($d['statut']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($d['expire'] === 'OUI'): ?>
                                                <span class="badge bg-danger">OUI</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">NON</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-primary btn-sm mb-1"
                                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=edit&idDeal=<?= $d['idDeal'] ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>

                                            <a class="btn btn-danger btn-sm mb-1"
                                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=delete&idDeal=<?= $d['idDeal'] ?>"
                                               onclick="return confirm('Supprimer ce deal ?');">
                                                <i class="fa fa-trash"></i>
                                            </a>

                                            <?php if ($d['statut'] === 'en_attente'): ?>
                                                <a class="btn btn-success btn-sm mb-1"
                                                   href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=accept&idDeal=<?= $d['idDeal'] ?>">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- JS -->
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/jquery-1.11.0.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/bootstrap.bundle.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/templatemo.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/custom.js"></script>
</body>
</html>