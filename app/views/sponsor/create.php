<?php
// app/views/sponsor/create.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="apple-touch-icon" sizes="76x76" href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/img/favicon.png">
  <title>Ajouter un sponsor</title>

  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

  <!-- Nucleo Icons (mêmes que sign-up, en CDN) -->
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />

  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- CSS Argon local : *** CHEMIN ADAPTÉ À TON DOSSIER *** -->
  <link id="pagestyle"
        href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/css/argon-dashboard.css?v=2.1.0"
        rel="stylesheet" />
</head>

<body class="">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white"
         href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index">
        Backoffice Sponsors
      </a>
    </div>
  </nav>
  <!-- End Navbar -->

  <main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg"
         style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signup-cover.jpg'); background-position: top;">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 text-center mx-auto">
            <h1 class="text-white mb-2 mt-5">Nouveau sponsor</h1>
            <p class="text-lead text-white">
              Utilisez ce formulaire pour ajouter un sponsor dans votre projet.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card z-index-0">
            <div class="card-header text-center pt-4">
              <h5>Informations du sponsor</h5>
            </div>

            <div class="card-body">
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <form role="form" method="post">
                <div class="mb-3">
                  <label class="form-label">Nom Entreprise *</label>
                  <input type="text" name="nomEntreprise" class="form-control" placeholder="Nom de l'entreprise" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Email Contact *</label>
                  <input type="email" name="emailContact" class="form-control" placeholder="email@exemple.com" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Téléphone</label>
                  <input type="text" name="telephone" class="form-control" placeholder="+216 ...">
                </div>

                <div class="mb-3">
                  <label class="form-label">Adresse</label>
                  <input type="text" name="adresse" class="form-control" placeholder="Adresse complète">
                </div>

                <div class="mb-3">
                  <label class="form-label">Type Sponsoring *</label>
                  <select name="typeSponsoring" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Or">Or</option>
                    <option value="Argent">Argent</option>
                    <option value="Bronze">Bronze</option>
                    <option value="Partenaire">Partenaire</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Montant engagé (€)</label>
                  <input type="number" step="0.01" name="montantEngage" class="form-control" placeholder="Ex : 5000.00">
                </div>

                <div class="mb-3">
                  <label class="form-label">Domaine d'activité</label>
                  <input type="text" name="domaineActivite" class="form-control" placeholder="Ex : Technologie, Banque...">
                </div>

                <div class="mb-3">
                  <label class="form-label">Logo URL</label>
                  <input type="text" name="logoUrl" class="form-control" placeholder="https://.../logo.png">
                </div>

                <div class="mb-3">
                  <label class="form-label">Contrat URL (PDF)</label>
                  <input type="text" name="contratUrl" class="form-control" placeholder="https://.../contrat.pdf">
                </div>

                <div class="mb-3">
                  <label class="form-label">Date début partenariat</label>
                  <input type="date" name="dateDebutPartenaire" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Date fin partenariat</label>
                  <input type="date" name="dateFinPartenaire" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Statut *</label>
                  <select name="statut" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Actif">Actif</option>
                    <option value="Inactif">Inactif</option>
                    <option value="En attente">En attente</option>
                  </select>
                </div>

                <div class="text-center">
                  <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">
                    Enregistrer le sponsor
                  </button>
                </div>
                <p class="text-sm mt-3 mb-0 text-center">
                  <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index"
                     class="text-dark font-weight-bolder">
                    &larr; Retour à la liste des sponsors
                  </a>
                </p>
              </form>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mb-4 mx-auto text-center">
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">Company</a>
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">About Us</a>
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">Team</a>
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">Products</a>
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">Blog</a>
          <a href="javascript:;" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">Pricing</a>
        </div>
        <div class="col-lg-8 mx-auto text-center mb-4 mt-2">
          <a href="javascript:;" class="text-secondary me-xl-4 me-4"><span class="text-lg fab fa-dribbble"></span></a>
          <a href="javascript:;" class="text-secondary me-xl-4 me-4"><span class="text-lg fab fa-twitter"></span></a>
          <a href="javascript:;" class="text-secondary me-xl-4 me-4"><span class="text-lg fab fa-instagram"></span></a>
          <a href="javascript:;" class="text-secondary me-xl-4 me-4"><span class="text-lg fab fa-pinterest"></span></a>
          <a href="javascript:;" class="text-secondary me-xl-4 me-4"><span class="text-lg fab fa-github"></span></a>
        </div>
      </div>
      <div class="row">
        <div class="col-8 mx-auto text-center mt-1">
          <p class="mb-0 text-secondary">
            Copyright ©
            <script>document.write(new Date().getFullYear())</script>
            Soft by Creative Tim.
          </p>
        </div>
      </div>
    </div>
  </footer>

  <!-- JS Files : même dossier que le CSS -->
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/core/popper.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/core/bootstrap.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/argon-dashboard.min.js?v=2.1.0"></script>
</body>
</html>