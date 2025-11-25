<?php
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
     * Enregistrer un code de réinitialisation de mot de passe
     */
    public function savePasswordResetCode($userId, $code, $expiresAt) {   
           try {
            $sqlDelete = "DELETE FROM password_resets WHERE user_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$userId]);

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
     * Invalider un code de réinitialisation
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
     * Obtenir les statistiques mensuelles pour l'histogramme
     * Retourne le nombre d'utilisateurs créés par mois sur les 6 derniers mois
     */
    public function getMonthlyUserStats() {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        DATE_FORMAT(created_at, '%b %Y') as month_label,
                        COUNT(*) as user_count,
                        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins
                    FROM users 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
                    ORDER BY month ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // S'assurer que nous avons toujours 6 mois de données
            return $this->fillMissingMonths($results);
            
        } catch(PDOException $e) {
            error_log("Erreur statistiques mensuelles: " . $e->getMessage());
            return $this->getDefaultMonthlyStats();
        }
    }
    
    /**
     * Remplir les mois manquants avec des zéros
     */
    private function fillMissingMonths($data) {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_label = date('M Y', strtotime("-$i months"));
            $months[$month] = [
                'month' => $month,
                'month_label' => $month_label,
                'user_count' => 0,
                'students' => 0,
                'admins' => 0
            ];
        }
        
        // Remplir avec les données existantes
        foreach ($data as $row) {
            if (isset($months[$row['month']])) {
                $months[$row['month']] = $row;
            }
        }
        
        return array_values($months);
    }
    
    /**
     * Statistiques mensuelles par défaut
     */
    private function getDefaultMonthlyStats() {
        $stats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_label = date('M Y', strtotime("-$i months"));
            $stats[] = [
                'month' => $month,
                'month_label' => $month_label,
                'user_count' => 0,
                'students' => 0,
                'admins' => 0
            ];
        }
        return $stats;
    }
    
    /**
     * Obtenir la distribution des rôles pour le diagramme circulaire
     */
    public function getRoleDistribution() {
        try {
            $sql = "SELECT 
                        role,
                        COUNT(*) as count,
                        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users)), 1) as percentage
                    FROM users 
                    GROUP BY role
                    ORDER BY count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur distribution des rôles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir le taux de croissance et d'engagement
     */
    public function getAdvancedStats() {
        try {
            $stats = [];
            
            // Nouveaux utilisateurs cette semaine vs semaine dernière
            $sql = "SELECT 
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as current_week,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                                  AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_week
                    FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $growth = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($growth['last_week'] > 0) {
                $stats['growth_rate'] = round((($growth['current_week'] - $growth['last_week']) / $growth['last_week']) * 100, 1);
            } else {
                $stats['growth_rate'] = $growth['current_week'] > 0 ? 100 : 0;
            }
            $stats['new_users_this_week'] = $growth['current_week'];
            
            // Taux d'engagement (utilisateurs actifs ce mois)
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_month
                    FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $engagement = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($engagement['total'] > 0) {
                $stats['engagement_rate'] = round(($engagement['active_month'] / $engagement['total']) * 100, 1);
            } else {
                $stats['engagement_rate'] = 0;
            }
            $stats['active_month_count'] = $engagement['active_month'];
            
            return $stats;
            
        } catch(PDOException $e) {
            error_log("Erreur récupération statistiques avancées: " . $e->getMessage());
            return [
                'growth_rate' => 0,
                'new_users_this_week' => 0,
                'engagement_rate' => 0,
                'active_month_count' => 0
            ];
        }
    }
    
    /**
     * Obtenir les statistiques d'activité des utilisateurs
     */
    public function getUserActivityStats() {
        try {
            $activityStats = [];
            
            // Utilisateurs actifs aujourd'hui
            $sql = "SELECT COUNT(*) as count FROM users 
                    WHERE DATE(updated_at) = CURDATE() AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activityStats['today_active'] = $stmt->fetchColumn();
            
            // Utilisateurs actifs cette semaine
            $sql = "SELECT COUNT(*) as count FROM users 
                    WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activityStats['week_active'] = $stmt->fetchColumn();
            
            // Utilisateurs actifs ce mois
            $sql = "SELECT COUNT(*) as count FROM users 
                    WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activityStats['month_active'] = $stmt->fetchColumn();
            
            // Top utilisateurs actifs
            $activityStats['top_users'] = $this->getTopActiveUsers();
            
            return $activityStats;
            
        } catch(PDOException $e) {
            error_log("Erreur statistiques activité: " . $e->getMessage());
            return [
                'today_active' => 0,
                'week_active' => 0,
                'month_active' => 0,
                'top_users' => []
            ];
        }
    }
    
    /**
     * Obtenir les utilisateurs les plus actifs
     */
    private function getTopActiveUsers() {
        try {
            $sql = "SELECT id, name, email, role, updated_at,
                    CASE 
                        WHEN updated_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Aujourd\\'hui'
                        WHEN updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'Cette semaine'
                        ELSE 'Ce mois'
                    END as last_seen
                    FROM users 
                    WHERE status = 'active'
                    ORDER BY updated_at DESC 
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ajouter des couleurs et icônes pour l'affichage
            foreach ($users as &$user) {
                if ($user['role'] === 'admin') {
                    $user['color'] = '#fa709a';
                    $user['icon'] = 'fa-user-shield';
                } else {
                    $user['color'] = '#4facfe';
                    $user['icon'] = 'fa-user-graduate';
                }
            }
            
            return $users;
            
        } catch(PDOException $e) {
            error_log("Erreur top utilisateurs: " . $e->getMessage());
            return [];
        }
    }
}
?>