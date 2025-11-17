<?php
require_once "../model/User.php";

$userModel = new User();
$action = $_GET['action'] ?? 'liste';

switch ($action) {

    /* ----------------------------------------------------------
       AJOUT UTILISATEUR (SIGN UP)
    -----------------------------------------------------------*/
    case 'ajout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($_POST['pwd'] !== $_POST['confirm_password']) {
                echo "Les mots de passe ne correspondent pas !";
                exit();
            }

            $userModel->add(
                $_POST['nom'],
                $_POST['prenom'],
                $_POST['email'],
                $_POST['region'],
                $_POST['pwd']
            );

            header("Location: ../view/sign in.html");
            exit();
        }
        break;


    /* ----------------------------------------------------------
       MODIFIER UTILISATEUR
    -----------------------------------------------------------*/
    case 'modifier':
        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel->update($id, $_POST['nom'], $_POST['prenom'], $_POST['email']);
            header("Location: ../view/liste_user.php");
            exit();
        } else {
            $data = $userModel->getById($id);
            include "../view/modifier_user.php";
        }
        break;


    /* ----------------------------------------------------------
       SUPPRIMER UTILISATEUR
    -----------------------------------------------------------*/
    case 'supprimer':
        $id = $_GET['id'];
        $userModel->delete($id);
        header("Location: ../view/liste_user.php");
        exit();


    /* ----------------------------------------------------------
       LOGIN (SIGN IN)
    -----------------------------------------------------------*/
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $user = $userModel->login($_POST['email'], $_POST['pwd']);

            if ($user) {
                session_start();
                $_SESSION['user'] = $user;

                header("Location: ../view/index.html");
                exit();
            } else {
                echo "Email ou mot de passe incorrect !";
            }
        }
        break;


    /* ----------------------------------------------------------
       LISTE UTILISATEURS
    -----------------------------------------------------------*/
    case 'liste':
    default:
        $users = $userModel->getAll();
        include "../view/liste_user.php";
        break;
}
