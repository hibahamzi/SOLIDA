<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Devenir sponsor - SOLIDA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FAVICONS -->
    <link rel="apple-touch-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/img/favicon.ico">

    <!-- CSS DU TEMPLATE FRONT (même que front.php) -->
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

        .sponsor-form-wrapper {
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

        .logo-preview {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 8px;
            object-fit: contain;
            background: #f5f5f5;
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

<!-- Header (comme front.php, sans "active" sur Sponsors) -->
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
<!-- Close Header -->

<!-- Modal Search -->
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

<!-- CONTENU : FORMULAIRE DEVENIR SPONSOR -->
<div class="container py-5">

    <h2 class="hero-title">🤝 Devenir sponsor de SOLIDA</h2>

    <div class="sponsor-form-wrapper">
        <p class="mb-4">
            Remplissez ce formulaire pour proposer un partenariat avec notre association étudiante.
            Les champs marqués d'une <span class="required">*</span> sont obligatoires.
        </p>

        <form id="sponsorCreateForm" method="post"
              action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=create"
              enctype="multipart/form-data">

            <!-- NOM ENTREPRISE -->
            <div class="mb-3">
                <label class="form-label">Nom de l'entreprise <span class="required">*</span></label>
                <input type="text" name="nomEntreprise" id="nomEntreprise"
                       class="form-control"
                       value="<?= htmlspecialchars($old['nomEntreprise'] ?? '') ?>">
                <div class="error-message" id="error-nomEntreprise"></div>
            </div>

            <!-- EMAIL + TELEPHONE -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email de contact <span class="required">*</span></label>
                    <input type="text" name="emailContact" id="emailContact"
                           class="form-control"
                           value="<?= htmlspecialchars($old['emailContact'] ?? '') ?>">
                    <div class="error-message" id="error-emailContact"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Téléphone (8 chiffres)</label>
                    <input type="text" name="telephone" id="telephone"
                           class="form-control"
                           value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
                    <div class="error-message" id="error-telephone"></div>
                </div>
            </div>

            <!-- ADRESSE -->
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" id="adresse"
                       class="form-control"
                       value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
                <div class="error-message" id="error-adresse"></div>
            </div>

            <!-- TYPE SPONSORING + MONTANT + DOMAINE -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type de sponsoring <span class="required">*</span></label>
                    <select name="typeSponsoring" id="typeSponsoring" class="form-select">
                        <option value="">-- Choisir --</option>
                        <option value="Financier" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Financier') ? 'selected' : '' ?>>Financier</option>
                        <option value="Matériel" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Matériel') ? 'selected' : '' ?>>Matériel</option>
                        <option value="Média" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Média') ? 'selected' : '' ?>>Média</option>
                    </select>
                    <div class="error-message" id="error-typeSponsoring"></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Montant engagé (€)</label>
                    <input type="text" name="montantEngage" id="montantEngage"
                           class="form-control"
                           value="<?= htmlspecialchars($old['montantEngage'] ?? '') ?>">
                    <div class="error-message" id="error-montantEngage"></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Domaine d'activité</label>
                    <input type="text" name="domaineActivite" id="domaineActivite"
                           class="form-control"
                           value="<?= htmlspecialchars($old['domaineActivite'] ?? '') ?>">
                    <div class="error-message" id="error-domaineActivite"></div>
                </div>
            </div>

            <!-- LOGO (UPLOAD, OPTIONNEL) + CONTRAT (URL TEXTE) -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Logo de l'entreprise
                        <small class="text-muted d-block">(image JPG/PNG/GIF, max 1 Mo, optionnel)</small>
                    </label>
                    <input type="file" name="logo" id="logo"
                           class="form-control" accept="image/*">
                    <div class="error-message" id="error-logo"></div>
                    <img id="logoPreview" class="logo-preview d-none" alt="Prévisualisation du logo">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contrat (URL)</label>
                    <input type="text" name="contratUrl" id="contratUrl"
                           class="form-control"
                           value="<?= htmlspecialchars($old['contratUrl'] ?? '') ?>">
                    <div class="error-message" id="error-contratUrl"></div>
                </div>
            </div>

            <!-- DATES (OBLIGATOIRES) -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date début partenariat <span class="required">*</span></label>
                    <input type="date" name="dateDebutPartenaire" id="dateDebutPartenaire"
                           class="form-control"
                           value="<?= htmlspecialchars($old['dateDebutPartenaire'] ?? '') ?>">
                    <div class="error-message" id="error-dateDebutPartenaire"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date fin partenariat <span class="required">*</span></label>
                    <input type="date" name="dateFinPartenaire" id="dateFinPartenaire"
                           class="form-control"
                           value="<?= htmlspecialchars($old['dateFinPartenaire'] ?? '') ?>">
                    <div class="error-message" id="error-dateFinPartenaire"></div>
                </div>
            </div>

            <!-- STATUT -->
            <div class="mb-3">
                <label class="form-label">Statut <span class="required">*</span></label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">-- Choisir --</option>
                    <option value="actif" <?= (isset($old['statut']) && $old['statut'] === 'actif') ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= (isset($old['statut']) && $old['statut'] === 'inactif') ? 'selected' : '' ?>>Inactif</option>
                </select>
                <div class="error-message" id="error-statut"></div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front"
                   class="btn btn-secondary">
                    ⟵ Retour à la page sponsors
                </a>
                <button type="submit" class="btn btn-success">
                    Envoyer ma demande de sponsoring
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
const MAX_LOGO_SIZE = 1 * 1024 * 1024; // 1 Mo
const logoInput = document.getElementById('logo');
const logoPreview = document.getElementById('logoPreview');

// Prévisualisation + contrôle de base du logo (optionnel)
logoInput.addEventListener('change', function () {
    const file = this.files[0];
    document.getElementById('error-logo').innerText = '';
    logoInput.classList.remove('is-invalid');
    logoPreview.classList.add('d-none');
    logoPreview.src = '';

    if (!file) return; // rien choisi → logo optionnel

    // type image
    if (!file.type.startsWith('image/')) {
        document.getElementById('error-logo').innerText =
            "Le fichier doit être une image (JPG, PNG, GIF...).";
        logoInput.classList.add('is-invalid');
        this.value = '';
        return;
    }

    // taille max 1 Mo
    if (file.size > MAX_LOGO_SIZE) {
        document.getElementById('error-logo').innerText =
            "Le logo est trop grand (taille max 1 Mo).";
        logoInput.classList.add('is-invalid');
        this.value = '';
        return;
    }

    // prévisualisation
    const reader = new FileReader();
    reader.onload = function (e) {
        logoPreview.src = e.target.result;
        logoPreview.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});

// Validation côté client - création sponsor
document.getElementById('sponsorCreateForm').addEventListener('submit', function (e) {
    let hasError = false;

    const nomEntreprise       = document.getElementById('nomEntreprise');
    const emailContact        = document.getElementById('emailContact');
    const telephone           = document.getElementById('telephone');
    const typeSponsoring      = document.getElementById('typeSponsoring');
    const montantEngage       = document.getElementById('montantEngage');
    const statut              = document.getElementById('statut');
    const dateDebutPartenaire = document.getElementById('dateDebutPartenaire');
    const dateFinPartenaire   = document.getElementById('dateFinPartenaire');

    const fields = [
        nomEntreprise, emailContact, telephone, typeSponsoring,
        montantEngage, statut, dateDebutPartenaire, dateFinPartenaire
    ];
    const errorIds = [
        'error-nomEntreprise','error-emailContact','error-telephone',
        'error-typeSponsoring','error-montantEngage','error-statut',
        'error-dateDebutPartenaire','error-dateFinPartenaire'
    ];

    // Reset erreurs
    fields.forEach((field, index) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[index]).innerText = '';
    });
    document.getElementById('error-logo').innerText = '';
    logoInput.classList.remove('is-invalid');

    // Nom obligatoire
    if (nomEntreprise.value.trim() === '') {
        hasError = true;
        nomEntreprise.classList.add('is-invalid');
        document.getElementById('error-nomEntreprise').innerText =
            "Le nom de l'entreprise est obligatoire.";
    }

    // Email obligatoire + format
    const emailVal = emailContact.value.trim();
    if (emailVal === '') {
        hasError = true;
        emailContact.classList.add('is-invalid');
        document.getElementById('error-emailContact').innerText =
            "L'email de contact est obligatoire.";
    } else {
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(emailVal)) {
            hasError = true;
            emailContact.classList.add('is-invalid');
            document.getElementById('error-emailContact').innerText =
                "Format d'email invalide (ex: nom@domaine.com).";
        }
    }

    // Téléphone : optionnel, si rempli → 8 chiffres
    const telVal = telephone.value.trim();
    if (telVal !== '') {
        const regexTel = /^\d{8}$/;
        if (!regexTel.test(telVal)) {
            hasError = true;
            telephone.classList.add('is-invalid');
            document.getElementById('error-telephone').innerText =
                "Le téléphone doit contenir exactement 8 chiffres.";
        }
    }

    // Type sponsoring obligatoire
    if (typeSponsoring.value.trim() === '') {
        hasError = true;
        typeSponsoring.classList.add('is-invalid');
        document.getElementById('error-typeSponsoring').innerText =
            "Le type de sponsoring est obligatoire.";
    }

    // Montant : optionnel, si rempli > 0
    const montantVal = montantEngage.value.trim();
    if (montantVal !== '') {
        if (isNaN(montantVal) || Number(montantVal) <= 0) {
            hasError = true;
            montantEngage.classList.add('is-invalid');
            document.getElementById('error-montantEngage').innerText =
                "Le montant engagé doit être un nombre positif.";
        }
    }

    // Dates : OBLIGATOIRES
    const dateDebVal = dateDebutPartenaire.value.trim();
    const dateFinVal = dateFinPartenaire.value.trim();

    if (dateDebVal === '') {
        hasError = true;
        dateDebutPartenaire.classList.add('is-invalid');
        document.getElementById('error-dateDebutPartenaire').innerText =
            "La date de début de partenariat est obligatoire.";
    }

    if (dateFinVal === '') {
        hasError = true;
        dateFinPartenaire.classList.add('is-invalid');
        document.getElementById('error-dateFinPartenaire').innerText =
            "La date de fin de partenariat est obligatoire.";
    }

    // Si les deux dates sont présentes, on vérifie l'ordre logique
    if (dateDebVal !== '' && dateFinVal !== '') {
        const dDeb = new Date(dateDebVal);
        const dFin = new Date(dateFinVal);

        if (dFin < dDeb) {
            hasError = true;
            dateFinPartenaire.classList.add('is-invalid');
            document.getElementById('error-dateFinPartenaire').innerText =
                "La date de fin doit être postérieure ou égale à la date de début.";
        }
    }

    // Statut obligatoire
    if (statut.value.trim() === '') {
        hasError = true;
        statut.classList.add('is-invalid');
        document.getElementById('error-statut').innerText =
            "Le statut est obligatoire.";
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>

</body>
</html>