<?php
// Calculate base path to back_office directory
// Links in HTML are resolved relative to the current page URL, not the included file location
$currentScript = $_SERVER['PHP_SELF'];
$basePath = '';

// Check if we're in front_office/sponsor/ (special case - sidebar is included from there)
if (strpos($currentScript, '/front_office/sponsor/') !== false) {
    // We're in front_office/sponsor/, need to go up to back_office
    $basePath = '../../back_office/';
}
// Check if we're in a subdirectory of back_office (like deal/)
elseif (preg_match('#/back_office/[^/]+/#', $currentScript)) {
    // We're in a subdirectory (e.g., /back_office/deal/index.php), need to go up 1 level
    $basePath = '../';
} else {
    // We're in back_office root (e.g., /back_office/dashboard.php), no need to go up
    $basePath = '';
}
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SOLIDA</h2>
        <p>Panneau d'Administration</p>
    </div>
    
    <!-- Admin Info in Sidebar -->
    <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                <?php 
                    $adminName = $_SESSION['user_fullname'] ?? 'Admin';
                    $nameParts = explode(' ', $adminName);
                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                    if (count($nameParts) > 1) {
                        $initials .= strtoupper(substr($nameParts[1], 0, 1));
                    }
                    echo $initials;
                ?>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="color: white; font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? 'Administrateur'); ?>
                </div>
                <div style="color: rgba(255,255,255,0.7); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'admin@solida.com'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php 
                // Calculate path to dashboard based on current location
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    // From front_office/sponsor/, go to back_office/dashboard
                    echo '../../back_office/dashboard.php';
                } elseif ($basePath == '../') {
                    // From back_office subdirectory, go up then to dashboard
                    echo '../dashboard.php';
                } else {
                    // From back_office root
                    echo 'dashboard.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de Bord</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/users.php';
                } elseif ($basePath == '../') {
                    echo '../users.php';
                } else {
                    echo 'users.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/evenementback.php';
                } elseif ($basePath == '../') {
                    echo '../evenementback.php';
                } else {
                    echo 'evenementback.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'evenementback.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </a>
        </li>
        
        <li>
            <a href="<?php 
                // Calculate path to deal index based on current location
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    // From front_office/sponsor/, go to back_office/deal
                    echo '../../back_office/deal/index.php';
                } elseif ($basePath == '../') {
                    // From back_office subdirectory, go up then to deal
                    echo '../deal/index.php';
                } else {
                    // From back_office root
                    echo 'deal/index.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'deal') !== false) || (basename($_SERVER['PHP_SELF']) == 'create.php' && strpos($_SERVER['PHP_SELF'], 'deal') !== false) || (basename($_SERVER['PHP_SELF']) == 'edit.php' && strpos($_SERVER['PHP_SELF'], 'deal') !== false) || (basename($_SERVER['PHP_SELF']) == 'show.php' && strpos($_SERVER['PHP_SELF'], 'deal') !== false) || (basename($_SERVER['PHP_SELF']) == 'pending.php' && strpos($_SERVER['PHP_SELF'], 'deal') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i>
                <span>Deals</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                // Calculate path to sponsor index based on current location
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    // Already in sponsor directory, just use index.php
                    echo 'index.php';
                } elseif ($basePath == '../../back_office/') {
                    // From front_office/sponsor/, go to sponsor index
                    echo '../sponsor/index.php';
                } elseif ($basePath == '../') {
                    // From back_office subdirectory, go up then to front_office/sponsor
                    echo '../../front_office/sponsor/index.php';
                } else {
                    // From back_office root
                    echo '../front_office/sponsor/index.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'sponsor') !== false) || (basename($_SERVER['PHP_SELF']) == 'create.php' && strpos($_SERVER['PHP_SELF'], 'sponsor') !== false) || (basename($_SERVER['PHP_SELF']) == 'edit.php' && strpos($_SERVER['PHP_SELF'], 'sponsor') !== false) || (basename($_SERVER['PHP_SELF']) == 'show.php' && strpos($_SERVER['PHP_SELF'], 'sponsor') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>
          
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/BackofficeReclamations.php';
                } elseif ($basePath == '../') {
                    echo '../BackofficeReclamations.php';
                } else {
                    echo 'BackofficeReclamations.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'BackofficeReclamations.php' || (basename($_SERVER['PHP_SELF']) == 'dashboard.php' && isset($_GET['section']) && $_GET['section'] == 'reclamations')) ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-circle"></i>
                <span>Réclamations</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/dashboard.php?section=forum';
                } elseif ($basePath == '../') {
                    echo '../dashboard.php?section=forum';
                } else {
                    echo 'dashboard.php?section=forum';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'indexForum.php' || (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'forum') !== false) || (basename($_SERVER['PHP_SELF']) == 'edit.php' && strpos($_SERVER['PHP_SELF'], 'forum') !== false) || (basename($_SERVER['PHP_SELF']) == 'dashboard.php' && isset($_GET['section']) && $_GET['section'] == 'forum')) ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Forum</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/dashboard.php?section=commentaire';
                } elseif ($basePath == '../') {
                    echo '../dashboard.php?section=commentaire';
                } else {
                    echo 'dashboard.php?section=commentaire';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'indexCommentaire.php' || (basename($_SERVER['PHP_SELF']) == 'edit.php' && strpos($_SERVER['PHP_SELF'], 'commentaire') !== false) || (basename($_SERVER['PHP_SELF']) == 'dashboard.php' && isset($_GET['section']) && $_GET['section'] == 'commentaire')) ? 'active' : ''; ?>">
                <i class="fas fa-comment-dots"></i>
                <span>Commentaires</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/dashboard.php?section=dons';
                } elseif ($basePath == '../') {
                    echo '../dashboard.php?section=dons';
                } else {
                    echo 'dashboard.php?section=dons';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php' && isset($_GET['section']) && $_GET['section'] == 'dons') ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-heart"></i>
                <span>Dons</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/dashboard.php?section=associations';
                } elseif ($basePath == '../') {
                    echo '../dashboard.php?section=associations';
                } else {
                    echo 'dashboard.php?section=associations';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php' && isset($_GET['section']) && $_GET['section'] == 'associations') ? 'active' : ''; ?>">
                <i class="fas fa-handshake"></i>
                <span>Associations</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../../back_office/admin-profile.php';
                } elseif ($basePath == '../') {
                    echo '../admin-profile.php';
                } else {
                    echo 'admin-profile.php';
                }
            ?>" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin-profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Mon Profil</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../index.php';
                } elseif ($basePath == '../') {
                    echo '../../front_office/index.php';
                } else {
                    echo '../front_office/index.php';
                }
            ?>">
                <i class="fas fa-globe"></i>
                <span>Voir le Site</span>
            </a>
        </li>
        <li>
            <a href="<?php 
                if (strpos($currentScript, '/front_office/sponsor/') !== false) {
                    echo '../sign-in.php';
                } elseif ($basePath == '../') {
                    echo '../../front_office/sign-in.php';
                } else {
                    echo '../front_office/sign-in.php';
                }
            ?>" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</aside>

