<?php
use Arthur\Core\App\Model;

class UsersModel extends Model {
    public $passwordRegister;
    /**
     * UsersModel constructor.
     * @param $tableName
     */
    public function __construct($tableName)
    {
        parent::__construct();
        $this->tableName = $tableName;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function add(): int
    {
        $firstname = htmlspecialchars(trim($_POST['firstname']));
        $lastname = htmlspecialchars(trim($_POST['lastname']));
        $username = "$firstname $lastname";
        $password = htmlspecialchars(trim($_POST['password']));
        $confirmpassword = htmlspecialchars(trim($_POST['confirmpassword']));

        $query = "SELECT email FROM users WHERE email = :email";
        $this->db->prepare($query);
        $this->db->bind('email', htmlspecialchars(trim($_POST['email'])));
        $this->db->execute();
        if ($this->db->rowCount() > 0):
            throw new RuntimeException('Email sudah terdaftar sebelumnya!', 1);
        endif;
        if ($password !== $confirmpassword):
            throw new \RuntimeException('Password tidak sesuai!', 1);
        endif;
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $this->db->prepare($query);
        $this->db->bind('username', $username);
        $this->db->bind('email', htmlspecialchars(trim($_POST['email'])));
        $this->db->bind('password', password_hash($password, PASSWORD_DEFAULT));
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        $query = "SELECT * FROM {$this->tableName} WHERE email = :email";
        $this->db->prepare($query);
        $this->db->bind('email', htmlspecialchars(trim($_POST['email'])));
        $this->db->execute();
        if ($this->db->rowCount() > 0):
            $result = $this->db->fetch();
            if (password_verify(htmlspecialchars(trim($_POST['password'])), $result['password'])):
                $_SESSION['login'] = true;
                $_SESSION['username'] = $result['username'];
                $_SESSION['role'] = $result['role'];
                if (isset($_POST['remember_me'])):
                    setcookie('key', base64_encode($result['id']), time() + (60 ** 2));
                    setcookie('value', hash('sha512', $result['username']), time() + (60 ** 2));
                endif;
                return true;
            else:
                throw new RuntimeException("Password yang kamu masukkan salah!",1);
            endif;
        else:
            throw new RuntimeException("Email kamu belum terdaftar!", 1);
        endif;
    }
}