<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Gestion des sponsors</title>

    <!-- CSS BOOTSTRAP + TEMPLATE BACKOFFICE (même que deals) -->
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
                       class="nav-link active">
                        <i class="fa fa-handshake-o me-2"></i> Gestion des sponsors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                       class="nav-link">
                        <i class="fa fa-tags me-2"></i> Gestion des deals
                    </a>
                </li>
            </ul>
        </nav>

        <!-- CONTENU PRINCIPAL -->
        <main class="col-md-10 ms-sm-auto col-lg-10 content-wrapper">

            <!-- Bandeau top -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3 border-bottom pb-2">
                <h1 class="h3 page-title">Gestion des sponsors</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a class="btn btn-success"
                       href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=create">
                        <i class="fa fa-plus me-1"></i> Ajouter un sponsor
                    </a>
                </div>
            </div>

            <!-- Indicateurs (optionnels) -->
            <div class="row mb-4">
                <?php
                $total = count($sponsors);
                $actifs = 0;
                $inactifs = 0;

                foreach ($sponsors as $sp) {
                    if (strtolower($sp->getStatut()) === 'actif') {
                        $actifs++;
                    } elseif (strtolower($sp->getStatut()) === 'inactif') {
                        $inactifs++;
                    }
                }
                ?>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total sponsors</h6>
                            <h3><?= $total ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Actifs</h6>
                            <h3><?= $actifs ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Inactifs</h6>
                            <h3><?= $inactifs ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLEAU DES SPONSORS -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Liste des sponsors</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nom entreprise</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Type sponsoring</th>
                                <th>Montant engagé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($sponsors)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        Aucun sponsor enregistré pour le moment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sponsors as $sp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sp->getId()) ?></td>
                                        <td><?= htmlspecialchars($sp->getNomEntreprise()) ?></td>
                                        <td><?= htmlspecialchars($sp->getEmailContact()) ?></td>
                                        <td><?= htmlspecialchars($sp->getTelephone()) ?></td>
                                        <td><?= htmlspecialchars($sp->getTypeSponsoring()) ?></td>
                                        <td>
                                            <?php if ($sp->getMontantEngage() !== null): ?>
                                                <?= htmlspecialchars($sp->getMontantEngage()) ?> €
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statut = strtolower($sp->getStatut());
                                            if ($statut === 'actif'): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php elseif ($statut === 'inactif'): ?>
                                                <span class="badge bg-secondary">Inactif</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($sp->getStatut()) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-primary btn-sm mb-1"
                                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=edit&id=<?= $sp->getId() ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>

                                            <a class="btn btn-danger btn-sm mb-1"
                                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=delete&id=<?= $sp->getId() ?>"
                                               onclick="return confirm('Supprimer ce sponsor ?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
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