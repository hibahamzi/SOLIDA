<?php
require_once "../config.php";

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion(); // utilise config.php
    }

    // Ajouter user
    public function add($nom, $prenom, $email, $region, $pwd)  {
        $hash = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users(nom, prenom, email, region, pwd) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nom, $prenom, $email, $region, $hash]);
    }

    // Modifier user (sans mot de passe)
    public function update($id, $nom, $prenom, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET nom=?, prenom=?, email=? WHERE id=?");
        return $stmt->execute([$nom, $prenom, $email, $id]);
    }

    // Supprimer user
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    // Lister tous les users
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // Récupérer user par ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Login user
    public function login($email, $pwd) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pwd, $user['pwd'])) {
            return $user;
        }
        return false;
    }
}
?>
