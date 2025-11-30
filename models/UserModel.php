<?php

class User {

    private int $id;

    private $fullname;

    private $email;

    private $password;

    private $role;

    private $age;

    private $address;

    private $bio;

    private $interests;

    private $created_at;

    public function __construct($fullname, $email, $password, $role = 'user', $age = null, $address = null, $bio = null, $interests = null, $created_at = null) {
        $this->fullname = $fullname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->age = $age;
        $this->address = $address;
        $this->bio = $bio;
        $this->interests = $interests;
        $this->created_at = $created_at;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of fullname
     */
    public function getFullname() {
        return $this->fullname;
    }

    /**
     * Set the value of fullname
     */
    public function setFullname($fullname): self {
        $this->fullname = $fullname;
        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail($email): self {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set the value of password
     */
    public function setPassword($password): self {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * Set the value of role
     */
    public function setRole($role): self {
        $this->role = $role;
        return $this;
    }

    /**
     * Get the value of age
     */
    public function getAge() {
        return $this->age;
    }

    /**
     * Set the value of age
     */
    public function setAge($age): self {
        $this->age = $age;
        return $this;
    }

    /**
     * Get the value of address
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Set the value of address
     */
    public function setAddress($address): self {
        $this->address = $address;
        return $this;
    }

    /**
     * Get the value of bio
     */
    public function getBio() {
        return $this->bio;
    }

    /**
     * Set the value of bio
     */
    public function setBio($bio): self {
        $this->bio = $bio;
        return $this;
    }

    /**
     * Get the value of interests
     */
    public function getInterests() {
        return $this->interests;
    }

    /**
     * Set the value of interests
     */
    public function setInterests($interests): self {
        $this->interests = $interests;
        return $this;
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     */
    public function setCreatedAt($created_at): self {
        $this->created_at = $created_at;
        return $this;
    }
}

?>

