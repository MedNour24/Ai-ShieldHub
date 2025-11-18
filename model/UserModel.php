<?php
class UserModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function createUser($name, $email, $password, $role) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, email, password, role, status, created_at) 
                    VALUES (?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$name, $email, $hashedPassword, $role]);
        } catch(PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur recherche utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllUsers() {
        try {
            $sql = "SELECT id, name, email, role, status, created_at, updated_at 
                    FROM users ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur récupération utilisateurs: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserById($id) {
        try {
            $sql = "SELECT id, name, email, role, status, created_at, updated_at 
                    FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur recherche utilisateur par ID: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUser($id, $name, $email, $role, $status) {
        try {
            $sql = "UPDATE users 
                    SET name = ?, email = ?, role = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$name, $email, $role, $status, $id]);
        } catch(PDOException $e) {
            error_log("Erreur mise à jour utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$hashedPassword, $id]);
        } catch(PDOException $e) {
            error_log("Erreur mise à jour mot de passe: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateLastLogin($id) {
        try {
            $sql = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Erreur mise à jour dernière connexion: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Erreur suppression utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function emailExists($email, $excludeUserId = null) {
        try {
            if ($excludeUserId) {
                $sql = "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email, $excludeUserId]);
            } else {
                $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$email]);
            }
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            return false;
        }
    }
    
    public function countUsers($role = null) {
        try {
            if ($role) {
                $sql = "SELECT COUNT(*) FROM users WHERE role = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$role]);
            } else {
                $sql = "SELECT COUNT(*) FROM users";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erreur comptage utilisateurs: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getActiveUsers() {
        try {
            $sql = "SELECT id, name, email, role, created_at, updated_at 
                    FROM users WHERE status = 'active' ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur récupération utilisateurs actifs: " . $e->getMessage());
            return false;
        }
    }
    
    public function changeUserStatus($id, $status) {
        try {
            $sql = "UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$status, $id]);
        } catch(PDOException $e) {
            error_log("Erreur changement statut utilisateur: " . $e->getMessage());
            return false;
        }
    }
}
?>