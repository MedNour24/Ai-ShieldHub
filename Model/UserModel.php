<?php
/**
 * Modèle Utilisateur
 * Gère toutes les opérations de base de données pour les utilisateurs
 */

class UserModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Créer un nouvel utilisateur
     */
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
    
    /**
     * Récupérer un utilisateur par email
     */
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
    
    /**
     * Récupérer tous les utilisateurs
     */
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
    
    /**
     * Récupérer un utilisateur par ID
     */
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
    
    /**
     * Mettre à jour un utilisateur
     */
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
    
    /**
     * Mettre à jour le mot de passe
     */
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
    
    /**
     * Mettre à jour la dernière connexion
     */
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
    
    /**
     * Supprimer un utilisateur
     */
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
    
    /**
     * Vérifier si un email existe
     */
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
    
    /**
     * Compter les utilisateurs
     */
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
    
    /**
     * Récupérer les utilisateurs actifs
     */
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
    
    /**
     * Changer le statut d'un utilisateur
     */
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
    
    // ============================================
    // FONCTIONS POUR LA RÉINITIALISATION DU MOT DE PASSE
    // ============================================
    
    /**
     * Enregistrer un code de réinitialisation de mot de passe
     */
    public function savePasswordResetCode($userId, $code, $expiresAt) {
        try {
            // D'abord, supprimer les anciens codes pour cet utilisateur
            $sqlDelete = "DELETE FROM password_resets WHERE user_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$userId]);
            
            // Ensuite, insérer le nouveau code
            $sql = "INSERT INTO password_resets (user_id, code, expires_at, created_at) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$userId, $code, $expiresAt]);
        } catch(PDOException $e) {
            error_log("Erreur enregistrement code reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier un code de réinitialisation de mot de passe
     */
    public function verifyPasswordResetCode($email, $code) {
        try {
            $sql = "SELECT pr.*, u.id as user_id, u.name, u.email 
                    FROM password_resets pr
                    INNER JOIN users u ON pr.user_id = u.id
                    WHERE u.email = ? AND pr.code = ? AND pr.used = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $code]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'valid' => false,
                    'message' => 'Invalid verification code'
                ];
            }
            
            // Vérifier si le code a expiré
            $now = new DateTime();
            $expiresAt = new DateTime($result['expires_at']);
            
            if ($now > $expiresAt) {
                return [
                    'valid' => false,
                    'message' => 'Verification code has expired. Please request a new one.'
                ];
            }
            
            return [
                'valid' => true,
                'user_id' => $result['user_id'],
                'message' => 'Code verified successfully'
            ];
            
        } catch(PDOException $e) {
            error_log("Erreur vérification code reset: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'An error occurred during verification'
            ];
        }
    }
    
    /**
     * Invalider un code de réinitialisation (marquer comme utilisé)
     */
    public function invalidatePasswordResetCode($userId) {
        try {
            $sql = "UPDATE password_resets SET used = 1 WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Erreur invalidation code reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Nettoyer les codes expirés (à exécuter périodiquement)
     */
    public function cleanExpiredResetCodes() {
        try {
            $sql = "DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur nettoyage codes expirés: " . $e->getMessage());
            return false;
        }
    }
}
?>