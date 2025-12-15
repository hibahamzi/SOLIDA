<?php
require_once __DIR__ . '/../models/ForumModel.php';

class ForumController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Page d'accueil publique du forum - Front Office
     */
    public function index(): void
    {
        // Get forums and categories
        $categorie = $_GET['categorie'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';

        $sql = "SELECT f.*, u.fullname as auteur_nom,
                       (SELECT COUNT(*) FROM commentaire WHERE id_forum = f.id_forum) as nb_commentaires
                FROM forum f
                LEFT JOIN users u ON f.id_user = u.id
                WHERE 1=1";

        $params = [];
        if ($categorie && $categorie !== 'all') {
            $sql .= " AND f.categorie = :categorie";
            $params[':categorie'] = $categorie;
        }

        if ($sort === 'date_asc') {
            $sql .= " ORDER BY f.date_creation ASC";
        } elseif ($sort === 'likes_desc') {
            $sql .= " ORDER BY f.likes DESC, f.date_creation DESC";
        } else {
            $sql .= " ORDER BY f.date_creation DESC";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all comments grouped by forum
        $commentsByForum = [];
        if (!empty($forums)) {
            $forumIds = array_map(function($f) { return (int)$f['id_forum']; }, $forums);
            $placeholders = implode(',', array_fill(0, count($forumIds), '?'));
            $sqlComments = "SELECT c.*, u.fullname as auteur_nom 
                           FROM commentaire c
                           LEFT JOIN users u ON c.id_auteur = u.id
                           WHERE c.id_forum IN ($placeholders)
                           ORDER BY c.date_commentaire ASC";
            $stmtComments = $this->pdo->prepare($sqlComments);
            $stmtComments->execute($forumIds);
            $allComments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
            
            // Group comments by forum
            foreach ($allComments as $c) {
                $fid = (int)$c['id_forum'];
                if (!isset($commentsByForum[$fid])) {
                    $commentsByForum[$fid] = [];
                }
                $commentsByForum[$fid][] = $c;
            }
        }

        // Get categories
        $sqlCategories = "SELECT DISTINCT categorie FROM forum WHERE categorie != '' ORDER BY categorie";
        $stmtCategories = $this->pdo->query($sqlCategories);
        $categories = $stmtCategories->fetchAll(PDO::FETCH_COLUMN);

        // Check if accessed from front office index.php or directly
        $isFrontOfficeRequest = isset($_GET['section']) && $_GET['section'] === 'forum';
        $currentPath = $_SERVER['PHP_SELF'];

        if ($isFrontOfficeRequest || (strpos($currentPath, '/front_office/') !== false && strpos($currentPath, 'index.php') !== false)) {
            // Front office request via index.php - set variables in $GLOBALS for index.php
            $GLOBALS['forums'] = $forums;
            $GLOBALS['categories'] = $categories;
            $GLOBALS['selectedCategorie'] = $categorie;
            $GLOBALS['selectedSort'] = $sort;
            $GLOBALS['commentsByForum'] = $commentsByForum;
            return; // Let index.php continue and display
        } else {
            // Direct access or other context - include view directly
            require __DIR__ . '/../views/forum/index.php';
        }
    }

    /**
     * Affichage du formulaire de création d'un sujet
     */
    public function create(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if accessed from front office index.php or directly
        $isFrontOfficeRequest = isset($_GET['section']) && $_GET['section'] === 'forum';
        $currentPath = $_SERVER['PHP_SELF'];

        if ($isFrontOfficeRequest) {
            // Front office request via index.php - set variables in $GLOBALS
            $GLOBALS['old'] = [];
            $GLOBALS['fieldErrors'] = [];
            return; // Let index.php continue and display the form
        } else {
            // Direct access - include view directly
            require __DIR__ . '/../views/forum/create.php';
        }
    }

    /**
     * Enregistrement d'un nouveau sujet
     */
    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToIndex();
            return;
        }

        $categorie = isset($_POST['categorie']) ? trim($_POST['categorie']) : '';
        $discussion_g = isset($_POST['discussion_g']) ? trim($_POST['discussion_g']) : '';
        $discussion_p = isset($_POST['discussion_p']) ? trim($_POST['discussion_p']) : '';
        $id_user = $_SESSION['user_id'] ?? null;

        // Validation
        if ($categorie === '' || mb_strlen($discussion_g) < 10 || mb_strlen($discussion_p) < 20) {
            $_SESSION['error'] = "Veuillez remplir correctement tous les champs obligatoires.";
            $this->redirectToIndex();
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO forum (categorie, discussion_g, discussion_p, date_creation, likes, id_user)
                VALUES (:categorie, :discussion_g, :discussion_p, NOW(), 0, :id_user)
            ");

            $stmt->execute([
                ':categorie' => $categorie,
                ':discussion_g' => $discussion_g,
                ':discussion_p' => $discussion_p,
                ':id_user' => $id_user,
            ]);

            $_SESSION['success'] = "Sujet créé avec succès !";
            $this->redirectToIndex();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            $this->redirectToIndex();
        }
    }

    /**
     * Liste des forums dans le dashboard admin
     */
    public function adminIndex(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $stmt = $this->pdo->query("
                SELECT f.*, u.fullname as auteur_nom,
                       (SELECT COUNT(*) FROM commentaire WHERE id_forum = f.id_forum) as nb_commentaires
                FROM forum f
                LEFT JOIN users u ON f.id_user = u.id
                ORDER BY f.date_creation DESC
            ");
            $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $forums = [];
            $error = 'Erreur de connexion à la base de données: ' . $e->getMessage();
        }

        require __DIR__ . '/../views/forum/indexForum.php';
    }

    /**
     * Affichage du formulaire d'édition (côté admin)
     */
    public function edit(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
            $_SESSION['error'] = "ID du forum invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        $id_forum = (int) $_GET['id'];

        try {
            $stmt = $this->pdo->prepare("
                SELECT id_forum, id_user, categorie, discussion_g, discussion_p, date_creation, likes
                FROM forum
                WHERE id_forum = :id_forum
            ");
            $stmt->execute([':id_forum' => $id_forum]);
            $forum = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$forum) {
                $_SESSION['error'] = "Forum introuvable.";
                $this->redirectToAdminIndex();
                return;
            }

            // Set $old for the view
            $old = [
                'id_forum' => $forum['id_forum'],
                'categorie' => $forum['categorie'],
                'discussion_g' => $forum['discussion_g'],
                'discussion_p' => $forum['discussion_p'],
                'date_creation' => $forum['date_creation'],
            ];
            $fieldErrors = [];

            require __DIR__ . '/../views/forum/edit.php';

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            $this->redirectToAdminIndex();
        }
    }

    /**
     * Traitement du formulaire d'édition (update)
     */
    public function update(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToAdminIndex();
            return;
        }

        if (!isset($_POST['id_forum']) || !ctype_digit($_POST['id_forum'])) {
            $_SESSION['error'] = "ID du forum invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        $id_forum = (int) $_POST['id_forum'];
        $categorie = isset($_POST['categorie']) ? trim($_POST['categorie']) : '';
        $discussion_g = isset($_POST['discussion_g']) ? trim($_POST['discussion_g']) : '';
        $discussion_p = isset($_POST['discussion_p']) ? trim($_POST['discussion_p']) : '';

        if ($categorie === '' || mb_strlen($discussion_g) < 10 || mb_strlen($discussion_p) < 20) {
            $_SESSION['error'] = "Veuillez remplir correctement tous les champs obligatoires.";
            header('Location: ../../back_office/dashboard.php?section=forum&action=edit&id=' . $id_forum);
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE forum
                SET categorie = :categorie,
                    discussion_g = :discussion_g,
                    discussion_p = :discussion_p
                WHERE id_forum = :id_forum
            ");

            $stmt->execute([
                ':categorie' => $categorie,
                ':discussion_g' => $discussion_g,
                ':discussion_p' => $discussion_p,
                ':id_forum' => $id_forum,
            ]);

            $_SESSION['success'] = "Sujet mis à jour avec succès.";
            $this->redirectToAdminIndex();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            header('Location: ../../back_office/dashboard.php?section=forum&action=edit&id=' . $id_forum);
            exit;
        }
    }

    /**
     * Suppression d'un forum (côté admin)
     */
    public function delete(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
            $_SESSION['error'] = "ID du forum invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        $id_forum = (int) $_GET['id'];

        try {
            $stmt = $this->pdo->prepare("DELETE FROM forum WHERE id_forum = :id_forum");
            $stmt->execute([':id_forum' => $id_forum]);

            $_SESSION['success'] = "Sujet supprimé avec succès.";
            $this->redirectToAdminIndex();

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            $this->redirectToAdminIndex();
        }
    }

    /**
     * Action LIKE (incrémente le nombre de likes d'un sujet)
     */
    public function like(): void
    {
        if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
            $this->redirectToIndex();
            return;
        }

        $id_forum = (int) $_GET['id'];

        try {
            $stmt = $this->pdo->prepare("UPDATE forum SET likes = COALESCE(likes, 0) + 1 WHERE id_forum = :id_forum");
            $stmt->execute([':id_forum' => $id_forum]);
        } catch (PDOException $e) {
            // Silently fail
        }

        $this->redirectToIndex();
    }

    /**
     * Show a forum topic with comments
     */
    public function show(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
            $_SESSION['error'] = "ID du forum invalide.";
            $this->redirectToIndex();
            return;
        }

        $id_forum = (int) $_GET['id'];

        try {
            // Get forum
            $stmt = $this->pdo->prepare("
                SELECT f.*, u.fullname as auteur_nom
                FROM forum f
                LEFT JOIN users u ON f.id_user = u.id
                WHERE f.id_forum = :id_forum
            ");
            $stmt->execute([':id_forum' => $id_forum]);
            $forum = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$forum) {
                $_SESSION['error'] = "Forum introuvable.";
                $this->redirectToIndex();
                return;
            }

            // Get comments
            $stmt = $this->pdo->prepare("
                SELECT c.*, u.fullname as auteur_nom
                FROM commentaire c
                LEFT JOIN users u ON c.id_auteur = u.id
                WHERE c.id_forum = :id_forum
                ORDER BY c.date_commentaire ASC
            ");
            $stmt->execute([':id_forum' => $id_forum]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if accessed from front office index.php or directly
            $isFrontOfficeRequest = isset($_GET['section']) && $_GET['section'] === 'forum';
            $currentPath = $_SERVER['PHP_SELF'];

            if ($isFrontOfficeRequest || (strpos($currentPath, '/front_office/') !== false && strpos($currentPath, 'index.php') !== false)) {
                // Front office request via index.php - set variables in $GLOBALS
                $GLOBALS['forum'] = $forum;
                $GLOBALS['comments'] = $comments;
                return; // Let index.php continue and display
            } else {
                // Direct access - include view directly
                require __DIR__ . '/../views/forum/show.php';
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            $this->redirectToIndex();
        }
    }

    /**
     * Redirect to front office forum index
     */
    private function redirectToIndex(): void
    {
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/front_office/') !== false || (strpos($currentPath, 'index.php') !== false && isset($_GET['section']))) {
            header('Location: index.php?section=forum');
        } else {
            header('Location: ../front_office/index.php?section=forum');
        }
        exit;
    }

    /**
     * Redirect to admin forum index
     */
    private function redirectToAdminIndex(): void
    {
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/back_office/') !== false) {
            header('Location: dashboard.php?section=forum');
        } else {
            header('Location: ../back_office/dashboard.php?section=forum');
        }
        exit;
    }
}
