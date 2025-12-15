<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $forums and $error should be set by ForumController::adminIndex()
// If not set, initialize empty array
if (!isset($forums)) {
    $forums = [];
}
if (!isset($error)) {
    $error = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Forums - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- CSS admin -->
    <link rel="stylesheet" href="../back_office/assets/css/admin.css">
</head>
<body>
<?php
// Calculate correct paths for includes
$viewDir = __DIR__; // views/forum
$backOfficeDir = dirname($viewDir) . '/back_office'; // views/back_office
$includesPath = $backOfficeDir . '/includes';

// Ensure paths are correct
if (!file_exists($includesPath . '/sidebar.php')) {
    // Try alternative path
    $includesPath = __DIR__ . '/../back_office/includes';
}
?>
<?php include $includesPath . '/sidebar.php'; ?>
<div class="main-content">
    <?php 
    $pageTitle = 'Gestion des Forums';
    include $includesPath . '/topbar.php'; 
    ?>
        
        <!-- Content Area -->
        <div class="content-area">

            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #2ecc71; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Table des forums -->
            <div class="table-container">
                <div class="table-header">
                    <h2>Liste des forums</h2>

                    <a href="../front_office/index.php?section=forum&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau Sujet
                    </a>
                </div>
                
                <div class="table-responsive">
                    <?php if (!empty($forums)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ID Utilisateur</th>
                                <th>Catégorie</th>
                                <th>Discussion générale</th>
                                <th>Discussion principale</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forums as $forum): ?>
                            <?php
                                // Handle both arrays and ForumModel objects
                                $id_forum = is_array($forum) ? (int)($forum['id_forum'] ?? 0) : (method_exists($forum, 'getIdForum') ? (int)$forum->getIdForum() : 0);
                                $id_user = is_array($forum) ? ($forum['id_user'] ?? null) : (method_exists($forum, 'getIdUser') ? $forum->getIdUser() : null);
                                $categorie = is_array($forum) ? ($forum['categorie'] ?? '') : (method_exists($forum, 'getCategorie') ? $forum->getCategorie() : '');
                                $discussion_g = is_array($forum) ? ($forum['discussion_g'] ?? '') : (method_exists($forum, 'getDiscussionG') ? $forum->getDiscussionG() : '');
                                $discussion_p = is_array($forum) ? ($forum['discussion_p'] ?? '') : (method_exists($forum, 'getDiscussionP') ? $forum->getDiscussionP() : '');
                                $date_creation = is_array($forum) ? ($forum['date_creation'] ?? '') : (method_exists($forum, 'getDateCreation') ? $forum->getDateCreation() : '');
                                
                                // Format date
                                $date_formatted = '';
                                if (!empty($date_creation)) {
                                    try {
                                        $date = new DateTime($date_creation);
                                        $date_formatted = $date->format('d/m/Y H:i');
                                    } catch (Exception $e) {
                                        $date_formatted = htmlspecialchars($date_creation);
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo $id_forum; ?></td>
                                <td><?php echo htmlspecialchars($id_user ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($categorie)); ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars(mb_strlen($discussion_g) > 50 ? mb_substr($discussion_g, 0, 50) . '...' : $discussion_g);
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars(mb_strlen($discussion_p) > 50 ? mb_substr($discussion_p, 0, 50) . '...' : $discussion_p);
                                    ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar" style="margin-right: 5px;"></i>
                                    <?php echo $date_formatted; ?>
                                </td>
                                <td>
                                    <a 
                                        href="../back_office/dashboard.php?section=forum&action=edit&id=<?php echo $id_forum; ?>" 
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Éditer
                                    </a>

                                    <a href="../back_office/dashboard.php?section=forum&action=delete&id=<?php echo $id_forum; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce forum ?');">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: #bcbcbc;">
                            <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                            Aucun forum trouvé
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
    </div>
    
</body>
</html>