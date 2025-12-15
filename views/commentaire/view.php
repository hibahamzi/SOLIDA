<?php
// $forum est une instance de ForumModel fournie par ForumController::view()

$id          = htmlspecialchars($forum->getIdForum());
$categorie   = htmlspecialchars($forum->getCategorie());
$discussionG = htmlspecialchars($forum->getDiscussionG());
$discussionP = htmlspecialchars($forum->getDiscussionP());
$date        = method_exists($forum, 'getDateCreationFormatted')
    ? htmlspecialchars($forum->getDateCreationFormatted())
    : (method_exists($forum, 'getDateCreation')
        ? htmlspecialchars($forum->getDateCreation())
        : '');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Discussion #<?php echo $id; ?> - SOLIDA Forum</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets1/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets1/img/favicon.ico">

    <link rel="stylesheet" href="assets1/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets1/css/templatemo.css">
    <link rel="stylesheet" href="assets1/css/custom.css">

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets1/css/fontawesome.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .forum-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px 0 60px 0;
        }
        .thread-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #28a745;
        }
        .meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .discussion-content {
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .badge-category {
            background: #e3f2fd;
            color: #1976d2;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <!-- Top Nav + Header reprennent le même style que tes autres pages -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
        <div class="container text-light">
            <div class="w-100 d-flex justify-content-between">
                <div>
                    <i class="fa fa-envelope mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none"
                       href="mailto:contact@solida.org">contact@solida.org</a>
                    <i class="fa fa-phone mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none"
                       href="tel:+212-600-000000">+212 600 000000</a>
                </div>
                <div>
                    <a class="text-light" href="https://fb.com" target="_blank" rel="sponsored">
                        <i class="fab fa-facebook-f fa-sm fa-fw me-2"></i>
                    </a>
                    <a class="text-light" href="https://www.instagram.com/" target="_blank">
                        <i class="fab fa-instagram fa-sm fa-fw me-2"></i>
                    </a>
                    <a class="text-light" href="https://twitter.com/" target="_blank">
                        <i class="fab fa-twitter fa-sm fa-fw me-2"></i>
                    </a>
                    <a class="text-light" href="https://www.linkedin.com/" target="_blank">
                        <i class="fab fa-linkedin fa-sm fa-fw"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-success logo h1 align-self-center" href="index.php">
                SOLIDA
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="align-self-center collapse navbar-collapse flex-fill d-lg-flex justify-content-lg-between"
                 id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.html">À propos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active"
                               href="index.php?controller=ForumController&action=index">Forum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.html">Contact</a>
                        </li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <a class="nav-icon d-none d-lg-inline" href="#" data-bs-toggle="modal"
                       data-bs-target="#templatemo_search">
                        <i class="fa fa-fw fa-search text-dark mr-2"></i>
                    </a>
                    <a class="nav-icon position-relative text-decoration-none" href="#">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Banner -->
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="h1 text-success">
                    <i class="fas fa-comment-dots me-3"></i>Discussion
                </h1>
            </div>
        </div>
    </div>

    <!-- Contenu sujet -->
    <div class="container forum-container">
        <h2 class="thread-title"><?php echo $discussionG; ?></h2>
        <div class="meta">
            Catégorie :
            <span class="badge-category"><?php echo $categorie; ?></span>
            <?php if (!empty($date)): ?>
                &nbsp;—&nbsp; Créé le <?php echo $date; ?>
            <?php endif; ?>
        </div>

        <div class="discussion-content">
            <?php echo nl2br($discussionP); ?>
        </div>

        <div class="mt-4">
            <a href="index.php?controller=ForumController&action=index"
               class="btn btn-outline-success">
                <i class="fas fa-arrow-left"></i> Retour au forum
            </a>
        </div>
    </div>

    <!-- Footer (même que les autres pages) -->
    <footer class="bg-dark" id="tempaltemo_footer">
        <!-- ... même footer que dans create/index ... -->
        <!-- tu peux copier/coller exactement ton footer existant -->
    </footer>

    <script src="assets1/js/jquery-1.11.0.min.js"></script>
    <script src="assets1/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets1/js/bootstrap.bundle.min.js"></script>
    <script src="assets1/js/templatemo.js"></script>
    <script src="assets1/js/custom.js"></script>
</body>
</html>