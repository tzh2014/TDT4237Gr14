<?php

namespace tdt4237\webapp\models;

use tdt4237\webapp\Hash;

class User
{
    const INSERT_QUERY = "INSERT INTO users(user, pass, email, age, bio, isadmin) VALUES(?, ?, ? , ? , ?, ?)";
    const UPDATE_QUERY = "UPDATE users SET email=?, age=?, bio=?, isadmin=? WHERE id=?";
    const FIND_BY_NAME = "SELECT * FROM users WHERE user= ?";

    const MIN_USER_LENGTH = 3;
    const MAX_USER_LENGTH = 10;
    const MIN_PASSWORD_LENGTH = 8;
    const MAX_PASSWORD_LENGTH = 16;

    protected $id = null;
    protected $user;
    protected $pass;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $age;
    protected $isAdmin = 0;

    static $app;

    function __construct()
    {
    }

    static function make($id, $username, $hash, $email, $bio, $age, $isAdmin)
    {
        $user = new User();
        $user->id = $id;
        $user->user = $username;
        $user->pass = $hash;
        $user->email = $email;
        $user->bio = $bio;
        $user->age = $age;
        $user->isAdmin = $isAdmin;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        if ($this->id === null) {
            $stmt = self::$app->db->prepare(self::INSERT_QUERY);
            $stmt->bindParam(1, $this->user);
            $stmt->bindParam(2, $this->pass);
            $stmt->bindParam(3, $this->email);
            $stmt->bindParam(4, $this->age);
            $stmt->bindParam(5, $this->bio);
            $stmt->bindParam(6, $this->isAdmin);
        } else {
            $stmt = self::$app->db->prepare(self::UPDATE_QUERY);
            $stmt->bindParam(1, $this->email);
            $stmt->bindParam(2, $this->age);
            $stmt->bindParam(3, $this->bio);
            $stmt->bindParam(4, $this->isAdmin);
            $stmt->bindParam(5, $this->id);
        }

        return $stmt->execute();
    }

    function getId()
    {
        return $this->id;
    }

    function getUserName()
    {
        return $this->user;
    }

    function getPasswordHash()
    {
        return $this->pass;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function getAge()
    {
        return $this->age;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->user = $username;
    }

    function setHash($hash)
    {
        $this->pass = $hash;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }

    function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * The caller of this function can check the length of the returned
     * array. If array length is 0, then all checks passed.
     *
     * @param User $user
     * @return array An array of strings of validation errors
     */
    static function validate(User $user, $pass)
    {
        $validationErrors = [];

        if (strlen($user->user) < self::MIN_USER_LENGTH) {
            array_push($validationErrors, "Username too short. Min length is " . self::MIN_USER_LENGTH);
        }

        if (strlen($user->user) > self::MAX_USER_LENGTH) {
            array_push($validationErrors, "Username too long. Max length is " . self::MAX_USER_LENGTH);
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $user->user) === 0) {
            array_push($validationErrors, 'Username can only contain letters and numbers');
        }

        if (self::findByUser($user->user) !== null){
            array_push($validationErrors, 'This username has already existed.');
        }

        if (strlen($pass) < self::MIN_PASSWORD_LENGTH) {
            array_push($validationErrors, "Password too short. Min length is " . self::MIN_PASSWORD_LENGTH);
        }

        if (strlen($pass) > self::MAX_PASSWORD_LENGTH) {
            array_push($validationErrors, "Password too long. Max length is " . self::MAX_PASSWORD_LENGTH);
        }

        $pwdContainsLowercase = preg_match('/[a-z]+/', $pass);
        $pwdContainsUppercase = preg_match('/[A-Z]+/', $pass);
        $pwdContainsDigit     = preg_match('/\d+/',    $pass);

        // Check if password contains both upper and lowercase letters, and numbers, according to OWASP Best Practices
        if ((!$pwdContainsLowercase) || (!$pwdContainsUppercase) || (!$pwdContainsDigit)) {
            array_push($validationErrors, "Password has to contain at least one uppercase character and number.");
        }

        return $validationErrors;
    }

    static function validateAge(User $user)
    {
        $age = $user->getAge();

        if ($age >= 0 && $age <= 150) {
            return true;
        }

        return false;
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $stmt = self::$app->db->prepare(self::FIND_BY_NAME);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        $row = $stmt->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    static function deleteByUsername($username)
    {
        $user = User::findByUser($username);
        if($user == null){
            return false;
        }

        $stmt = self::$app->db->prepare("DELETE FROM users WHERE user= ?");
        $stmt->bindParam(1, $username);

        return $stmt->execute();
    }

    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['user'],
            $row['pass'],
            $row['email'],
            $row['bio'],
            $row['age'],
            $row['isadmin']
        );
    }
}
User::$app = \Slim\Slim::getInstance();
