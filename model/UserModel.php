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
                    VALUES (:name, :email, :password, :role, 'active', NOW())";
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Créer une notification d'inscription
            $this->createNotification(
                'registration',
                'Nouvel utilisateur inscrit',
                "Un nouvel utilisateur '{$name}' ({$email}) s'est inscrit avec le rôle '{$role}'.",
                null,
                'low',
                $userId
            );
            
            return $userId;
            
        } catch(PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            throw new Exception("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
        }
    }
    
    /**
     * Récupérer un utilisateur par email
     */
    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Erreur recherche utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un utilisateur par ID
     */
    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            error_log("Erreur recherche utilisateur par ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un email existe
     */
    public function emailExists($email, $excludeUserId = null) {
        try {
            if ($excludeUserId) {
                $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :excludeId";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':email' => $email,
                    ':excludeId' => $excludeUserId
                ]);
            } else {
                $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':email' => $email]);
            }
            
            return $stmt->fetchColumn() > 0;
            
        } catch(PDOException $e) {
            error_log("Erreur vérification email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword($id, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $id
            ]);
            
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
            $sql = "UPDATE users SET updated_at = NOW() WHERE id = :id";
           $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $id]);
            
        } catch(PDOException $e) {
            error_log("Erreur mise à jour dernière connexion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sauvegarder un code de réinitialisation
     */
    public function savePasswordResetCode($userId, $code, $expiresAt) {   
        try {
            // D'abord supprimer les anciens codes
            $sqlDelete = "DELETE FROM password_resets WHERE user_id = :userId";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([':userId' => $userId]);
            
            // Insérer le nouveau code
            $sql = "INSERT INTO password_resets (user_id, code, expires_at, created_at) 
                    VALUES (:userId, :code, :expiresAt, NOW())";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':userId' => $userId,
                ':code' => $code,
                ':expiresAt' => $expiresAt
            ]);
            
        } catch(PDOException $e) {
            error_log("Erreur enregistrement code reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier un code de réinitialisation
     */
    public function verifyPasswordResetCode($email, $code) {
        try {
            $sql = "SELECT pr.*, u.id as user_id 
                    FROM password_resets pr
                    INNER JOIN users u ON pr.user_id = u.id
                    WHERE u.email = :email AND pr.code = :code AND pr.used = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':code' => $code
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'valid' => false,
                    'message' => 'Code de vérification invalide'
                ];
            }
            
            $now = new DateTime();
            $expiresAt = new DateTime($result['expires_at']);
            
            if ($now > $expiresAt) {
                return [
                    'valid' => false,
                    'message' => 'Le code de vérification a expiré. Veuillez en demander un nouveau.'
                ];
            }
            
            return [
                'valid' => true,
                'user_id' => $result['user_id'],
                'message' => 'Code vérifié avec succès'
            ];
            
        } catch(PDOException $e) {
            error_log("Erreur vérification code reset: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'Une erreur est survenue lors de la vérification'
            ];
        }
    }
    
    /**
     * Invalider un code de réinitialisation
     */
    public function invalidatePasswordResetCode($userId) {
        try {
            $sql = "UPDATE password_resets SET used = 1 WHERE user_id = :userId";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':userId' => $userId]);
            
        } catch(PDOException $e) {
            error_log("Erreur invalidation code reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sauvegarder le token remember me
     */
    public function saveRememberToken($userId, $token) {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + (86400 * 30));
            $sql = "UPDATE users SET remember_token = :token, remember_token_expires = :expiresAt WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':token' => $token,
                ':expiresAt' => $expiresAt,
                ':id' => $userId
            ]);
            
        } catch(PDOException $e) {
            error_log("Erreur sauvegarde token: " . $e->getMessage());
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
                    SET name = :name, email = :email, role = :role, status = :status, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':status' => $status,
                ':id' => $id
            ]);
            
        } catch(PDOException $e) {
            error_log("Erreur mise à jour utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateUserProfile($userId, $name, $email, $password = null, $profileImage = null) {
        try {
            // Construire la requête dynamiquement
            $updateFields = ["name = :name", "email = :email"];
            $params = [
                ':name' => $name,
                ':email' => $email,
                ':id' => $userId
            ];
            
            // Ajouter le mot de passe si fourni
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = "password = :password";
                $params[':password'] = $hashedPassword;
            }
            
            // Ajouter l'image de profil si fournie
            if (!empty($profileImage)) {
                $updateFields[] = "profile_image = :profile_image";
                $params[':profile_image'] = $profileImage;
            }
            
            // Toujours mettre à jour la date
            $updateFields[] = "updated_at = NOW()";
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch(PDOException $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $id]);
            
        } catch(PDOException $e) {
            error_log("Erreur suppression utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bannir un utilisateur
     */
    public function banUser($userId) {
        try {
            $sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $userId]);
            
        } catch(PDOException $e) {
            error_log("Erreur bannissement utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Débannir un utilisateur
     */
    public function unbanUser($userId) {
        try {
            $sql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $userId]);
            
        } catch(PDOException $e) {
            error_log("Erreur réactivation utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Compter les utilisateurs
     */
    public function countUsers($role = null) {
        try {
            if ($role) {
                $sql = "SELECT COUNT(*) FROM users WHERE role = :role";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':role' => $role]);
            } else {
                $sql = "SELECT COUNT(*) FROM users";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            
            return (int)$stmt->fetchColumn();
            
        } catch(PDOException $e) {
            error_log("Erreur comptage utilisateurs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Compter les utilisateurs actifs
     */
    public function countActiveUsers() {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
            
        } catch(PDOException $e) {
            error_log("Erreur comptage utilisateurs actifs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtenir les statistiques mensuelles
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
            
            // Remplir les mois manquants
            return $this->fillMissingMonths($results);
            
        } catch(PDOException $e) {
            error_log("Erreur statistiques mensuelles: " . $e->getMessage());
            return $this->getDefaultMonthlyStats();
        }
    }
  
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
      
        foreach ($data as $row) {
            if (isset($months[$row['month']])) {
                $months[$row['month']] = $row;
            }
        }
        
        return array_values($months);
    }

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
     * Obtenir les statistiques avancées
     */
    public function getAdvancedStats() {
        try {
            $stats = [];
            
            // Nouveaux utilisateurs cette semaine
            $sql = "SELECT 
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as current_week,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                                  AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_week
                    FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $growth = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $currentWeek = (int)$growth['current_week'];
            $lastWeek = (int)$growth['last_week'];
            
            if ($lastWeek > 0) {
                $stats['growth_rate'] = round((($currentWeek - $lastWeek) / $lastWeek) * 100, 1);
            } else {
                $stats['growth_rate'] = $currentWeek > 0 ? 100 : 0;
            }
            
            $stats['new_users_this_week'] = $currentWeek;
            
            return $stats;
            
        } catch(PDOException $e) {
            error_log("Erreur récupération statistiques avancées: " . $e->getMessage());
            return [
                'growth_rate' => 0,
                'new_users_this_week' => 0
            ];
        }
    }
    
    /**
     * Obtenir les statistiques d'activité
     */
    public function getUserActivityStats() {
        try {
            $activityStats = [];
            
            // Utilisateurs actifs cette semaine (mise à jour dans les 7 derniers jours)
            $sql = "SELECT COUNT(*) as count FROM users 
                    WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activityStats['week_active'] = (int)$stmt->fetchColumn();
            
            // Pour "aujourd'hui" - utilisateurs mis à jour aujourd'hui
            $sqlToday = "SELECT COUNT(*) as count FROM users 
                        WHERE DATE(updated_at) = CURDATE() AND status = 'active'";
            $stmtToday = $this->db->prepare($sqlToday);
            $stmtToday->execute();
            $activityStats['today_active'] = (int)$stmtToday->fetchColumn();
            
            // Pour "ce mois" - utilisateurs mis à jour ce mois
            $sqlMonth = "SELECT COUNT(*) as count FROM users 
                        WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = 'active'";
            $stmtMonth = $this->db->prepare($sqlMonth);
            $stmtMonth->execute();
            $activityStats['month_active'] = (int)$stmtMonth->fetchColumn();
            
            // Top utilisateurs actifs
            $activityStats['top_users'] = $this->getTopActiveUsers();
            
            return $activityStats;
            
        } catch(PDOException $e) {
            error_log("Erreur statistiques activité: " . $e->getMessage());
           return [
                'week_active' => 0,
                'today_active' => 0,
                'month_active' => 0,
                'top_users' => []
            ];
        }
    }
   
    private function getTopActiveUsers() {
        try {
            $sql = "SELECT id, name, email, role, updated_at,
                    CASE 
                        WHEN updated_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Aujourd''hui'
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
            
            // Ajouter des couleurs aléatoires pour l'affichage
            $colors = ['#667eea', '#fa709a', '#4facfe', '#43e97b', '#f6d365'];
            foreach ($users as &$user) {
                $user['color'] = $colors[array_rand($colors)];
            }
            
            return $users;
            
        } catch(PDOException $e) {
            error_log("Erreur top utilisateurs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Créer une notification
     */
    public function createNotification($type, $title, $message, $userId = null, $severity = 'low', $relatedUserId = null) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return true; // Simuler la création si la table n'existe pas
            }
            
            // Si la table existe, insérer la notification
            $sql = "INSERT INTO notifications (type, title, message, user_id, severity, related_user_id, is_read, created_at) 
                    VALUES (:type, :title, :message, :user_id, :severity, :related_user_id, 0, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':type' => $type,
                ':title' => $title,
                ':message' => $message,
                ':user_id' => $userId,
                ':severity' => $severity,
                ':related_user_id' => $relatedUserId
            ]);
            
            return true;
            
        } catch(PDOException $e) {
            error_log("Erreur création notification: " . $e->getMessage());
            return true; // Retourner true même en cas d'erreur pour ne pas bloquer le flux
        }
    }
    
    /**
     * Récupérer toutes les notifications
     */
    public function getAllNotifications($userId, $limit = 50) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return []; // Retourner tableau vide si la table n'existe pas
            }
            
            // Récupérer les notifications
            $sql = "SELECT * FROM notifications 
                    WHERE (user_id IS NULL OR user_id = :user_id)
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
       } catch(PDOException $e) {
            error_log("Erreur récupération notifications: " . $e->getMessage());
            return [];
        }
   }
    
    /**
     * Compter les notifications non lues
     */
    public function countUnreadNotifications($userId) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE (user_id IS NULL OR user_id = :user_id) 
                    AND is_read = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return (int)$stmt->fetchColumn();
            
        } catch(PDOException $e) {
            error_log("Erreur comptage notifications: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Marquer une notification comme lue
     */
    public function markNotificationAsRead($userId, $notificationId) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return true;
            }
            
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE id = :notification_id 
                    AND (user_id IS NULL OR user_id = :user_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':notification_id' => $notificationId,
                ':user_id' => $userId
            ]);
            
            return $stmt->rowCount() > 0;
            
        } catch(PDOException $e) {
            error_log("Erreur marquage notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllNotificationsAsRead($userId) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return true;
            }
            
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE (user_id IS NULL OR user_id = :user_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return true;
            
        } catch(PDOException $e) {
            error_log("Erreur marquage toutes notifications: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une notification
     */
    public function deleteNotification($userId, $notificationId) {
        try {
            // Vérifier si la table notifications existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'notifications'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return true;
            }
            
            $sql = "DELETE FROM notifications 
                    WHERE id = :notification_id 
                    AND (user_id IS NULL OR user_id = :user_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':notification_id' => $notificationId,
                ':user_id' => $userId
            ]);
            
            return $stmt->rowCount() > 0;
            
        } catch(PDOException $e) {
            error_log("Erreur suppression notification: " . $e->getMessage());
            return false;
        }
   }
    
    /**
     * Enregistrer une tentative de connexion
     */
    public function logLoginAttempt($email, $ipAddress, $userAgent, $success) {
        try {
            // Vérifier si la table login_attempts existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'login_attempts'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return true;
            }
            
            $sql = "INSERT INTO login_attempts (email, ip_address, user_agent, success, created_at) 
                    VALUES (:email, :ip_address, :user_agent, :success, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':success' => $success ? 1 : 0
            ]);
            
            return true;
            
        } catch(PDOException $e) {
            error_log("Erreur log tentative connexion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Détecter les activités suspectes
     */
    public function detectSuspiciousActivity() {
        try {
            // Simuler la détection - dans une vraie implémentation, vous analyseriez les logs
            $suspiciousActivities = [];
            
            // Exemple: Détecter plusieurs tentatives de connexion échouées
            $sql = "SELECT email, COUNT(*) as attempts 
                    FROM login_attempts 
                    WHERE success = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    GROUP BY email 
                    HAVING attempts >= 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $failedAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($failedAttempts as $attempt) {
                $suspiciousActivities[] = [
                    'type' => 'failed_login_attempts',
                    'email' => $attempt['email'],
                    'attempts' => $attempt['attempts'],
                    'message' => "{$attempt['email']} a eu {$attempt['attempts']} tentatives de connexion échouées en 1 heure"
                ];
                
                // Créer une notification pour l'activité suspecte
                $this->createNotification(
                    'suspicious_activity',
                    'Activité suspecte détectée',
                    "Plusieurs tentatives de connexion échouées pour {$attempt['email']}",
                    null,
                    'high'
                );
            }
            
            return $suspiciousActivities;
            
        } catch(PDOException $e) {
            error_log("Erreur détection activité suspecte: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques de connexion
     */
    public function getLoginStats() {
        try {
            // Vérifier si la table login_attempts existe
            $checkTable = $this->db->query("SHOW TABLES LIKE 'login_attempts'");
            $tableExists = $checkTable->rowCount() > 0;
            
            if (!$tableExists) {
                return [
                    'today_attempts' => 0,
                    'today_failed' => 0,
                    'failure_rate' => 0
                ];
            }
            
            // Tentatives aujourd'hui
            $sqlToday = "SELECT 
                            COUNT(*) as total_attempts,
                            SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_attempts
                         FROM login_attempts 
                         WHERE DATE(created_at) = CURDATE()";
            
            $stmtToday = $this->db->prepare($sqlToday);
            $stmtToday->execute();
            $todayStats = $stmtToday->fetch(PDO::FETCH_ASSOC);
            
            $totalAttempts = (int)$todayStats['total_attempts'];
            $failedAttempts = (int)$todayStats['failed_attempts'];
            
            $failureRate = $totalAttempts > 0 ? round(($failedAttempts / $totalAttempts) * 100, 1) : 0;
            
            return [
                'today_attempts' => $totalAttempts,
                'today_failed' => $failedAttempts,
                'failure_rate' => $failureRate
            ];
            
        } catch(PDOException $e) {
            error_log("Erreur stats connexion: " . $e->getMessage());
            return [
                'today_attempts' => 0,
                'today_failed' => 0,
                'failure_rate' => 0
            ];
        }
    }
}
?>
