<?php
// Activer le rapport d'erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Créer le dossier logs s'il n'existe pas
if (!file_exists(dirname(__FILE__) . '/logs')) {
    @mkdir(dirname(__FILE__) . '/logs', 0777, true);
}

class Database {
    private $host = "localhost";
    private $db_name = "user";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            return $this->conn;
            
        } catch(PDOException $exception) {
            error_log("Erreur de connexion : " . $exception->getMessage());
            if (ini_get('display_errors')) {
                echo "Erreur de connexion : " . $exception->getMessage();
            }
            return false;
        }
    }
    
    public function testConnection() {
        $conn = $this->getConnection();
        return ($conn !== false);
    }
}

function connectDB() {
    $database = new Database();
    return $database->getConnection();
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? null;
}

function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function logout() {
    session_unset();
    session_destroy();
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, "/");
    }
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    
    session_start();
    
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

date_default_timezone_set('Africa/Tunis');

// Constantes du site
define('SITE_NAME', 'AI ShieldHub');
define('SITE_URL', 'http://localhost/user1');
define('ADMIN_EMAIL', 'admin@aishieldhub.com');

// NOUVEAU: Définir le chemin de base pour les redirections
define('BASE_PATH', '/user1');
define('LOGIN_PAGE', BASE_PATH . '/view/FutureAi/index.php');

if (isset($_GET['test_db'])) {
    $database = new Database();
    if ($database->testConnection()) {
        echo json_encode(['success' => true, 'message' => 'Connexion réussie!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Échec de connexion!']);
    }
    exit;
}
?>