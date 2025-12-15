<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // DEBUG TEMP (à commenter après test)
    // var_dump('NAVBAR user_id = ', $_SESSION['user_id'] ?? null);
}

/**
 * Charger la config si $pdo n'est pas déjà défini.
 * config/config.php crée bien $pdo.
 */
if (!isset($pdo)) {
    require_once __DIR__ . '/../../../config/config.php';
}

// Par sécurité : vérifier que $pdo est bien une instance de PDO
if (!isset($pdo) || !$pdo instanceof PDO) {
    $pdo = null;
}

// Get user info if logged in
$userId = $_SESSION['user_id'] ?? null;
$user = null;

if ($userId && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // DEBUG TEMP (à commenter après test)
        // var_dump('NAVBAR user row = ', $user);
    } catch (PDOException $e) {
        // Silencieux en cas d'erreur
        $user = null;
        // optionnel : error_log('NAVBAR error: ' . $e->getMessage());
    }
}

// Calculate base path for navigation links based on where navbar is included from
$currentScript = $_SERVER['PHP_SELF'];
$isInPhpDir = (strpos($currentScript, '/php/') !== false);
$basePath = $isInPhpDir ? '../front_office/' : '';
?>
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
        <a class="navbar-brand text-success logo h1 align-self-center" href="<?= $basePath; ?>index.php">
            SOLIDA
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#templatemo_main_nav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="align-self-center collapse navbar-collapse flex-fill d-lg-flex justify-content-lg-between" id="templatemo_main_nav">
            <div class="flex-fill">
                <ul class="nav navbar-nav d-flex flex-wrap flex-lg-nowrap mx-lg-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['section'])) ? 'active' : ''; ?>" href="<?= $basePath; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'evenement.php') ? 'active' : ''; ?>" href="<?= $basePath; ?>evenement.php">Événement</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'sponsors') ? 'active' : ''; ?>" href="<?= $basePath; ?>index.php?section=sponsors">Sponsors</a>
                    </li>
                  
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'listeReclamation.php' || basename($_SERVER['PHP_SELF']) == 'AddReclamation.php' || basename($_SERVER['PHP_SELF']) == 'UpdateReclamation.php' || (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'reclamations')) ? 'active' : ''; ?>" href="<?= $basePath; ?>index.php?section=reclamations"><span class="d-none d-md-inline">Réclamation</span><span class="d-md-none">Réclam.</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'forum') ? 'active' : ''; ?>" href="<?= $basePath; ?>index.php?section=forum">Forum</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'dons') || (basename($_SERVER['PHP_SELF']) == 'step-type.php' || basename($_SERVER['PHP_SELF']) == 'step4.php' || basename($_SERVER['PHP_SELF']) == 'step5.php' || basename($_SERVER['PHP_SELF']) == 'association2.php') ? 'active' : ''; ?>" href="<?= $basePath; ?>index.php?section=dons">Dons</a>
                    </li>
                    <?php if (!$user): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath; ?>sign-in.php">Sign in</a>
                    </li>
                    <?php endif; ?>
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

                <?php if ($user): ?>
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                        <i class="fa fa-fw fa-user text-dark"></i>
                        <span class="text-dark ms-2 d-none d-md-inline"><?php echo htmlspecialchars($user['fullname']); ?></span>
                        <i class="fas fa-chevron-down text-dark ms-1" style="font-size: 12px;"></i>
                    </div>
                    <div class="user-dropdown-menu">
                        <div class="user-dropdown-header">
                            <p><?php echo htmlspecialchars($user['fullname']); ?></p>
                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                        </div>
                        <a href="<?= $basePath; ?>userprofile.php" class="user-dropdown-item">
                            <i class="fas fa-user"></i>
                            Mon Profil
                        </a>
                        <a href="<?= $basePath; ?>participations-history.php" class="user-dropdown-item">
                            <i class="fas fa-calendar-check"></i>
                            Mes Participations
                        </a>
                        <a href="<?= $basePath; ?>index.php?section=reclamations" class="user-dropdown-item">
                            <i class="fas fa-exclamation-circle"></i>
                            Mes Réclamations
                        </a>
                        <a href="<?= $basePath; ?>sign-in.php" class="user-dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Se déconnecter
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a class="nav-icon position-relative text-decoration-none" href="<?= $basePath; ?>sign-in.php">
                    <i class="fa fa-fw fa-user text-dark mr-3"></i>
                    <span class="position-absolute top-0 left-100 translate-middle badge rounded-pill bg-light text-dark"></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<!-- Close Header -->

<!-- Modal -->
<div class="modal fade bg-white" id="templatemo_search" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.classList.toggle('active');
        }
    }
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && !dropdown.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
</script>

<style>
    /* Navbar responsive fixes */
    @media (min-width: 992px) {
        #templatemo_main_nav .navbar-nav {
            flex-wrap: nowrap !important;
            white-space: nowrap;
            justify-content: center;
        }
        
        #templatemo_main_nav .nav-item {
            white-space: nowrap;
            margin: 0 0.15rem;
        }
        
        #templatemo_main_nav .nav-link {
            padding: 0.5rem 0.6rem;
            font-size: 0.9rem;
        }
    }
    
    @media (min-width: 1200px) {
        #templatemo_main_nav .nav-item {
            margin: 0 0.3rem;
        }
        
        #templatemo_main_nav .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
        }
    }
    
    @media (min-width: 1400px) {
        #templatemo_main_nav .nav-item {
            margin: 0 0.5rem;
        }
    }
    
    @media (max-width: 991.98px) {
        #templatemo_main_nav .navbar-nav {
            flex-direction: column;
            width: 100%;
        }
        
        #templatemo_main_nav .nav-item {
            width: 100%;
            margin: 0.25rem 0;
        }
    }
    
    /* User dropdown styles */
    .user-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .user-dropdown-toggle {
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    
    .user-dropdown-toggle:hover {
        background-color: #f8f9fa;
    }
    
    .user-dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 8px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .user-dropdown.active .user-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .user-dropdown-header {
        padding: 15px;
        border-bottom: 1px solid #e9eef5;
    }
    
    .user-dropdown-header p {
        margin: 0;
        color: #212934;
        font-weight: 600;
        font-size: 15px;
    }
    
    .user-dropdown-header small {
        color: #bcbcbc;
        font-size: 13px;
    }
    
    .user-dropdown-item {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #212934;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .user-dropdown-item:hover {
        background-color: #f8f9fa;
        color: #e74c3c;
    }
    
    .user-dropdown-item i {
        width: 20px;
        text-align: center;
    }
</style>