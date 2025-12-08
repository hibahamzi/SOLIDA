<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

class userController {

    /* ================================
       GET ALL USERS
    ================================= */
    public function getAllUsers() {
        global $pdo;
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        try {
            $query = $pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /* ================================
       GET ONE USER BY ID
    ================================= */
    public function getUserById($id) {
        global $pdo;
        $sql = "SELECT * FROM users WHERE id = :id";
        try {
            $query = $pdo->prepare($sql);
            $query->bindValue(":id", $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur lors de la récupération : " . $e->getMessage());
        }
    }

    /* ================================
       GET USER BY EMAIL
    ================================= */
    public function getUserByEmail($email) {
        global $pdo;
        $sql = "SELECT * FROM users WHERE email = :email";
        try {
            $query = $pdo->prepare($sql);
            $query->bindValue(":email", strtolower(trim($email)), PDO::PARAM_STR);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur lors de la récupération : " . $e->getMessage());
        }
    }

    /* ================================
       CHECK IF EMAIL EXISTS
    ================================= */
    public function emailExists($email, $excludeId = null) {
        global $pdo;
        try {
            if ($excludeId) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                $stmt->execute([strtolower(trim($email)), $excludeId]);
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([strtolower(trim($email))]);
            }
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'email: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       AUTHENTICATE USER
    ================================= */
    public function authenticate($email, $password) {
        $user = $this->getUserByEmail(strtolower(trim($email)));
        
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is banned
            if (isset($user['is_banned']) && $user['is_banned'] == 1) {
                return 'banned';
            }
            return $user;
        }
        
        return false;
    }

    /* ================================
       CREATE USER (SIGNUP)
    ================================= */
    public function createUser($userData) {
        global $pdo;
        try {
            // Vérifier si l'email existe déjà
            if ($this->emailExists($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé.'
                ];
            }
            
            // Hash du mot de passe
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            
            // Préparer la requête d'insertion
            $stmt = $pdo->prepare("
                INSERT INTO users (fullname, email, password, age, address, bio, interests, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userData['fullname'],
                $userData['email'],
                $hashedPassword,
                $userData['age'],
                $userData['address'],
                $userData['bio'],
                $userData['interests'],
                $userData['role'] ?? 'user'
            ]);
            
            if ($result) {
                $userId = $pdo->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Compte créé avec succès!',
                    'user_id' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création du compte.'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de base de données: ' . $e->getMessage()
            ];
        }
    }

    /* ================================
       UPDATE USER
    ================================= */
    public function updateUser($userId, $data) {
        global $pdo;
        try {
            // Si un nouveau mot de passe est fourni, l'inclure dans la mise à jour
            if (!empty($data['password'])) {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET fullname = ?, email = ?, password = ?, role = ?, age = ?, address = ?, bio = ?, interests = ?
                    WHERE id = ?
                ");
                
                return $stmt->execute([
                    $data['fullname'],
                    $data['email'],
                    $data['password'],
                    $data['role'] ?? 'user',
                    $data['age'],
                    $data['address'],
                    $data['bio'],
                    $data['interests'],
                    $userId
                ]);
            } else {
                // Mise à jour sans changer le mot de passe
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET fullname = ?, email = ?, role = ?, age = ?, address = ?, bio = ?, interests = ?
                    WHERE id = ?
                ");
                
                return $stmt->execute([
                    $data['fullname'],
                    $data['email'],
                    $data['role'] ?? 'user',
                    $data['age'],
                    $data['address'],
                    $data['bio'],
                    $data['interests'],
                    $userId
                ]);
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       UPDATE ADMIN PROFILE
    ================================= */
    public function updateAdminProfile($adminId, $data) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET fullname = ?, email = ?, age = ?, address = ?, bio = ?, interests = ?
                WHERE id = ? AND role = 'admin'
            ");
            
            return $stmt->execute([
                $data['fullname'],
                $data['email'],
                $data['age'],
                $data['address'],
                $data['bio'],
                $data['interests'],
                $adminId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'admin: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       DELETE USER
    ================================= */
    public function deleteUser($userId) {
        global $pdo;
        $sql = "DELETE FROM users WHERE id = :id";
        try {
            $query = $pdo->prepare($sql);
            $query->execute(["id" => $userId]);
            return true;
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /* ================================
       TOGGLE USER ROLE
    ================================= */
    public function toggleUserRole($userId) {
        global $pdo;
        try {
            // Get current role
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            // Toggle role
            $newRole = ($user['role'] === 'user') ? 'admin' : 'user';
            
            // Update role
            $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $updateStmt->execute([$newRole, $userId]);
        } catch (PDOException $e) {
            error_log("Erreur lors du changement de rôle: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       TOGGLE USER BAN STATUS
    ================================= */
    public function toggleUserBanStatus($userId) {
        global $pdo;
        try {
            // Get current ban status
            $stmt = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user === false) {
                return false;
            }
            
            // Toggle ban status (0 = unbanned, 1 = banned)
            $newBanStatus = ($user['is_banned'] == 0) ? 1 : 0;
            
            // Update ban status
            $updateStmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
            return $updateStmt->execute([$newBanStatus, $userId]);
        } catch (PDOException $e) {
            error_log("Erreur lors du changement du statut de ban: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       SEND VERIFICATION EMAIL
    ================================= */
    private function sendVerificationEmail($email, $code) {
        require_once __DIR__ . '/../views/front_office/assets/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'khaireddinebacha0@gmail.com';
            $mail->Password = 'rhep oram nyip wojt';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('khaireddinebacha0@gmail.com', 'SOLIDA');
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Code de verification';
            $mail->Body = "
                <p>Code de verification: <strong>$code</strong></p>
            ";
            $mail->AltBody = "Code de verification: $code";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erreur envoi email: " . $mail->ErrorInfo);
            return false;
        }
    }

    /* ================================
       VALIDATION FUNCTIONS
    ================================= */
    private function validateFullname($fullname) {
        $fullname = trim($fullname);
        
        if (empty($fullname)) {
            return "Le nom complet est obligatoire.";
        }
        
        $words = preg_split('/\s+/', $fullname);
        if (count($words) < 2) {
            return "Le nom complet doit contenir au moins 2 mots (prénom et nom).";
        }
        
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $fullname)) {
            return "Le nom ne peut contenir que des lettres et des espaces.";
        }
        
        return true;
    }

    private function validateEmail($email, $excludeId = null) {
        $email = trim($email);
        
        if (empty($email)) {
            return "L'email est obligatoire.";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format d'email invalide.";
        }
        
        if (strpos($email, '@') === false) {
            return "L'email doit contenir le symbole @.";
        }
        
        if ($this->emailExists($email, $excludeId)) {
            return "Cet email est déjà utilisé.";
        }
        
        return true;
    }

    private function validatePassword($password) {
        if (empty($password)) {
            return "Le mot de passe est obligatoire.";
        }
        
        if (strlen($password) < 8) {
            return "Le mot de passe doit contenir au moins 8 caractères.";
        }
        
        if (!preg_match('/[0-9]/', $password) && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            return "Le mot de passe doit contenir au moins un chiffre ou un symbole.";
        }
        
        return true;
    }

    private function validateAge($age) {
        if (empty($age)) {
            return "L'âge est obligatoire.";
        }
        
        if (!is_numeric($age)) {
            return "L'âge doit être un nombre.";
        }
        
        $age = intval($age);
        
        if ($age < 18) {
            return "Vous devez avoir au moins 18 ans.";
        }
        
        if ($age > 100) {
            return "Veuillez entrer un âge valide.";
        }
        
        return true;
    }

    private function validateAddress($address) {
        $address = trim($address);
        
        if (empty($address)) {
            return "L'adresse est obligatoire.";
        }
        
        if (!preg_match('/^\d+\s+(rue|avenue|boulevard|av|bd|impasse|chemin|allée|place)/i', $address)) {
            return "L'adresse doit commencer par un numéro suivi de 'rue', 'avenue' ou 'boulevard' (ex: 8 rue de la République).";
        }
        
        if (strlen($address) < 10) {
            return "L'adresse doit être plus détaillée.";
        }
        
        return true;
    }

    private function validateBio($bio) {
        $bio = trim($bio);
        
        if (empty($bio)) {
            return "La biographie est obligatoire.";
        }
        
        $words = str_word_count($bio);
        
        if ($words < 10) {
            return "La biographie doit contenir au moins 10 mots.";
        }
        
        return true;
    }

    private function validateInterests($interests) {
        if (empty($interests)) {
            return "Veuillez sélectionner au moins un centre d'intérêt.";
        }
        
        return true;
    }

    /* ================================
       SIGNUP HANDLER
    ================================= */
    public function handleSignup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Méthode non autorisée.'
            ];
        }
        
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $age = $_POST['age'] ?? '';
        $address = $_POST['address'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $interests = $_POST['interests'] ?? '';
        
        if (is_array($interests)) {
            $interests = implode(', ', $interests);
        }
        
        $errors = [];
        
        $fullnameResult = $this->validateFullname($fullname);
        if ($fullnameResult !== true) $errors['fullname'] = $fullnameResult;
        
        $emailResult = $this->validateEmail($email);
        if ($emailResult !== true) $errors['email'] = $emailResult;
        
        $passwordResult = $this->validatePassword($password);
        if ($passwordResult !== true) $errors['password'] = $passwordResult;
        
        $ageResult = $this->validateAge($age);
        if ($ageResult !== true) $errors['age'] = $ageResult;
        
        $addressResult = $this->validateAddress($address);
        if ($addressResult !== true) $errors['address'] = $addressResult;
        
        $bioResult = $this->validateBio($bio);
        if ($bioResult !== true) $errors['bio'] = $bioResult;
        
        $interestsResult = $this->validateInterests($interests);
        if ($interestsResult !== true) $errors['interests'] = $interestsResult;
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Erreurs de validation.',
                'errors' => $errors
            ];
        }
        
        $userData = [
            'fullname' => trim($fullname),
            'email' => trim(strtolower($email)),
            'password' => $password,
            'age' => intval($age),
            'address' => trim($address),
            'bio' => trim($bio),
            'interests' => trim($interests)
        ];
        
        $result = $this->createUser($userData);
        
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_fullname'] = $userData['fullname'];
            $_SESSION['user_role'] = 'user';
        }
        
        return $result;
    }

    /* ================================
       SIGNIN HANDLER
    ================================= */
    public function handleSignin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide.";
        }
        
        if (empty($password)) {
            $errors['password'] = "Le mot de passe est obligatoire.";
        }
        
        if (!empty($errors)) {
            $_SESSION['signin_errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        $user = $this->authenticate($email, $password);
        
        if ($user === 'banned') {
            $_SESSION['signin_message'] = "Votre compte a été suspendu. Contactez l'administrateur.";
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/sign-in.php');
            exit();
        } elseif ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_fullname'] = $user['fullname'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: ../views/back_office/dashboard.php');
            } else {
                header('Location: ../views/front_office/userprofile.php');
            }
            exit();
        } else {
            $_SESSION['signin_message'] = "Email ou mot de passe incorrect.";
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
    }

    /* ================================
       UPDATE PROFILE HANDLER
    ================================= */
    public function handleUpdateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Méthode non autorisée.'
            ];
        }
        
        $userId = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? null);
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $age = $_POST['age'] ?? '';
        $address = $_POST['address'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $interests = $_POST['interests'] ?? '';
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Utilisateur non identifié.'
            ];
        }
        
        if (is_array($interests)) {
            $interests = implode(', ', $interests);
        }
        
        $errors = [];
        
        $fullnameResult = $this->validateFullname($fullname);
        if ($fullnameResult !== true) $errors['fullname'] = $fullnameResult;
        
        $emailResult = $this->validateEmail($email, $userId);
        if ($emailResult !== true) $errors['email'] = $emailResult;
        
        $ageResult = $this->validateAge($age);
        if ($ageResult !== true) $errors['age'] = $ageResult;
        
        $addressResult = $this->validateAddress($address);
        if ($addressResult !== true) $errors['address'] = $addressResult;
        
        $bioResult = $this->validateBio($bio);
        if ($bioResult !== true) $errors['bio'] = $bioResult;
        
        $interestsResult = $this->validateInterests($interests);
        if ($interestsResult !== true) $errors['interests'] = $interestsResult;
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Erreurs de validation.',
                'errors' => $errors
            ];
        }
        
        try {
            global $pdo;
            $stmt = $pdo->prepare("
                UPDATE users 
                SET fullname = ?, email = ?, age = ?, address = ?, bio = ?, interests = ?
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                trim($fullname),
                trim(strtolower($email)),
                intval($age),
                trim($address),
                trim($bio),
                trim($interests),
                $userId
            ]);
            
            if ($result) {
                $_SESSION['user_email'] = trim(strtolower($email));
                $_SESSION['user_fullname'] = trim($fullname);
                
                return [
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du profil.'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de base de données: ' . $e->getMessage()
            ];
        }
    }

    /* ================================
       DELETE PROFILE HANDLER
    ================================= */
    public function handleDeleteProfile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
            header('Location: ../views/front_office/userprofile.php');
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            $result = $this->deleteUser($userId);
            
            if ($result) {
                session_destroy();
                header('Location: ../views/front_office/index.php?deleted=1');
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression du profil.";
                header('Location: ../views/front_office/userprofile.php');
                exit();
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
            header('Location: ../views/front_office/userprofile.php');
            exit();
        }
    }

    /* ================================
       UPDATE ADMIN PROFILE HANDLER
    ================================= */
    public function handleUpdateAdminProfile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/back_office/admin-profile.php');
            exit();
        }
        
        $adminId = $_SESSION['user_id'];
        
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
        $address = trim($_POST['address'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $interests = isset($_POST['interests']) && is_array($_POST['interests']) 
            ? implode(', ', $_POST['interests']) 
            : '';
        
        $errors = [];
        
        $fullnameResult = $this->validateFullname($fullname);
        if ($fullnameResult !== true) $errors['fullname'] = $fullnameResult;
        
        $emailResult = $this->validateEmail($email, $adminId);
        if ($emailResult !== true) $errors['email'] = $emailResult;
        
        if ($age !== null && $age < 18) {
            $errors['age'] = "Vous devez avoir au moins 18 ans.";
        }
        
        if (!empty($address)) {
            $addressResult = $this->validateAddress($address);
            if ($addressResult !== true) $errors['address'] = $addressResult;
        }
        
        if (!empty($bio)) {
            $wordCount = str_word_count($bio);
            if ($wordCount < 10) {
                $errors['bio'] = "La biographie doit contenir au moins 10 mots.";
            }
        }
        
        if (empty($interests)) {
            $errors['interests'] = "Veuillez sélectionner au moins un centre d'intérêt.";
        }
        
        if (!empty($errors)) {
            $_SESSION['update_errors'] = $errors;
            $_SESSION['update_message'] = "Veuillez corriger les erreurs dans le formulaire.";
            header('Location: ../views/back_office/admin-profile.php');
            exit();
        }
        
        $data = [
            'fullname' => $fullname,
            'email' => $email,
            'age' => $age,
            'address' => $address,
            'bio' => $bio,
            'interests' => $interests
        ];
        
        if ($this->updateAdminProfile($adminId, $data)) {
            $_SESSION['user_fullname'] = $fullname;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success_message'] = "Profil administrateur mis à jour avec succès!";
            header('Location: ../views/back_office/admin-profile.php?updated=1');
            exit();
        } else {
            $_SESSION['update_message'] = "Erreur lors de la mise à jour du profil.";
            header('Location: ../views/back_office/admin-profile.php');
            exit();
        }
    }

    /* ================================
       USERS BACK OFFICE HANDLERS
    ================================= */
    public function handleUsersBack() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        // CREATE USER
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
            $address = trim($_POST['address'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $interests = isset($_POST['interests']) && is_array($_POST['interests']) 
                ? implode(', ', $_POST['interests']) 
                : '';
            
            $errors = [];
            
            $fullnameResult = $this->validateFullname($fullname);
            if ($fullnameResult !== true) $errors['fullname'] = $fullnameResult;
            
            $emailResult = $this->validateEmail($email);
            if ($emailResult !== true) $errors['email'] = $emailResult;
            
            $passwordResult = $this->validatePassword($password);
            if ($passwordResult !== true) $errors['password'] = $passwordResult;
            
            if ($age !== null && $age < 18) {
                $errors['age'] = "L'âge doit être d'au moins 18 ans.";
            }
            
            if (!empty($address)) {
                $addressResult = $this->validateAddress($address);
                if ($addressResult !== true) $errors['address'] = $addressResult;
            }
            
            if (!empty($bio)) {
                $bioResult = $this->validateBio($bio);
                if ($bioResult !== true) $errors['bio'] = $bioResult;
            }
            
            if (empty($interests)) {
                $errors['interests'] = "Veuillez sélectionner au moins un centre d'intérêt.";
            }
            
            if (!empty($errors)) {
                $_SESSION['users_errors'] = $errors;
                $_SESSION['users_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/back_office/users.php?modal=add');
                exit();
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $data = [
                'fullname' => $fullname,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'age' => $age,
                'address' => $address,
                'bio' => $bio,
                'interests' => $interests
            ];
            
            if ($this->createUser($data)) {
                $_SESSION['users_success'] = "Utilisateur créé avec succès!";
                header('Location: ../views/back_office/users.php');
                exit();
            } else {
                $_SESSION['users_message'] = "Erreur lors de la création de l'utilisateur.";
                header('Location: ../views/back_office/users.php?modal=add');
                exit();
            }
        }
        
        // UPDATE USER
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = intval($_POST['user_id'] ?? 0);
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $age = !empty($_POST['age']) ? intval($_POST['age']) : null;
            $address = trim($_POST['address'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $interests = isset($_POST['interests']) && is_array($_POST['interests']) 
                ? implode(', ', $_POST['interests']) 
                : '';
            
            $errors = [];
            
            $fullnameResult = $this->validateFullname($fullname);
            if ($fullnameResult !== true) $errors['fullname'] = $fullnameResult;
            
            $emailResult = $this->validateEmail($email, $userId);
            if ($emailResult !== true) $errors['email'] = $emailResult;
            
            if (!empty($password)) {
                $passwordResult = $this->validatePassword($password);
                if ($passwordResult !== true) $errors['password'] = $passwordResult;
            }
            
            if ($age !== null && $age < 18) {
                $errors['age'] = "L'âge doit être d'au moins 18 ans.";
            }
            
            if (!empty($address)) {
                $addressResult = $this->validateAddress($address);
                if ($addressResult !== true) $errors['address'] = $addressResult;
            }
            
            if (!empty($bio)) {
                $bioResult = $this->validateBio($bio);
                if ($bioResult !== true) $errors['bio'] = $bioResult;
            }
            
            if (empty($interests)) {
                $errors['interests'] = "Veuillez sélectionner au moins un centre d'intérêt.";
            }
            
            if (!empty($errors)) {
                $_SESSION['users_errors'] = $errors;
                $_SESSION['users_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/back_office/users.php?modal=edit&id=' . $userId);
                exit();
            }
            
            $data = [
                'fullname' => $fullname,
                'email' => $email,
                'role' => $role,
                'age' => $age,
                'address' => $address,
                'bio' => $bio,
                'interests' => $interests
            ];
            
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            if ($this->updateUser($userId, $data)) {
                $_SESSION['users_success'] = "Utilisateur mis à jour avec succès!";
                header('Location: ../views/back_office/users.php');
                exit();
            } else {
                $_SESSION['users_message'] = "Erreur lors de la mise à jour de l'utilisateur.";
                header('Location: ../views/back_office/users.php?modal=edit&id=' . $userId);
                exit();
            }
        }
        
        // DELETE USER
        if ($action === 'delete' && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            
            if ($this->deleteUser($userId)) {
                $_SESSION['users_success'] = "Utilisateur supprimé avec succès!";
            } else {
                $_SESSION['users_message'] = "Erreur lors de la suppression de l'utilisateur.";
            }
            
            header('Location: ../views/back_office/users.php');
            exit();
        }
        
        // TOGGLE ROLE
        if ($action === 'toggle_role' && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            
            if ($this->toggleUserRole($userId)) {
                $_SESSION['users_success'] = "Rôle de l'utilisateur modifié avec succès!";
            } else {
                $_SESSION['users_message'] = "Erreur lors du changement de rôle.";
            }
            
            header('Location: ../views/back_office/users.php');
            exit();
        }
        
        // TOGGLE BAN STATUS
        if ($action === 'toggle_ban' && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            
            if ($this->toggleUserBanStatus($userId)) {
                $_SESSION['users_success'] = "Statut de ban de l'utilisateur modifié avec succès!";
            } else {
                $_SESSION['users_message'] = "Erreur lors du changement du statut de ban.";
            }
            
            header('Location: ../views/back_office/users.php');
            exit();
        }
        
        header('Location: ../views/back_office/users.php');
        exit();
    }

    /* ================================
       HANDLE FORGOT PASSWORD
    ================================= */
    public function handleForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        $email = trim($_POST['email'] ?? '');
        
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Format d'email invalide.";
        } elseif (strpos($email, '@') === false) {
            $errors['email'] = "L'email doit contenir le symbole @.";
        }
        
        if (!empty($errors)) {
            $_SESSION['forgot_errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        // Check if email exists
        $user = $this->getUserByEmail($email);
        if (!$user) {
            $_SESSION['forgot_message'] = "Aucun compte trouvé avec cette adresse email.";
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        // Check if user is banned
        if (isset($user['is_banned']) && $user['is_banned'] == 1) {
            $_SESSION['forgot_message'] = "Votre compte est suspendu. Contactez l'administrateur.";
            $_SESSION['old_input'] = ['email' => $email];
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        // Generate verification code
        $verificationCode = rand(100000, 999999);
        
        // Store in session
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_code'] = $verificationCode;
        $_SESSION['reset_step'] = 'code';
        
        // Send email
        if ($this->sendVerificationEmail($email, $verificationCode)) {
            header('Location: ../views/front_office/reset-password.php');
            exit();
        } else {
            $_SESSION['forgot_message'] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
    }

    /* ================================
       HANDLE VERIFY CODE
    ================================= */
    public function handleVerifyCode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/front_office/reset-password.php');
            exit();
        }
        
        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code'])) {
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        $code = trim($_POST['verification_code'] ?? '');
        
        $errors = [];
        
        if (empty($code)) {
            $errors['verification_code'] = "Le code de vérification est obligatoire.";
        } elseif ($code != $_SESSION['reset_code']) {
            $errors['verification_code'] = "Code de vérification incorrect.";
        }
        
        if (!empty($errors)) {
            $_SESSION['reset_errors'] = $errors;
            $_SESSION['reset_step'] = 'code';
            $_SESSION['old_input'] = ['verification_code' => $code];
            header('Location: ../views/front_office/reset-password.php');
            exit();
        }
        
        // Code is correct, proceed to password reset
        $_SESSION['reset_step'] = 'password';
        header('Location: ../views/front_office/reset-password.php');
        exit();
    }

    /* ================================
       HANDLE RESET PASSWORD
    ================================= */
    public function handleResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/front_office/reset-password.php');
            exit();
        }
        
        if (!isset($_SESSION['reset_email']) || $_SESSION['reset_step'] !== 'password') {
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Validate password
        $passwordValidation = $this->validatePassword($newPassword);
        if ($passwordValidation !== true) {
            $errors['new_password'] = $passwordValidation;
        }
        
        if ($confirmPassword !== $newPassword) {
            $errors['confirm_password'] = "Les mots de passe ne correspondent pas.";
        }
        
        if (!empty($errors)) {
            $_SESSION['reset_errors'] = $errors;
            $_SESSION['reset_step'] = 'password';
            header('Location: ../views/front_office/reset-password.php');
            exit();
        }
        
        // Update password
        $email = $_SESSION['reset_email'];
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            $_SESSION['reset_message'] = "Erreur: utilisateur non trouvé.";
            header('Location: ../views/front_office/forgot-password.php');
            exit();
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $user['id']]);
            
            if ($result) {
                // Clear session
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_code']);
                unset($_SESSION['reset_step']);
                
                $_SESSION['signin_message'] = "Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                header('Location: ../views/front_office/sign-in.php');
                exit();
            } else {
                $_SESSION['reset_message'] = "Erreur lors de la mise à jour du mot de passe.";
                $_SESSION['reset_step'] = 'password';
                header('Location: ../views/front_office/reset-password.php');
                exit();
            }
        } catch (PDOException $e) {
            error_log("Error resetting password: " . $e->getMessage());
            $_SESSION['reset_message'] = "Erreur de base de données.";
            $_SESSION['reset_step'] = 'password';
            header('Location: ../views/front_office/reset-password.php');
            exit();
        }
    }
}

// ================================
// ROUTING LOGIC
// ================================

$controller = new userController();

// Determine which action to handle based on the request
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Handle Forgot Password
if ($action === 'forgot_password') {
    $controller->handleForgotPassword();
}

// Handle Verify Code
elseif ($action === 'verify_code') {
    $controller->handleVerifyCode();
}

// Handle Reset Password
elseif ($action === 'reset_password') {
    $controller->handleResetPassword();
}

// Handle Users Back Office actions (create, update, delete, toggle_role, toggle_ban)
elseif ($action === 'create' || $action === 'update' || $action === 'delete' || $action === 'toggle_role' || $action === 'toggle_ban') {
    $controller->handleUsersBack();
}

// Handle Delete Profile (check for confirm parameter)
elseif (isset($_GET['confirm']) && $_GET['confirm'] === 'yes' && isset($_SESSION['user_id'])) {
    $controller->handleDeleteProfile();
}

// Handle Signup (check for signup form fields)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname']) && isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['action'])) {
    $result = $controller->handleSignup();
    
    if ($result['success']) {
        header('Location: ../views/front_office/userprofile.php?success=1');
        exit();
    } else {
        $_SESSION['signup_errors'] = $result['errors'] ?? [];
        $_SESSION['signup_message'] = $result['message'];
        $_SESSION['old_input'] = $_POST;
        header('Location: ../views/front_office/sign-up.php');
        exit();
    }
}

// Handle Signin (check for signin form fields)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['fullname']) && !isset($_POST['action'])) {
    $controller->handleSignin();
}

// Handle Update Profile (check for update profile form - has user_id and not admin profile)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && !isset($_POST['action']) && strpos($requestUri, 'admin-profile') === false) {
    $result = $controller->handleUpdateProfile();
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header('Location: ../views/front_office/userprofile.php?updated=1');
        exit();
    } else {
        $_SESSION['update_errors'] = $result['errors'] ?? [];
        $_SESSION['update_message'] = $result['message'];
        $_SESSION['old_input'] = $_POST;
        header('Location: ../views/front_office/userprofile.php?error=1');
        exit();
    }
}

// Handle Admin Profile Update (check for admin profile form)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($requestUri, 'admin-profile') !== false) {
    $controller->handleUpdateAdminProfile();
}

?>

