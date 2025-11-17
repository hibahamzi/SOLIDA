<?php

class ForumController {

    private $forumModel;

    public function __construct() {
        $this->forumModel = new ForumModel();
    }

    public function index() {
        $forums = $this->forumModel->getAllForums();
        include "views/forum/index.php";
    }

    public function create() {
        include "views/forum/create.php";
    }

    public function store() {

        // sécurisation + empêche undefined index
        $categorie    = $_POST['categorie']    ?? null;
        $discussion_g = $_POST['discussion_g'] ?? null;
        $discussion_p = $_POST['discussion_p'] ?? null;

        if (!$categorie || !$discussion_g || !$discussion_p) {
            die("Erreur : Il manque des champs !");
        }

        // user non connecté => NULL (pas 0 sinon FK ERROR)
        $id_user = $_SESSION['user_id'] ?? null;

        $this->forumModel->createForum(
            $id_user,
            $categorie,
            $discussion_g,
            $discussion_p
        );

        header("Location: index.php?controller=ForumController&action=index");
        exit;
    }

    public function edit() {
        $id = $_GET['id'];
        $forum = $this->forumModel->find($id);
        include "views/forum/edit.php";
    }

    public function update() {
        $id           = $_POST['id'];
        $categorie    = $_POST['categorie'];
        $discussion_g = $_POST['discussion_g'];
        $discussion_p = $_POST['discussion_p'];

        $this->forumModel->updateForum($id, $categorie, $discussion_g, $discussion_p);

        header("Location: index.php?controller=ForumController&action=index");
        exit;
    }

    public function delete() {
        $id = $_GET['id'];
        $this->forumModel->deleteForum($id);

        header("Location: index.php?controller=ForumController&action=index");
        exit;
    }
}
