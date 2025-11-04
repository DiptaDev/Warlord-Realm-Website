<?php
// config.php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "warlord_realm";
    private $username = "warlord_realm";
    private $password = "warlordnetwork";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
} // ← TAMBAHKAN INI (kurung tutup class Database)

// Simple session management for admin
session_start();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function adminLogin($username, $password) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $query = "SELECT * FROM admin_users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_id'] = $admin['id'];
                return true;
            }
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function adminLogout() {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}
?>