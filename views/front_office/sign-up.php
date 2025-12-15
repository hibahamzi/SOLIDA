<?php
session_start();

// Récupérer les erreurs et les anciennes données
$errors = $_SESSION['signup_errors'] ?? [];
$message = $_SESSION['signup_message'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];

// Nettoyer les variables de session
unset($_SESSION['signup_errors']);
unset($_SESSION['signup_message']);
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription - SOLIDA</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/sign up.css">

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">


</head>

<body>
    <!-- Start Top Nav -->
    <nav class="navbar navbar-expand-lg bg-dark navbar-light d-none d-lg-block" id="templatemo_nav_top">
        <div class="container text-light">
            <div class="w-100 d-flex justify-content-between">
                <div>
                    <i class="fa fa-envelope mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="mailto:info@company.com">info@company.com</a>
                    <i class="fa fa-phone mx-2"></i>
                    <a class="navbar-sm-brand text-light text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                </div>
                <div>
                    <a class="text-light" href="https://fb.com/templatemo" target="_blank" rel="sponsored"><i class="fab fa-facebook-f fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://twitter.com/" target="_blank"><i class="fab fa-twitter fa-sm fa-fw me-2"></i></a>
                    <a class="text-light" href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin fa-sm fa-fw"></i></a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Top Nav -->

    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light shadow">
        <div class="container d-flex justify-content-between align-items-center">

            <a class="navbar-brand text-success logo h1 align-self-center" href="index.php">
                SOLIDA
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="align-self-center collapse navbar-collapse flex-fill  d-lg-flex justify-content-lg-between" id="templatemo_main_nav">
                <div class="flex-fill">
                    <ul class="nav navbar-nav d-flex justify-content-between mx-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Événement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Dons</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Reclamation</a>
                        </li>
                    </ul>
                </div>
                <div class="navbar align-self-center d-flex">
                    <div class="d-lg-none flex-sm-fill mt-3 mb-4 col-7 col-sm-auto pr-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputMobileSearch" placeholder="Search ...">
                            <div class="input-group-text">
                                <i class="fa fa-fw fa-search"></i>
                            </div>
                        </div>
                    </div>
                    <a class="nav-icon d-none d-lg-inline" href="#" data-bs-toggle="modal" data-bs-target="#templatemo_search">
                        <i class="fa fa-fw fa-search text-dark mr-2"></i>
                    </a>

                    <a class="nav-icon position-relative text-decoration-none" href="sign-up.php">
                        <i class="fa fa-fw fa-user text-dark mr-3"></i>
                        <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark"></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!-- Close Header -->

    <!-- Sign Up Form -->
    <section class="signup-section">
        <div class="container">
            <div class="signup-box">
                <h2 class="text-center mb-4 text-success">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </h2>
                
                <?php if ($message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form id="signupForm" action="../../controllers/userControllers.php" method="POST">
                    
                    <!-- Nom Complet -->
                    <div class="mb-3">
                        <label for="fullname" class="form-label">
                            <i class="fas fa-user"></i> Nom Complet *
                        </label>
                        <input 
                            type="text" 
                            class="form-control <?php echo isset($errors['fullname']) ? 'is-invalid' : ''; ?>" 
                            id="fullname" 
                            name="fullname" 
                            placeholder="Ex: Jean Dupont" 
                            value="<?php echo htmlspecialchars($oldInput['fullname'] ?? ''); ?>"
                            required
                        >
                        <?php if (isset($errors['fullname'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['fullname']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Au moins 2 mots (prénom et nom)</small>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input 
                            type="email" 
                            class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                            id="email" 
                            name="email" 
                            placeholder="exemple@email.com" 
                            value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>"
                            required
                        >
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mot de Passe -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mot de passe *
                        </label>
                        <input 
                            type="password" 
                            class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                            id="password" 
                            name="password" 
                            placeholder="Au moins 8 caractères" 
                            required
                        >
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Minimum 8 caractères avec au moins un chiffre ou symbole</small>
                    </div>
                    
                    <!-- Âge -->
                    <div class="mb-3">
                        <label for="age" class="form-label">
                            <i class="fas fa-birthday-cake"></i> Âge *
                        </label>
                        <input 
                            type="number" 
                            class="form-control <?php echo isset($errors['age']) ? 'is-invalid' : ''; ?>" 
                            id="age" 
                            name="age" 
                            placeholder="Votre âge" 
                            min="18" 
                            max="100" 
                            value="<?php echo htmlspecialchars($oldInput['age'] ?? ''); ?>"
                            required
                        >
                        <?php if (isset($errors['age'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['age']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Vous devez avoir au moins 18 ans</small>
                    </div>
                    
                    <!-- Adresse -->
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Adresse *
                        </label>
                        <input 
                            type="text" 
                            class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                            id="address" 
                            name="address" 
                            placeholder="Ex: 8 rue de la République, Tunis" 
                            value="<?php echo htmlspecialchars($oldInput['address'] ?? ''); ?>"
                            required
                        >
                        <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Format: numéro + rue/avenue/boulevard + nom</small>
                    </div>
                    
                    <!-- Biographie -->
                    <div class="mb-3">
                        <label for="bio" class="form-label">
                            <i class="fas fa-info-circle"></i> Biographie *
                        </label>
                        <textarea 
                            class="form-control <?php echo isset($errors['bio']) ? 'is-invalid' : ''; ?>" 
                            id="bio" 
                            name="bio" 
                            rows="4" 
                            placeholder="Parlez-nous un peu de vous... (minimum 10 mots)"
                            required
                        ><?php echo htmlspecialchars($oldInput['bio'] ?? ''); ?></textarea>
                        <?php if (isset($errors['bio'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['bio']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Décrivez-vous en quelques mots (minimum 10 mots)</small>
                    </div>
                    
                    <!-- Centres d'Intérêt -->
                    <div class="mb-4" id="interestsContainer">
                        <label class="form-label">
                            <i class="fas fa-heart"></i> Centres d'Intérêt *
                        </label>
                        <div class="row">
                            <?php 
                            $interestsList = [
                                'Sports', 'Musique', 'Technologie', 'Arts', 
                                'Voyages', 'Lecture', 'Cinéma', 'Cuisine',
                                'Photographie', 'Danse', 'Sciences', 'Mode',
                                'Jeux Vidéo', 'Nature', 'Bénévolat', 'Entrepreneuriat'
                            ];
                            
                            $selectedInterests = [];
                            if (isset($oldInput['interests'])) {
                                if (is_array($oldInput['interests'])) {
                                    $selectedInterests = $oldInput['interests'];
                                } else {
                                    $selectedInterests = explode(', ', $oldInput['interests']);
                                }
                            }
                            
                            foreach ($interestsList as $interest): 
                                $checked = in_array($interest, $selectedInterests) ? 'checked' : '';
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="interests[]" 
                                        value="<?php echo $interest; ?>" 
                                        id="interest_<?php echo str_replace(' ', '_', $interest); ?>"
                                        <?php echo $checked; ?>
                                    >
                                    <label class="form-check-label" for="interest_<?php echo str_replace(' ', '_', $interest); ?>">
                                        <?php echo $interest; ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($errors['interests'])): ?>
                        <div class="text-danger small mt-2"><?php echo $errors['interests']; ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted d-block mt-2">Sélectionnez au moins un centre d'intérêt</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                    
                    <p class="mt-3 text-center">
                        Déjà un compte ? 
                        <a href="sign-in.php" class="text-success fw-bold">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </section>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/signup.js"></script>
</body>
</html>
