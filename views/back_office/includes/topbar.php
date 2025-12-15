<!-- Top Bar -->
<div class="top-bar">
    <h1><?php echo $pageTitle ?? 'Tableau de Bord'; ?></h1>
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-details">
            <span>Administrateur</span>
            <small><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'admin@solida.com'); ?></small>
        </div>
    </div>
</div>

