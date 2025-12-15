<?php
// app/views/commentaire/index.php
session_start();

// (Optionnel) sécurité admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: ../front_office/sign-in.php');
//     exit();
// }

// Infos admin (pour l'affichage)
$adminName  = $_SESSION['user_fullname'] ?? 'Admin';
$nameParts  = explode(' ', $adminName);
$initials   = strtoupper(substr($nameParts[0], 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr($nameParts[1], 0, 1));
}
$adminEmail = $_SESSION['user_email'] ?? 'admin@solida.com';

// Comptage simple pour stats rapides
$totalCommentaires = isset($comments) ? count($comments) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires - SOLIDA Admin</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- CSS admin (même que forum, réutilisation du template) -->
    <link rel="stylesheet" href="app/views/forum/assets1/css/admin.css">

    <style>
        /* Styles spécifiques au tableau commentaires (complément à admin.css) */

        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .forum-header h2 {
            margin: 0;
            color: #212934;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .forum-header h2 i {
            color: #59ab6e;
        }
        .forum-header .subtitle {
            color: #bcbcbc;
            font-size: 14px;
            margin-top: 4px;
        }
        .forum-header .btn-new {
            background: #59ab6e;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .forum-header .btn-new:hover {
            background: #4a915b;
        }

        .badge-forum {
            background-color: #e9f7ef;
            color: #28a745;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 999px;
        }
        .text-small {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .table-forum {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table-forum thead tr {
            background-color: #f8f9fa;
        }
        .table-forum thead th {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            color: #555;
        }
        .table-forum tbody tr {
            border-bottom: 1px solid #eee;
        }
        .table-forum tbody tr:hover {
            background-color: #f9fbff;
        }
        .table-forum tbody td {
            padding: 8px 12px;
            vertical-align: top;
        }
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #bcbcbc;
        }
        .no-data i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
            color: #dcdcdc;
        }

        /* Stats bloc */
        .forum-stats {
            margin-top: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .forum-stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .forum-stat-card h3 {
            margin: 0 0 8px;
            font-size: 16px;
            color: #212934;
        }
        .forum-stat-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #59ab6e;
        }
        .forum-stat-card .desc {
            font-size: 13px;
            color: #bcbcbc;
        }
    </style>
</head>
<body>
    
    <!-- Sidebar (copié de forum/index.php, juste lien actif changé) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>SOLIDA</h2>
            <p>Panneau d'Administration</p>
        </div>
        
        <!-- Admin Info in Sidebar -->
        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                    <?php echo $initials; ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="color: white; font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($adminName); ?>
                    </div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($adminEmail); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li>
                <a href="events.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Événements</span>
                </a>
            </li>
            <li>
                <a href="donations.php">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Dons</span>
                </a>
            </li>
            <li>
                <a href="claims.php">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Réclamations</span>
                </a>
            </li>
            <li>
                <a href="index.php?controller=ForumController&action=index">
                    <i class="fas fa-comments"></i>
                    <span>Forum</span>
                </a>
            </li>
            <li>
                <a href="index.php?controller=CommentaireController&action=index" class="active">
                    <i class="fas fa-comment-dots"></i>
                    <span>Commentaires</span>
                </a>
            </li>
            <li>
                <a href="admin-profile.php">
                    <i class="fas fa-user-cog"></i>
                    <span>Mon Profil</span>
                </a>
            </li>
            <li>
                <a href="../front_office/index.php">
                    <i class="fas fa-globe"></i>
                    <span>Voir le Site</span>
                </a>
            </li>
            <li>
                <a href="../front_office/sign-in.php" onclick="return confirm('Voulez-vous vraiment vous déconnecter ?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>Gestion des Commentaires</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span><?php echo htmlspecialchars($adminName); ?></span>
                    <small><?php echo htmlspecialchars($adminEmail); ?></small>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            
            <!-- En-tête Commentaires -->
            <div class="forum-header">
                <div>
                    <h2>
                        <i class="fas fa-comment-dots"></i>
                        Commentaires du Forum
                    </h2>
                    <div class="subtitle">
                        Consultez et modérez les commentaires postés sur les discussions.
                    </div>
                </div>
                <div>
                    <!-- Bouton optionnel, par exemple pour voir le forum front -->
                    <a href="../front_office/index.php" class="btn-new">
                        <i class="fas fa-globe"></i> Voir le Forum
                    </a>
                </div>
            </div>
            
            <!-- Tableau des commentaires -->
            <div class="table-container">
                <div class="table-header" style="display:flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin:0; font-size:18px; color:#212934;">
                        Liste des Commentaires
                    </h2>
                    <span style="color:#bcbcbc; font-size:13px;">
                        Total : <strong><?php echo $totalCommentaires; ?></strong> commentaire(s)
                    </span>
                </div>
                
                <div class="table-responsive">
                    <?php if (empty($comments)): ?>
                        <div class="no-data">
                            <i class="fas fa-inbox"></i>
                            Aucun commentaire trouvé pour le moment.
                        </div>
                    <?php else: ?>
                        <table class="table-forum">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Contenu</th>
                                    <th>Auteur</th>
                                    <th>Discussion (id_forum)</th>
                                    <th>Date commentaire</th>
                                    <th style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comments as $comment): ?>
                                    <?php
                                        // S'adapter à CommentaireModel (getters ou array)
                                        if ($comment instanceof CommentaireModel) {
                                            $id_commentaire  = $comment->getIdCommentaire();
                                            $contenu         = $comment->getContenu();
                                            $date_commentaire= $comment->getDateCommentaire();
                                            $id_auteur       = $comment->getIdAuteur();
                                            $id_forum        = $comment->getIdForum();
                                            $nom_prenom      = $comment->nom_prenom ?? null; // si tu ajoutes plus tard
                                        } else {
                                            $id_commentaire  = $comment['id_commentaire'] ?? null;
                                            $contenu         = $comment['contenu'] ?? '';
                                            $date_commentaire= $comment['date_commentaire'] ?? null;
                                            $id_auteur       = $comment['id_auteur'] ?? null;
                                            $id_forum        = $comment['id_forum'] ?? null;
                                            $nom_prenom      = $comment['nom_prenom'] ?? null;
                                        }

                                        $shortContenu = mb_strlen($contenu) > 80
                                            ? mb_substr($contenu, 0, 77) . '...'
                                            : $contenu;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($id_commentaire); ?></td>
                                        <td class="text-small">
                                            <?php echo nl2br(htmlspecialchars($shortContenu)); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($nom_prenom)): ?>
                                                <?php echo htmlspecialchars($nom_prenom); ?>
                                            <?php else: ?>
                                                <span class="badge-forum">
                                                    ID: <?php echo htmlspecialchars((string)$id_auteur); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($id_forum)): ?>
                                                <span class="badge-forum">
                                                    Forum #<?php echo htmlspecialchars((string)$id_forum); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color:#bcbcbc;">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-small">
                                            <?php
                                            if (!empty($date_commentaire)) {
                                                try {
                                                    $d = new DateTime($date_commentaire);
                                                    echo $d->format('d/m/Y H:i');
                                                } catch (Exception $e) {
                                                    echo htmlspecialchars($date_commentaire);
                                                }
                                            } else {
                                                echo '<span style="color:#bcbcbc;">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <!-- À adapter si tu ajoutes edit/suppr de commentaires -->
                                            <a href="index.php?controller=CommentaireController&action=delete&id=<?php echo urlencode((string)$id_commentaire); ?>"
                                               class="btn-sm btn-danger"
                                               onclick="return confirm('Voulez-vous vraiment supprimer ce commentaire ?');">
                                                <i class="fas fa-trash-alt"></i> Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistiques Commentaires -->
            <div class="forum-stats">
                <div class="forum-stat-card">
                    <h3><i class="fas fa-comment-dots" style="color:#59ab6e; margin-right:6px;"></i> Commentaires</h3>
                    <div class="value"><?php echo $totalCommentaires; ?></div>
                    <div class="desc">Nombre total de commentaires postés sur le forum.</div>
                </div>
                <div class="forum-stat-card">
                    <h3><i class="fas fa-info-circle" style="color:#3498db; margin-right:6px;"></i> Info</h3>
                    <div class="desc">
                        Utilisez cette page pour modérer les commentaires : supprimer les contenus inappropriés.
                    </div>
                </div>
            </div>

        </div><!-- /.content-area -->
        
    </div><!-- /.main-content -->
    
</body>
</html>