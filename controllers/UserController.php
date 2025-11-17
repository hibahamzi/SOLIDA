<?php
require_once __DIR__ . '/../models/UserModel.php';
class UserController {
    private $model;
    public function __construct() { $this->model = new UserModel(); }
    public function index() {
        $users = $this->model->getAllUsers();
        include __DIR__ . '/../views/user/list.php';
    }
}
?>