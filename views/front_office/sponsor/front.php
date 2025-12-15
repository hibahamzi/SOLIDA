<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Page Sponsors - Événements Étudiants</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICONS -->
    <link rel="apple-touch-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/favicon.ico">

    <!-- CSS DU TEMPLATE -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/templatemo.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/custom.css">

    <!-- Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/fontawesome.min.css">

    <style>
        body {
            background: #f8f8f8;
        }
        .hero-title {
            text-align: center;
            font-size: 28px;
            margin: 40px 0 20px 0;
            font-weight: bold;
        }
        .partners-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            width: 80%;
            margin: auto;
        }
        .partner-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: left;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .partner-card-top {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .partner-logo {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: contain;
            background: #f1f1f1;
            flex-shrink: 0;
        }
        .partner-info strong {
            font-size: 16px;
        }

        .partner-actions {
            text-align: right;
        }
        .btn-view-sponsor {
            padding: 6px 12px;
            border-radius: 8px;
            background: #28a745;
            color: #fff;
            font-size: 13px;
            border: none;
            text-decoration: none;
        }
        .btn-view-sponsor:hover {
            background: #218838;
            color: #fff;
        }

        .cta {
            text-align: center;
            margin: 60px 0;
        }
        .cta a.btn-link,
        .cta button.btn-link {
            padding: 12px 20px;
            margin: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            text-decoration: none;
            border: none;
        }

        /* Boutons verts comme SOLIDA */
        .btn-add-sponsor,
        .btn-add-deal {
            background: #28a745;
            color: #fff;
        }

        /* Bouton accès admin (icône seulement) */
        .btn-admin-icon {
            background: transparent;
            border: none;
            color: #ffffff;
            cursor: pointer;
        }
        .btn-admin-icon i {
            font-size: 18px;
        }
        .btn-admin-icon:hover i {
            color: #ffc107;
        }

        /* OFFRES */
        .offers-grid {
            width: 80%;
            margin: 0 auto 60px auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .offer-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .offer-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .offer-sponsor {
            font-weight: 500;
            color: #17a2b8;
        }
        .offer-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            color: #fff;
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-expired {
            background-color: #dc3545;
        }
        .offer-footer {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }

        /* Bloc tri */
        .sort-bar {
            width: 80%;
            margin: 0 auto 20px auto;
            display:flex;
            justify-content: space-between;
            align-items:center;
            gap:15px;
            flex-wrap:wrap;
        }
        .sort-bar label {
            margin-right: 6px;
            font-weight: 500;
        }
        .sort-bar select {
            min-width: 200px;
        }

        @media (max-width: 992px) {
            .partners-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .offers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .partners-grid,
            .offers-grid {
                grid-template-columns: 1fr;
            }
            .clr {
                background-color: green;
            }
        }

        /* Message d'erreur mot de passe */
        #admin-error {
            display: none;
            padding: 8px 12px;
            margin: 0;
            background: #f8d7da;
            color: #721c24;
            font-size: 14px;
        }
    </style>
</head>

<body>

<!-- ======= Top Nav noir ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
    <div class="container text-light">
        <div class="w-100 d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-envelope mx-2"></i>
                <a class="navbar-sm-brand text-light text-decoration-none"
                   href="mailto:contact@solida.com">contact@solida.com</a>
                <i class="fa fa-phone mx-2"></i>
                <a class="navbar-sm-brand text-light text-decoration-none"
                   href="tel:010-020-0340">010-020-0340</a>
            </div>

            <div class="d-flex align-items-center">
                <!-- Réseaux sociaux -->
                <div class="me-3">
                    <a class="text-light" href="#" target="_blank"><i class="fab fa-facebook-f fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="#" target="_blank"><i class="fab fa-instagram fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="#" target="_blank"><i class="fab fa-twitter fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="#" target="_blank"><i class="fab fa-linkedin fa-sm fa-fw"></i></a>
                </div>

                <!-- 🔐 Bouton admin dans le header, icône uniquement -->
                <button type="button" class="btn-admin-icon" id="btnAdminSponsorHeader"
                        title="Espace admin sponsors">
                    <i class="fas fa-user-shield"></i>
                </button>
            </div>
        </div>
    </div>
</nav>
<!-- Message d'erreur admin sous le header -->
<div id="admin-error" class="text-center">
    Mot de passe incorrect.
</div>

<div class="clr">
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-success logo h1 align-self-center"
               href="../sponsor.php">
                SOLIDA
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#templatemo_main_nav" aria-controls="templatemo_main_nav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="align-self-center collapse navbar-collapse flex-fill d-lg-flex justify-content-lg-between"
                 id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../sponsor.php">Accueil</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#">Événements / Shop</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Forum</a></li>
                        <li class="nav-item">
                            <a class="nav-link active"
                               href="../sponsor.php?action=front">
                                Sponsors
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Mon Compte</a></li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <a class="nav-icon d-none d-lg-inline" href="#" data-bs-toggle="modal"
                       data-bs-target="#templatemo_search">
                        <i class="fa fa-fw fa-search text-dark mr-2"></i>
                    </a>
                    <a class="nav-icon position-relative text-decoration-none" href="#">
                        <i class="fa fa-fw fa-cart-arrow-down text-dark mr-1"></i>
                        <span
                            class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark">7</span>
                    </a>
                    <a class="nav-icon position-relative text-decoration-none" href="#">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                        <span
                            class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark">+99</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</div>

<!-- Modal Search -->
<div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog"
     aria-labelledby="templatemo_search_label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="w-100 pt-1 mb-5 text-right">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <form action="" method="get" class="modal-content modal-body border-0 p-0">
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="inputModalSearch" name="q"
                       placeholder="Search ...">
                <button type="submit" class="input-group-text bg-success text-light">
                    <i class="fa fa-fw fa-search text-white"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container py-5">

    <!-- BARRE DE TRI -->
    <?php
    $currentSort = $_GET['sort'] ?? '';
    ?>
    <div class="sort-bar">
        <form method="get" action="../sponsor.php">
            <input type="hidden" name="controller" value="sponsor">
            <input type="hidden" name="action" value="front">
            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                <label for="sort">Trier par :</label>
                <select name="sort" id="sort" class="form-select form-select-sm">
                    <option value="">Par défaut</option>
                    <optgroup label="Sponsors">
                        <option value="nom_asc" <?= $currentSort === 'nom_asc' ? 'selected' : '' ?>>
                            Nom (A → Z)
                        </option>
                        <option value="montant_desc" <?= $currentSort === 'montant_desc' ? 'selected' : '' ?>>
                            Montant engagé (décroissant)
                        </option>
                    </optgroup>
                    <optgroup label="Offres / Deals">
                        <option value="deal_date_debut_recent" <?= $currentSort === 'deal_date_debut_recent' ? 'selected' : '' ?>>
                            Offres - Date de début (récent d'abord)
                        </option>
                        <option value="deal_periode_longue" <?= $currentSort === 'deal_periode_longue' ? 'selected' : '' ?>>
                            Offres - Période la plus longue
                        </option>
                        <option value="deal_montant_desc" <?= $currentSort === 'deal_montant_desc' ? 'selected' : '' ?>>
                            Offres - Prix initial (décroissant)
                        </option>
                        <option value="deal_accept_date" <?= $currentSort === 'deal_accept_date' ? 'selected' : '' ?>>
                            Offres - Date d'acceptation (plus récentes)
                        </option>
                        <option value="deal_dispo" <?= $currentSort === 'deal_dispo' ? 'selected' : '' ?>>
                            Offres - D'abord non expirées
                        </option>
                        <option value="deal_expire" <?= $currentSort === 'deal_expire' ? 'selected' : '' ?>>
                            Offres - D'abord expirées
                        </option>
                    </optgroup>
                </select>
                <button type="submit" class="btn btn-success btn-sm">
                    Appliquer
                </button>
            </div>
        </form>
    </div>

    <h2 class="hero-title">NOS PARTENAIRES PRIVILÉGIÉS</h2>

    <div class="partners-grid">
        <?php if (!empty($sponsors)) : ?>
            <?php foreach ($sponsors as $sp) : ?>
                <div class="partner-card">
                    <div class="partner-card-top">
                        <?php if ($sp->getLogoUrl()) : ?>
                            <img src="<?= htmlspecialchars($sp->getLogoUrl(), ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Logo <?= htmlspecialchars($sp->getNomEntreprise(), ENT_QUOTES, 'UTF-8') ?>"
                                 class="partner-logo">
                        <?php else: ?>
                            <div class="partner-logo d-flex align-items-center justify-content-center">
                                <span class="text-muted" style="font-size: 11px;">Pas de logo</span>
                            </div>
                        <?php endif; ?>

                        <div class="partner-info">
                            <strong><?= htmlspecialchars($sp->getNomEntreprise(), ENT_QUOTES, 'UTF-8') ?></strong><br>

                            <?php if ($sp->getDomaineActivite()) : ?>
                                <span><?= htmlspecialchars($sp->getDomaineActivite(), ENT_QUOTES, 'UTF-8') ?></span><br>
                            <?php endif; ?>

                            <?php if ($sp->getTypeSponsoring()) : ?>
                                <span>Type :
                                    <?= htmlspecialchars($sp->getTypeSponsoring(), ENT_QUOTES, 'UTF-8') ?>
                                </span><br>
                            <?php endif; ?>

                            <?php if ($sp->getMontantEngage()) : ?>
                                <span>Montant engagé :
                                    <?= htmlspecialchars($sp->getMontantEngage(), ENT_QUOTES, 'UTF-8') ?> €
                                </span><br>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- BOUTON VOIR LE SPONSOR -->
                    <div class="partner-actions">
                        <a href="index.php?section=sponsor&id=<?= $sp->getId() ?>"
                           class="btn-view-sponsor">
                            Voir le sponsor
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">Aucun sponsor enregistré pour le moment.</p>
        <?php endif; ?>
    </div>

    <h2 class="hero-title">NOS OFFRES</h2>

    <?php if (empty($offers)): ?>
        <p class="text-center">Aucune offre disponible pour le moment.</p>
    <?php else: ?>
        <div class="offers-grid">
            <?php foreach ($offers as $o): ?>
                <?php
                $dateDebut = $o['dateDebut'] ?? null;
                $periode   = (int)($o['periodeValidite'] ?? 0);

                $isExpired = false;

                if ($dateDebut && $periode > 0) {
                    $start = new DateTime($dateDebut);
                    $end   = clone $start;
                    $end->modify('+' . $periode . ' days');
                    $now   = new DateTime('today');

                    if ($now > $end) {
                        $isExpired = true;
                    }
                }

                $badgeClass = $isExpired ? 'badge-expired' : 'badge-available';
                $badgeText  = $isExpired ? 'Expirée' : 'Disponible';
                ?>
                <div class="offer-card">
                    <div>
                        <div class="offer-title">
                            <?= htmlspecialchars($o['intitule'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="offer-sponsor">
                            Sponsor :
                            <?= htmlspecialchars($o['nomEntreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <p class="mt-2 mb-1">
                            Réduction :
                            <strong><?= htmlspecialchars($o['reduction'] ?? 0, ENT_QUOTES, 'UTF-8') ?> %</strong><br>
                            Prix initial :
                            <strong><?= htmlspecialchars($o['prixInitial'] ?? 0, ENT_QUOTES, 'UTF-8') ?> DT</strong>
                        </p>

                        <p class="mb-1">
                            Début :
                            <?= htmlspecialchars($o['dateDebut'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                            Période :
                            <?= htmlspecialchars($o['periodeValidite'] ?? 0, ENT_QUOTES, 'UTF-8') ?> jours
                        </p>

                        <span class="offer-badge <?= $badgeClass ?>">
                            <?= $badgeText ?>
                        </span>
                    </div>

                    <div class="offer-footer">
                        <?php if (!empty($o['descriptionD'])): ?>
                            <small><?= nl2br(htmlspecialchars($o['descriptionD'], ENT_QUOTES, 'UTF-8')) ?></small>
                        <?php endif; ?>

                        <div class="mt-2">
                            <a href="index.php?section=deal&idDeal=<?= (int)$o['idDeal'] ?>"
                               class="btn btn-sm btn-primary">
                                Voir le deal
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="cta">
        <a class="btn-link btn-add-sponsor"
           href="../sponsor.php?action=create">
            Devenir sponsor / Ajouter un sponsor
        </a>

        <a class="btn-link btn-add-deal"
           href="index.php?section=deal&action=create">
            Créer une offre / deal
        </a>

        <!-- 🔹 NOUVEAU : bouton Assistant IA sur les offres -->
        <a class="btn-link btn-add-deal"
           href="index.php?section=deal&action=assistantIA">
            Assistant IA sur les offres
        </a>
    </div>

</div>

<footer class="bg-dark text-light py-3">
    <div class="container">
        <p class="mb-0">
            &copy; <?= date('Y') ?> SOLIDA - Tous droits réservés.
        </p>
    </div>
</footer>

<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/jquery-1.11.0.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/jquery-migrate-1.2.1.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/bootstrap.bundle.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/templatemo.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/custom.js"></script>

<script>
    // Mot de passe admin en JS (simple)
    const ADMIN_PASSWORD = '2710';
    const adminBtnHeader = document.getElementById('btnAdminSponsorHeader');
    const adminError = document.getElementById('admin-error');

    adminBtnHeader.addEventListener('click', function () {
        adminError.style.display = 'none';

        const pwd = prompt("Veuillez entrer le mot de passe pour accéder à l'espace admin des sponsors :");

        if (pwd === null) {
            // Annulé
            return;
        }

        if (pwd === ADMIN_PASSWORD) {
            window.location.href = "../sponsor.php?action=index";
        } else {
            adminError.style.display = 'block';
        }
    });
</script>

</body>
</html>