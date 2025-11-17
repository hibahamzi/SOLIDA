<?php
require_once __DIR__ . '/../models/CommentaireModel.php';
class CommentaireController {
    private $model;
    public function __construct() { $this->model = new CommentaireModel(); }
    public function index() {
        $comments = $this->model->getCommentairesByForum(null);
        include __DIR__ . '/../views/commentaire/list.php';
    }
    public function store() {
        $this->model->createCommentaire($_POST['contenu'], $_POST['id_auteur'], $_POST['id_forum'] ?? null);
        header('Location: index.php?action=forum_show&id=' . ($_POST['id_forum'] ?? '')); exit;
    }
}
?>