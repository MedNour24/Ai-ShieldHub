<?php
class UserModel {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Bannir un utilisateur (désactiver son compte)
     */
    public function banUser($userId) {
        try {
            $sql = "UPDATE users SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Erreur bannissement utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Débannir un utilisateur (réactiver son compte)
     */
    public function unbanUser($userId) {
        try {
            $sql = "UPDATE users SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Erreur réactivation utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un utilisateur est banni
     */
    public function isUserBanned($userId) {
        try {
            $sql = "SELECT status FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['status'] === 'inactive';
        } catch(PDOException $e) {
            error_log("Erreur vérification statut utilisateur: " . $e->getMessage());
            return false;
        }
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
     * Récupérer un utilisateur par ID
     */
    public function getUserById($id) {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur recherche utilisateur par ID: " . $e->getMessage());
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
     * Mettre à jour le profil utilisateur
     */
    public function updateUserProfile($userId, $name, $email, $password = null, $profileImage = null) {
        try {
            // Construire la requête dynamiquement
            $updateFields = [];
            $params = [];
            
            $updateFields[] = "name = ?";
            $params[] = $name;
            
            $updateFields[] = "email = ?";
            $params[] = $email;
            
            // Ajouter le mot de passe si fourni
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = "password = ?";
                $params[] = $hashedPassword;
            }
            
            // Ajouter l'image de profil si fournie
            if (!empty($profileImage)) {
                $updateFields[] = "profile_image = ?";
                $params[] = $profileImage;
            }
            
            // Toujours mettre à jour la date de modification
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch(PDOException $e) {
            error_log("Error updating user profile: " . $e->getMessage());
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
    
    /**
     * Supprimer l'image de profil d'un utilisateur
     */
    public function deleteProfileImage($userId) {
        try {
            $sql = "UPDATE users SET profile_image = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$userId]);
        } catch(PDOException $e) {
            error_log("Erreur suppression image profil: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les informations de profil complètes
     */
    public function getProfileInfo($userId) {
        try {
            $sql = "SELECT id, name, email, profile_image, role, status, created_at, updated_at 
                    FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur récupération info profil: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // GESTION DES NOTIFICATIONS (JSON dans users)
    // ========================================
    
    /**
     * Créer une nouvelle notification pour tous les admins
     */
    public function createNotification($type, $title, $message, $userId = null, $severity = 'low') {
        try {
            $notification = [
                'id' => uniqid('notif_', true),
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'severity' => $severity,
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Si userId est spécifié, ajouter seulement à cet utilisateur
            if ($userId) {
                return $this->addNotificationToUser($userId, $notification);
            }
            
            // Sinon, ajouter à tous les admins
            $sql = "SELECT id FROM users WHERE role = 'admin'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($admins as $admin) {
                $this->addNotificationToUser($admin['id'], $notification);
            }
            
            return true;
        } catch(PDOException $e) {
            error_log("Erreur création notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajouter une notification à un utilisateur spécifique
     */
    private function addNotificationToUser($userId, $notification) {
        try {
            // Récupérer les notifications actuelles
            $sql = "SELECT notifications, unread_notifications_count FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $notifications = [];
            if (!empty($user['notifications'])) {
                $notifications = json_decode($user['notifications'], true) ?: [];
            }
            
            // Ajouter la nouvelle notification au début
            array_unshift($notifications, $notification);
            
            // Garder seulement les 100 dernières notifications
            $notifications = array_slice($notifications, 0, 100);
            
            // Mettre à jour la base de données
            $sql = "UPDATE users 
                    SET notifications = ?, 
                        unread_notifications_count = unread_notifications_count + 1,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([json_encode($notifications), $userId]);
            
        } catch(PDOException $e) {
            error_log("Erreur ajout notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer toutes les notifications d'un utilisateur
     */
    public function getAllNotifications($userId, $limit = 50) {
        try {
            $sql = "SELECT notifications FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!empty($user['notifications'])) {
                $notifications = json_decode($user['notifications'], true) ?: [];
                return array_slice($notifications, 0, $limit);
            }
            
            return [];
        } catch(PDOException $e) {
            error_log("Erreur récupération notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les notifications non lues
     */
    public function getUnreadNotifications($userId) {
        try {
            $notifications = $this->getAllNotifications($userId);
            return array_filter($notifications, function($notif) {
                return !$notif['is_read'];
            });
        } catch(Exception $e) {
            error_log("Erreur récupération notifications non lues: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compter les notifications non lues
     */
    public function countUnreadNotifications($userId) {
        try {
            $sql = "SELECT unread_notifications_count FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['unread_notifications_count'] : 0;
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
            $sql = "SELECT notifications, unread_notifications_count FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($user['notifications'])) {
                return false;
            }
            
            $notifications = json_decode($user['notifications'], true) ?: [];
            $updated = false;
            
            foreach ($notifications as &$notif) {
                if ($notif['id'] === $notificationId && !$notif['is_read']) {
                    $notif['is_read'] = true;
                    $updated = true;
                    break;
                }
            }
            
            if ($updated) {
                $newCount = max(0, (int)$user['unread_notifications_count'] - 1);
                $sql = "UPDATE users 
                        SET notifications = ?, 
                            unread_notifications_count = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([json_encode($notifications), $newCount, $userId]);
            }
            
            return false;
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
            $sql = "SELECT notifications FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($user['notifications'])) {
                return true;
            }
            
            $notifications = json_decode($user['notifications'], true) ?: [];
            
            foreach ($notifications as &$notif) {
                $notif['is_read'] = true;
            }
            
            $sql = "UPDATE users 
                    SET notifications = ?, 
                        unread_notifications_count = 0,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([json_encode($notifications), $userId]);
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
            $sql = "SELECT notifications, unread_notifications_count FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($user['notifications'])) {
                return false;
            }
            
            $notifications = json_decode($user['notifications'], true) ?: [];
            $wasUnread = false;
            
            $notifications = array_filter($notifications, function($notif) use ($notificationId, &$wasUnread) {
                if ($notif['id'] === $notificationId) {
                    if (!$notif['is_read']) {
                        $wasUnread = true;
                    }
                    return false;
                }
                return true;
            });
            
            $notifications = array_values($notifications);
            
            $newCount = $wasUnread ? max(0, (int)$user['unread_notifications_count'] - 1) : (int)$user['unread_notifications_count'];
            
            $sql = "UPDATE users 
                    SET notifications = ?, 
                        unread_notifications_count = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([json_encode($notifications), $newCount, $userId]);
        } catch(PDOException $e) {
            error_log("Erreur suppression notification: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // GESTION DES TENTATIVES DE CONNEXION (JSON)
    // ========================================
    
    /**
     * Enregistrer une tentative de connexion
     */
    public function logLoginAttempt($email, $ipAddress, $userAgent, $success) {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return false;
            }
            
            $attempt = [
                'ip' => $ipAddress,
                'user_agent' => $userAgent,
                'success' => $success,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Récupérer les tentatives existantes
            $loginAttempts = [];
            if (!empty($user['login_attempts'])) {
                $loginAttempts = json_decode($user['login_attempts'], true) ?: [];
            }
            
            // Ajouter la nouvelle tentative
            array_unshift($loginAttempts, $attempt);
            
            // Garder seulement les 100 dernières tentatives
            $loginAttempts = array_slice($loginAttempts, 0, 100);
            
            // Mettre à jour
            $sql = "UPDATE users 
                    SET login_attempts = ?,
                        last_login_ip = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([json_encode($loginAttempts), $ipAddress, $user['id']]);
            
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
            $suspiciousActivities = [];
            $now = time();
            
            // Récupérer tous les utilisateurs avec leurs tentatives
            $sql = "SELECT id, name, email, login_attempts FROM users WHERE login_attempts IS NOT NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                if (empty($user['login_attempts'])) continue;
                
                $attempts = json_decode($user['login_attempts'], true) ?: [];
                
                // 1. Tentatives échouées répétées (5+ dans les dernières 15 minutes)
                $recentFailed = array_filter($attempts, function($attempt) use ($now) {
                    $attemptTime = strtotime($attempt['timestamp']);
                    return !$attempt['success'] && ($now - $attemptTime) <= 900; // 15 minutes
                });
                
                if (count($recentFailed) >= 5) {
                    $ips = array_unique(array_column($recentFailed, 'ip'));
                    $suspiciousActivities[] = [
                        'type' => 'failed_login',
                        'severity' => 'high',
                        'title' => 'Tentatives de connexion échouées répétées',
                        'message' => count($recentFailed) . " tentatives de connexion échouées détectées pour '{$user['email']}' depuis " . count($ips) . " IP(s): " . implode(', ', $ips),
                        'data' => ['email' => $user['email'], 'count' => count($recentFailed)]
                    ];
                }
                
                // 2. Connexions depuis plusieurs IPs (3+ IPs différentes en 1 heure)
                $recentAttempts = array_filter($attempts, function($attempt) use ($now) {
                    $attemptTime = strtotime($attempt['timestamp']);
                    return ($now - $attemptTime) <= 3600; // 1 heure
                });
                
                $uniqueIPs = array_unique(array_column($recentAttempts, 'ip'));
                if (count($uniqueIPs) >= 3) {
                    $suspiciousActivities[] = [
                        'type' => 'multiple_ips',
                        'severity' => 'medium',
                        'title' => 'Connexions depuis plusieurs IPs',
                        'message' => "Le compte '{$user['email']}' s'est connecté depuis " . count($uniqueIPs) . " adresses IP différentes en 1 heure: " . implode(', ', $uniqueIPs),
                        'data' => ['email' => $user['email'], 'ips' => $uniqueIPs]
                    ];
                }
            }
            
            // 3. Pics d'inscriptions inhabituels (10+ en 1 heure)
            $sql = "SELECT COUNT(*) as new_users,
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour_slot
                    FROM users 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY hour_slot
                    HAVING new_users >= 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $registrationSpikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($registrationSpikes as $spike) {
                $suspiciousActivities[] = [
                    'type' => 'registration_spike',
                    'severity' => 'medium',
                    'title' => 'Pic d\'inscriptions inhabituel',
                    'message' => "{$spike['new_users']} nouveaux utilisateurs se sont inscrits pendant l'heure {$spike['hour_slot']}",
                    'data' => $spike
                ];
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
            $stats = [
                'today_attempts' => 0,
                'today_failed' => 0,
                'failure_rate' => 0
            ];
            
            $now = time();
            $todayStart = strtotime('today');
            
            $sql = "SELECT login_attempts FROM users WHERE login_attempts IS NOT NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($users as $user) {
                if (empty($user['login_attempts'])) continue;
                
                $attempts = json_decode($user['login_attempts'], true) ?: [];
                
                foreach ($attempts as $attempt) {
                    $attemptTime = strtotime($attempt['timestamp']);
                    if ($attemptTime >= $todayStart) {
                        $stats['today_attempts']++;
                        if (!$attempt['success']) {
                            $stats['today_failed']++;
                        }
                    }
                }
            }
            
            if ($stats['today_attempts'] > 0) {
                $stats['failure_rate'] = round(($stats['today_failed'] / $stats['today_attempts']) * 100, 1);
            }
            
            return $stats;
            
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