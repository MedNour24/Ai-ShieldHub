<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers CORS et JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Vérifier si le fichier config existe
if (!file_exists(dirname(__DIR__) . '/config.php')) {
    echo json_encode([
        'success' => false,
        'message' => 'Fichier config non trouvé'
    ]);
    exit;
}

require_once dirname(__DIR__) . '/config.php';

// Vérifier si le fichier modèle existe
if (!file_exists(dirname(__DIR__) . '/model/UserModel.php')) {
    echo json_encode([
        'success' => false,
        'message' => 'Fichier modèle non trouvé'
    ]);
    exit;
}

require_once dirname(__DIR__) . '/model/UserModel.php';

class UserController {
    private $userModel;
    
    public function __construct($database) {
        $this->userModel = new UserModel($database);
    }
    
    // Inscription
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $role = $_POST['role'] ?? '';
            $terms = isset($_POST['terms']) ? true : false;
            
            $errors = [];
            
            if (empty($name)) $errors[] = "Le nom est requis";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
            if (empty($password) || strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            if ($password !== $confirmPassword) $errors[] = "Les mots de passe ne correspondent pas";
            if (empty($role)) $errors[] = "Le rôle est requis";
            if (!$terms) $errors[] = "Vous devez accepter les conditions d'utilisation";
            
            if (empty($errors)) {
                $existingUser = $this->userModel->getUserByEmail($email);
                if ($existingUser) $errors[] = "Cet email est déjà utilisé";
            }
            
            if (empty($errors)) {
                if ($this->userModel->createUser($name, $email, $password, $role)) {
                    echo json_encode(['success' => true, 'message' => 'Inscription réussie!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        exit;
    }
    
    // Connexion
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;
            
            $errors = [];
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            
            if (empty($password)) {
                $errors[] = "Le mot de passe est requis";
            }
            
            if (empty($errors)) {
                $user = $this->userModel->getUserByEmail($email);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Vérifier si le compte est actif
                    if ($user['status'] !== 'active') {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Votre compte est inactif. Veuillez contacter l\'administrateur.'
                        ]);
                        exit;
                    }
                    
                    // Créer la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                    
                    // Si "Remember me" est coché, créer un cookie
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (86400 * 30), "/");
                    }
                    
                    // Mettre à jour la dernière connexion
                    $this->userModel->updateLastLogin($user['id']);
                    
                    // Rediriger selon le rôle
                    $redirect = '';
                    if ($user['role'] === 'admin') {
                        $redirect = '../back/kaidmin-lite/index.php';
                    } elseif ($user['role'] === 'student') {
                        // Redirection vers la page étudiante (index2.php)
                        $redirect = './index2.php';
                    } else {
                        // Par défaut, redirection vers dashboard
                        $redirect = './dashboard.php';
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Connexion réussie! Redirection en cours...',
                        'redirect' => $redirect,
                        'user' => [
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role' => $user['role']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Email ou mot de passe incorrect'
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        exit;
    }
    
    // Déconnexion
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        // Supprimer le cookie "Remember me"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, "/");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie',
            'redirect' => '../../view/future-ai/index.php'
        ]);
        exit;
    }
    
    // Ajouter un utilisateur (pour l'admin)
    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';
            
            $errors = [];
            
            if (empty($name)) $errors[] = "Name is required";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
            if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
            if ($password !== $confirm_password) $errors[] = "Passwords do not match";
            if (empty($role)) $errors[] = "Role is required";
            
            if (empty($errors)) {
                $existingUser = $this->userModel->getUserByEmail($email);
                if ($existingUser) $errors[] = "Email already exists";
            }
            
            if (empty($errors)) {
                if ($this->userModel->createUser($name, $email, $password, $role)) {
                    echo json_encode(['success' => true, 'message' => 'User added successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error adding user']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        exit;
    }
    
    // Lister tous les utilisateurs
    public function listUsers() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $users = $this->userModel->getAllUsers();
            
            if ($users !== false) {
                echo json_encode(['success' => true, 'data' => $users]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error fetching users']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        exit;
    }
    
    // Mettre à jour un utilisateur
    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? '';
            $status = $_POST['status'] ?? '';
            
            $errors = [];
            
            if (empty($id)) $errors[] = "User ID is required";
            if (empty($name)) $errors[] = "Name is required";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
            if (empty($role)) $errors[] = "Role is required";
            if (empty($status)) $errors[] = "Status is required";
            
            if (empty($errors)) {
                $existingUser = $this->userModel->getUserByEmail($email);
                if ($existingUser && $existingUser['id'] != $id) {
                    $errors[] = "Email already exists for another user";
                }
            }
            
            if (empty($errors)) {
                if ($this->userModel->updateUser($id, $name, $email, $role, $status)) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating user']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        exit;
    }
    
    // Supprimer un utilisateur
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            if ($this->userModel->deleteUser($id)) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting user']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        exit;
    }
}

// Traitement des requêtes
try {
    $database = connectDB();
    
    if (!$database) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    $userController = new UserController($database);
    
    // Déterminer l'action
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'register':
            $userController->register();
            break;
        case 'login':
            $userController->login();
            break;
        case 'logout':
            $userController->logout();
            break;
        case 'add':
            $userController->addUser();
            break;
        case 'list':
            $userController->listUsers();
            break;
        case 'update':
            $userController->updateUser();
            break;
        case 'delete':
            $userController->deleteUser();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action non reconnue: ' . $action
            ]);
            exit;
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
    exit;
}
?>