<?php
// $deal est fourni par DealController::show()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Détail du deal - SOLIDA</title>
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
        .deal-detail-wrapper {
            width: 80%;
            margin: 0 auto 60px auto;
        }
        .deal-detail-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .deal-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .deal-sponsor {
            font-weight: 500;
            color: #17a2b8;
            margin-bottom: 15px;
        }
        .deal-meta {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .deal-meta strong {
            font-weight: 600;
        }
        .offer-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
            color: #fff;
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-expired {
            background-color: #dc3545;
        }
        .deal-description {
            margin-top: 15px;
            font-size: 15px;
        }
        .deal-views {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }
        .back-link {
            display:inline-block;
            margin-top:20px;
            text-decoration:none;
            color:#007bff;
            font-weight:500;
        }
        .back-link i {
            margin-right:5px;
        }

        /* Bloc coupon */
        .coupon-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .coupon-info {
            font-size: 14px;
        }
        .coupon-code-text {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 3px;
        }
        .coupon-code-hidden {
            display: none;
            margin-top: 10px;
        }

        /* Bouton imprimer */
        .print-btn-container {
            margin-top: 20px;
            text-align: right;
        }

        @media (max-width: 576px) {
            .deal-detail-wrapper {
                width: 95%;
            }
            .clr {
                background-color: green;
            }
        }

        /* Styles pour l'impression (PDF via Ctrl+P / Imprimer) */
        @media print {
            body {
                background: #ffffff !important;
            }
            /* Cacher les menus, footer, boutons inutiles */
            #templatemo_nav_top,
            .clr,
            footer,
            .back-link,
            .print-btn-container,
            .modal,
            .navbar {
                display: none !important;
            }
            .deal-detail-wrapper {
                width: 100% !important;
                margin: 0 !important;
            }
            .deal-detail-card {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>

<body>

<!-- ======= Top Nav noir ======= -->
<nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
    <div class="container text-light">
        <div class="w-100 d-flex justify-content-between">
            <div>
                <i class="fa fa-envelope mx-2"></i>
                <a class="navbar-sm-brand text-light text-decoration-none"
                   href="mailto:contact@solida.com">contact@solida.com</a>
                <i class="fa fa-phone mx-2"></i>
                <a class="navbar-sm-brand text-light text-decoration-none"
                   href="tel:010-020-0340">010-020-0340</a>
            </div>
            <div>
                <a class="text-light" href="#" target="_blank"><i class="fab fa-facebook-f fa-sm fa-fw me-2"></i></a>
                <a class="text-light" href="#" target="_blank"><i class="fab fa-instagram fa-sm fa-fw me-2"></i></a>
                <a class="text-light" href="#" target="_blank"><i class="fab fa-twitter fa-sm fa-fw me-2"></i></a>
                <a class="text-light" href="#" target="_blank"><i class="fab fa-linkedin fa-sm fa-fw"></i></a>
            </div>
        </div>
    </div>
</nav>

<div class="clr">
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-success logo h1 align-self-center"
               href="/PROJET_WEB_MVC_FINAL/public/index1.php">
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
                            <a class="nav-link" href="/PROJET_WEB_MVC_FINAL/public/index1.php">Accueil</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#">Événements / Shop</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Forum</a></li>
                        <li class="nav-item">
                            <a class="nav-link active"
                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front">
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
                        <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark">7</span>
                    </a>
                    <a class="nav-icon position-relative text-decoration-none" href="#">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                        <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark">+99</span>
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

    <h2 class="hero-title">DÉTAIL DU DEAL</h2>

    <?php
    // Calcul pour badge "Disponible / Expirée"
    $dateDebut = $deal['dateDebut'] ?? null;
    $periode   = (int)($deal['periodeValidite'] ?? 0);
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

    // Coupon + QR (API goqr.me)
    $hasCoupon  = $deal['has_coupon'] ?? 'NON';
    $couponCode = $deal['coupon_code'] ?? null;
    $qrUrl      = null;

    if ($hasCoupon === 'OUI' && !empty($couponCode)) {
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?'
               . 'size=200x200'
               . '&data=' . urlencode($couponCode);
    }
    ?>

    <div class="deal-detail-wrapper">
        <div class="deal-detail-card" id="deal-print-block">

            <!-- Bouton imprimer -->
            <div class="print-btn-container">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
                    <i class="fa fa-print"></i> Imprimer l'offre
                </button>
            </div>

            <!-- NOUVEL LAYOUT : 2 colonnes dans le même bloc -->
            <div class="row">
                <!-- Colonne gauche : informations du deal -->
                <div class="col-md-7">
                    <div class="deal-title">
                        <?= htmlspecialchars($deal['intitule'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <div class="deal-sponsor">
                        Sponsor : <?= htmlspecialchars($deal['nomEntreprise'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                    <div class="deal-meta">
                        Prix initial :
                        <strong><?= htmlspecialchars($deal['prixInitial'], ENT_QUOTES, 'UTF-8') ?> DT</strong><br>
                        Réduction :
                        <strong><?= htmlspecialchars($deal['reduction'], ENT_QUOTES, 'UTF-8') ?> %</strong>
                    </div>

                    <div class="deal-meta">
                        Début :
                        <?= htmlspecialchars($deal['dateDebut'], ENT_QUOTES, 'UTF-8') ?><br>
                        Période :
                        <?= htmlspecialchars($deal['periodeValidite'], ENT_QUOTES, 'UTF-8') ?> jours
                    </div>

                    <div class="deal-meta">
                        Statut :
                        <span class="offer-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>

                    <div class="deal-views">
                        <i class="fa fa-eye"></i>
                        Vues :
                        <strong><?= (int)$deal['click_count'] ?></strong>
                    </div>

                    <?php if (!empty($deal['descriptionD'])): ?>
                        <div class="deal-description">
                            <?= nl2br(htmlspecialchars($deal['descriptionD'], ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Colonne droite : QR + texte + bouton -->
                <div class="col-md-5">
                    <?php if ($qrUrl): ?>
                        <div class="coupon-section" style="border-top:none; margin-top:0; padding-top:0;">
                            <div>
                                <img src="<?= $qrUrl ?>"
                                     alt="QR Code coupon"
                                     width="200"
                                     height="200"
                                     style="border:1px solid #ddd; border-radius:10px; background:#fff;">
                            </div>
                            <div class="coupon-info">
                                <p><strong>Coupon disponible pour ce deal</strong></p>
                                <p>Scannez ce QR code avec votre téléphone pour voir le code du coupon.</p>

                                <!-- Bouton pour voir le code directement sans scanner -->
                                <button type="button" class="btn btn-success btn-sm mt-2"
                                        onclick="document.getElementById('coupon-code').classList.toggle('coupon-code-hidden');">
                                    Voir le code
                                </button>

                                <div id="coupon-code" class="coupon-code-hidden">
                                    <p class="mt-2">Code :</p>
                                    <span class="coupon-code-text">
                                        <?= htmlspecialchars($couponCode, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- fin row -->

            <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front" class="back-link">
                <i class="fa fa-arrow-left"></i>
                Retour aux offres
            </a>
        </div>
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
</body>
</html>