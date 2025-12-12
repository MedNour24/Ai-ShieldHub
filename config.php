<?php
// Activer le rapport d'erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Gestion des sessions (DOIT être en premier)
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

class config
{
    private static $pdo = null;
    
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "user";
            
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
                
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
               
            } catch (PDOException $e) {
                // Journalisation de l'erreur
                $logDir = dirname(__FILE__) . '/logs';
                if (!file_exists($logDir)) {
                    @mkdir($logDir, 0777, true);
                }
                
                error_log("[" . date('Y-m-d H:i:s') . "] Erreur PDO: " . $e->getMessage() . "\n", 3, $logDir . '/db_errors.log');
                
                if (ini_get('display_errors')) {
                    die('Erreur de connexion: ' . $e->getMessage());
                } else {
                    die('Erreur de connexion à la base de données');
                }
            }
        }
        return self::$pdo;
    }
    
    public static function testConnexion()
    {
        try {
            $conn = self::getConnexion();
            return ($conn !== null);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
// class config
// {   private static $pdo = null;
//     public static function getConnexion()
//     {
//         if (!isset(self::$pdo)) {
//             $servername="localhost";
//             $username="root";
//             $password ="";
//             $dbname="modulecommunaute";
//             try {
//                 self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname",
//                         $username,
//                         $password
                   
//                 );
//                 self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                 self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
               
               
//             } catch (Exception $e) {
//                 die('Erreur: ' . $e->getMessage());
//             }
//         }
//         return self::$pdo;
//     }
// }
// config::getConnexion();
// ?>
