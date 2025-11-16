<?php
// app/views/sponsor/index.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="apple-touch-icon" sizes="76x76" href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/img/favicon.png">
  <title>Liste des sponsors</title>

  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  <!-- CSS Argon local -->
  <link id="pagestyle"
        href="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/css/argon-dashboard.css?v=2.1.0"
        rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">

  <!-- NAVBAR SIMPLE -->
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl mt-4">
    <div class="container-fluid py-1">
      <nav aria-label="breadcrumb">
        <h4 class="font-weight-bolder mb-0">Sponsors</h4>
        <span class="text-sm mb-0">Backoffice &gt; Liste des sponsors</span>
      </nav>
      <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=create"
           class="btn btn-sm bg-gradient-dark mb-0">
          <i class="fas fa-plus me-1"></i> Nouveau sponsor
        </a>
      </div>
    </div>
  </nav>

  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header pb-0">
            <h6>Liste des sponsors</h6>
          </div>
          <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Entreprise</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Statut</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($sponsors)): ?>
                    <?php foreach ($sponsors as $s): ?>
                      <tr>
                        <td>
                          <p class="text-xs font-weight-bold mb-0">
                            <?= htmlspecialchars($s['id']) ?>
                          </p>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0">
                            <?= htmlspecialchars($s['nomEntreprise']) ?>
                          </p>
                        </td>
                        <td>
                          <p class="text-xs text-secondary mb-0">
                            <?= htmlspecialchars($s['emailContact']) ?>
                          </p>
                        </td>
                        <td>
                          <span class="badge badge-sm bg-gradient-info">
                            <?= htmlspecialchars($s['typeSponsoring']) ?>
                          </span>
                        </td>
                        <td>
                          <?php
                          $statusClass = 'bg-gradient-secondary';
                          if ($s['statut'] === 'Actif') $statusClass = 'bg-gradient-success';
                          elseif ($s['statut'] === 'En attente') $statusClass = 'bg-gradient-warning';
                          ?>
                          <span class="badge badge-sm <?= $statusClass ?>">
                            <?= htmlspecialchars($s['statut']) ?>
                          </span>
                        </td>
                        <td class="align-middle text-center">
                          <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=edit&id=<?= $s['id'] ?>"
                             class="btn btn-sm btn-primary mb-0 me-1">
                            <i class="fas fa-edit"></i>
                          </a>
                          <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=delete&id=<?= $s['id'] ?>"
                             class="btn btn-sm btn-danger mb-0"
                             onclick="return confirm('Supprimer ce sponsor ?');">
                            <i class="fas fa-trash"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center py-4">
                        Aucun sponsor pour le moment.
                        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=create">
                          Ajouter un sponsor
                        </a>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer pt-3">
      <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-12 mb-lg-0 mb-4 text-center">
            <div class="copyright text-secondary text-sm">
              Copyright ©
              <script>document.write(new Date().getFullYear())</script>
              Soft by Creative Tim.
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- JS Argon -->
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/core/popper.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/core/bootstrap.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="/PROJET_WEB_MVC_FINAL/public/argon-dashboard-3-main/assets/js/argon-dashboard.min.js?v=2.1.0"></script>
</body>
</html>