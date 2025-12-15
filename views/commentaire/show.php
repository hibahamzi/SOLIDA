<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets1/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets1/img/favicon.png">
  <title>
    SOLIDA - Détail du forum
  </title>
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="assets1/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets1/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->
  <link id="pagestyle" href="assets1/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  
  <!-- Sidebar -->
  <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="index.php">
        <img src="assets1/img/logo-ct-dark.png" width="26px" height="26px" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">SOLIDA</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard Forum</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?controller=ForumController&action=index">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Liste des Forums</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?controller=ForumController&action=create">
            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-fat-add text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Nouveau Forum</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>

  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="index.php">Forum</a></li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">Détail du forum</li>
          </ol>
          <h6 class="font-weight-bolder text-white mb-0">Forum #<?= htmlspecialchars($forum['id_forum']) ?></h6>
        </nav>
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h6>Forum #<?= htmlspecialchars($forum['id_forum']) ?></h6>
            </div>
            <div class="card-body">
              <p><strong>Catégorie:</strong> <?= htmlspecialchars($forum['categorie']) ?></p>
              <p><strong>Discussion G:</strong><br><?= nl2br(htmlspecialchars($forum['discussion_g'])) ?></p>
              <p><strong>Discussion P:</strong><br><?= nl2br(htmlspecialchars($forum['discussion_p'])) ?></p>
              <hr>
              
              <h6>Commentaires</h6>
              <?php if(!empty($commentaires)): foreach($commentaires as $c): ?>
                <div class="card mb-3">
                  <div class="card-body">
                    <small><?= htmlspecialchars($c['nom_prenom'] ?? 'Anonyme') ?> — <?= htmlspecialchars($c['date_commentaire'] ?? '') ?></small>
                    <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
                  </div>
                </div>
              <?php endforeach; else: ?>
                <p>Aucun commentaire.</p>
              <?php endif; ?>
              
              <h6>Ajouter commentaire</h6>
              <form method="post" action="index.php?action=commentaire_add">
                <input type="hidden" name="id_forum" value="<?= $forum['id_forum'] ?>">
                <div class="form-group mb-3">
                  <label class="form-control-label">Votre id (auteur):</label>
                  <input type="text" name="id_auteur" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                  <label class="form-control-label">Contenu:</label>
                  <textarea name="contenu" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter</button>
              </form>
              
              <div class="mt-4">
                <a href="index.php?controller=ForumController&action=index" class="btn btn-secondary">Retour liste</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <footer class="footer pt-3">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>document.write(new Date().getFullYear())</script>,
                SOLIDA - Plateforme de Discussion
              </div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>

  <!-- Core JS Files -->
  <script src="assets1/js/core/popper.min.js"></script>
  <script src="assets1/js/core/bootstrap.min.js"></script>
  <script src="assets1/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets1/js/plugins/smooth-scrollbar.min.js"></script>
</body>
</html>