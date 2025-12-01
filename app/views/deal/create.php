<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Proposer une offre - SOLIDA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICONS -->
    <link rel="apple-touch-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/favicon.ico">

    <!-- CSS TEMPLATE FRONT (même que sponsors/front.php) -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/templatemo.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/custom.css">
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

        .deal-form-wrapper {
            max-width: 900px;
            margin: 0 auto 60px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .required {
            color: red;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #dc3545;
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
<!-- ======= Fin Top Nav ======= -->

<!-- Header front SOLIDA (copié de sponsors/front.php) -->
<div class="clr">
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">

            <a class="navbar-brand text-success logo h1 align-self-center"
               href="/PROJET_WEB_MVC_FINAL/public/index1.php">
                SOLIDA
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between"
                 id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/PROJET_WEB_MVC_FINAL/public/index1.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Événements / Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Forum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front">
                                Sponsors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=create">
                                Proposer une offre
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Mon Compte</a>
                        </li>
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
<!-- /Header -->

<!-- Modal Search (optionnel) -->
<div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="w-100 pt-1 mb-5 text-right">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="get" class="modal-content modal-body border-0 p-0">
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="inputModalSearch" name="q" placeholder="Search ...">
                <button type="submit" class="input-group-text bg-success text-light">
                    <i class="fa fa-fw fa-search text-white"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CONTENU : FORMULAIRE PROPOSER UNE OFFRE -->
<div class="container py-5">
    <h2 class="hero-title">🎁 Proposer une offre promotionnelle</h2>

    <div class="deal-form-wrapper">
        <p class="mb-4">
            Remplissez ce formulaire pour proposer une offre en partenariat avec SOLIDA.
            Les champs marqués d'une <span class="required">*</span> sont obligatoires.
        </p>

        <form id="dealCreateForm" method="post"
              action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=store">

            <!-- INTITULE -->
            <div class="mb-3">
                <label class="form-label">Intitulé de l'offre <span class="required">*</span></label>
                <input type="text" name="intitule" id="intitule" class="form-control">
                <div class="error-message" id="error-intitule"></div>
            </div>

            <!-- SPONSOR + PRIX INITIAL -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sponsor concerné <span class="required">*</span></label>
                    <select name="idSponsor" id="idSponsor" class="form-select">
                        <option value="">-- Choisir un sponsor --</option>
                        <?php foreach ($sponsors as $s): ?>
                            <option value="<?= (int)$s['id'] ?>">
                                <?= htmlspecialchars($s['nomEntreprise']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-message" id="error-idSponsor"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prix initial (€) <span class="required">*</span></label>
                    <input type="text" name="prixInitial" id="prixInitial" class="form-control">
                    <div class="error-message" id="error-prixInitial"></div>
                </div>
            </div>

            <!-- REDUCTION -->
            <div class="mb-3">
                <label class="form-label">Réduction (%) <span class="required">*</span></label>
                <input type="text" name="reduction" id="reduction" class="form-control">
                <div class="error-message" id="error-reduction"></div>
            </div>

            <!-- DESCRIPTION -->
            <div class="mb-3">
                <label class="form-label">Description de l'offre <span class="required">*</span></label>
                <textarea name="descriptionD" id="descriptionD" rows="4" class="form-control"></textarea>
                <div class="error-message" id="error-descriptionD"></div>
            </div>

            <!-- DATES + PÉRIODE -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date de début <span class="required">*</span></label>
                    <input type="date" name="dateDebut" id="dateDebut" class="form-control">
                    <div class="error-message" id="error-dateDebut"></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Période de validité (jours) <span class="required">*</span></label>
                    <input type="text" name="periodeValidite" id="periodeValidite" class="form-control">
                    <div class="error-message" id="error-periodeValidite"></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Expire ? <span class="required">*</span></label>
                    <select name="expire" id="expire" class="form-select">
                        <option value="NON" selected>NON</option>
                        <option value="OUI">OUI</option>
                    </select>
                    <div class="error-message" id="error-expire"></div>
                </div>
            </div>

            <!-- NOTE (optionnelle) -->
            <div class="mb-3">
                <label class="form-label">Note (1 à 5, optionnel)</label>
                <input type="text" name="note" id="note" class="form-control">
                <div class="error-message" id="error-note"></div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front"
                   class="btn btn-secondary">
                    ⟵ Retour
                </a>
                <button type="submit" class="btn btn-success">
                    Envoyer ma proposition d'offre
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-light py-3">
    <div class="container">
        <p class="mb-0">
            &copy; <?= date('Y') ?> SOLIDA - Tous droits réservés.
        </p>
    </div>
</footer>

<!-- JS TEMPLATE -->
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/jquery-1.11.0.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/bootstrap.bundle.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/templatemo.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/custom.js"></script>

<script>
// Validation côté client - création deal (front)
document.getElementById('dealCreateForm').addEventListener('submit', function (e) {
    let hasError = false;

    const intitule        = document.getElementById('intitule');
    const idSponsor       = document.getElementById('idSponsor');
    const prixInitial     = document.getElementById('prixInitial');
    const reduction       = document.getElementById('reduction');
    const descriptionD    = document.getElementById('descriptionD');
    const dateDebut       = document.getElementById('dateDebut');
    const periodeValidite = document.getElementById('periodeValidite');
    const expire          = document.getElementById('expire');
    const note            = document.getElementById('note');

    const fields = [
        intitule, idSponsor, prixInitial, reduction,
        descriptionD, dateDebut, periodeValidite, expire, note
    ];
    const errorIds = [
        'error-intitule','error-idSponsor','error-prixInitial','error-reduction',
        'error-descriptionD','error-dateDebut','error-periodeValidite','error-expire','error-note'
    ];

    fields.forEach((field, index) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[index]).innerText = '';
    });

    // Intitulé obligatoire
    if (intitule.value.trim() === '') {
        hasError = true;
        intitule.classList.add('is-invalid');
        document.getElementById('error-intitule').innerText =
            "L'intitulé de l'offre est obligatoire.";
    }

    // Sponsor obligatoire
    if (idSponsor.value.trim() === '') {
        hasError = true;
        idSponsor.classList.add('is-invalid');
        document.getElementById('error-idSponsor').innerText =
            "Veuillez choisir un sponsor.";
    }

    // Prix initial : obligatoire, nombre positif
    const prixVal = prixInitial.value.trim();
    if (prixVal === '' || isNaN(prixVal) || Number(prixVal) <= 0) {
        hasError = true;
        prixInitial.classList.add('is-invalid');
        document.getElementById('error-prixInitial').innerText =
            "Le prix initial doit être un nombre positif.";
    }

    // Réduction : obligatoire, nombre >= 0
    const redVal = reduction.value.trim();
    if (redVal === '' || isNaN(redVal) || Number(redVal) < 0) {
        hasError = true;
        reduction.classList.add('is-invalid');
        document.getElementById('error-reduction').innerText =
            "La réduction doit être un nombre supérieur ou égal à 0.";
    }

    // Description obligatoire
    if (descriptionD.value.trim() === '') {
        hasError = true;
        descriptionD.classList.add('is-invalid');
        document.getElementById('error-descriptionD').innerText =
            "La description est obligatoire.";
    }

    // Date début obligatoire
    const debVal = dateDebut.value.trim();
    if (debVal === '') {
        hasError = true;
        dateDebut.classList.add('is-invalid');
        document.getElementById('error-dateDebut').innerText =
            "La date de début est obligatoire.";
    }

    // Période : obligatoire, nombre > 0
    const perVal = periodeValidite.value.trim();
    if (perVal === '' || isNaN(perVal) || Number(perVal) <= 0) {
        hasError = true;
        periodeValidite.classList.add('is-invalid');
        document.getElementById('error-periodeValidite').innerText =
            "La période de validité doit être un nombre de jours positif.";
    }

    // Note optionnelle, si remplie : 1 à 5
    const noteVal = note.value.trim();
    if (noteVal !== '') {
        if (isNaN(noteVal) || Number(noteVal) < 1 || Number(noteVal) > 5) {
            hasError = true;
            note.classList.add('is-invalid');
            document.getElementById('error-note').innerText =
                "La note doit être un nombre entre 1 et 5.";
        }
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>

</body>
</html>