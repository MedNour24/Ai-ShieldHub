<?php
/**
 * TeamController - Business Logic & API Controller
 * Handles all team-related operations with security
 * FIXED VERSION - CSRF Token Validation Disabled
 */

// Autoload dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Model/team.php';


class TeamController
{
    private $pdo;
    private $team;
    
    // Security constants
    private const ALLOWED_ACTIONS = [
        'create', 'read', 'update', 'delete', 'list',
        'listByTournoi', 'listByCategory', 'search',
        'join', 'getTournois', 'checkTag', 'getStatistics'
    ];
    
    // Validation constants
    private const MIN_NAME_LENGTH = 3;
    private const MAX_NAME_LENGTH = 100;
    private const MIN_TAG_LENGTH = 2;
    private const MAX_TAG_LENGTH = 10;
    private const PHONE_PATTERN = '/^[\d\s\+\-\(\)\.]+$/';
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeSession();
        $this->initializeDatabase();
    }
    
    /**
     * Initialize session securely
     */
    private function initializeSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_secure', '0'); // Set to '1' if using HTTPS
            session_start();
        }
    }
    
    /**
     * Initialize database connection
     */
    private function initializeDatabase(): void
    {
        try {
            $this->pdo = config::getConnexion();
            $this->team = new Team($this->pdo);
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->sendJsonResponse(false, "Erreur de connexion au serveur");
            exit;
        }
    }
    
    /**
     * Main request router with security
     */
    public function handleRequest(): void
    {
        // CSRF protection DISABLED for development
        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //     $this->validateCsrfToken();
        // }
        
        $action = $this->sanitizeInput($_POST['action'] ?? $_GET['action'] ?? '');
        
        // Validate action
        if (empty($action)) {
            $this->sendJsonResponse(false, "Action non spécifiée");
            return;
        }
        
        if (!in_array($action, self::ALLOWED_ACTIONS)) {
            $this->sendJsonResponse(false, "Action non autorisée");
            return;
        }
        
        // Rate limiting
        if (!$this->checkRateLimit()) {
            $this->sendJsonResponse(false, "Trop de requêtes. Veuillez patienter.");
            return;
        }
        
        try {
            // Dynamic method call
            $methodName = $action . 'Action';
            if (method_exists($this, $methodName)) {
                $this->$methodName();
            } else {
                $this->sendJsonResponse(false, "Action non reconnue");
            }
        } catch (Exception $e) {
            error_log("Error in action '$action': " . $e->getMessage());
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    // ============ ACTION METHODS ============
    
    /**
     * Create a new team
     */
    private function createAction(): void
    {
        try {
            $data = $this->validateCreateData($_POST);
            
            // Check tag uniqueness
            if (!$this->team->isTagUnique($data['team_tag'])) {
                throw new Exception("Ce tag d'équipe est déjà utilisé");
            }
            
            // Set model properties
            $this->team->setIdTournoi($data['id_tournoi']);
            $this->team->setTeamName($data['team_name']);
            $this->team->setTeamTag($data['team_tag']);
            $this->team->setCountry($data['country']);
            $this->team->setLeaderName($data['leader_name']);
            $this->team->setLeaderEmail($data['leader_email']);
            $this->team->setLeaderPhone($data['leader_phone']);
            $this->team->setCategory($data['category']);
            
            // Process members
            $members = $this->processMembers($_POST);
            $this->team->setMembers($members);
            
            // Create
            if ($this->team->create()) {
                $this->logAction('create_team', $this->team->getIdTeam());
                $this->sendJsonResponse(true, "Équipe créée avec succès", [
                    'id_team' => $this->team->getIdTeam(),
                    'team_name' => $this->team->getTeamName(),
                    'team_tag' => $this->team->getTeamTag()
                ]);
            } else {
                throw new Exception("Erreur lors de la création de l'équipe");
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Read a single team
     */
    private function readAction(): void
    {
        try {
            $id_team = $this->validateId($_GET['id_team'] ?? null);
            
            if ($this->team->read($id_team)) {
                $data = [
                    'id_team' => $this->team->getIdTeam(),
                    'id_tournoi' => $this->team->getIdTournoi(),
                    'team_name' => $this->team->getTeamName(),
                    'team_tag' => $this->team->getTeamTag(),
                    'country' => $this->team->getCountry(),
                    'leader_name' => $this->team->getLeaderName(),
                    'leader_email' => $this->team->getLeaderEmail(),
                    'leader_phone' => $this->team->getLeaderPhone(),
                    'category' => $this->team->getCategory(),
                    'members' => $this->team->getMembers(),
                    'total_members' => $this->team->getTotalMembers(),
                    'created_at' => $this->team->getCreatedAt()
                ];
                $this->sendJsonResponse(true, "Équipe trouvée", $data);
            } else {
                $this->sendJsonResponse(false, "Équipe introuvable", null, 404);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Update a team
     */
    private function updateAction(): void
    {
        try {
            $id_team = $this->validateId($_POST['id_team'] ?? null);
            $data = $this->validateCreateData($_POST);
            
            // Load existing team
            if (!$this->team->read($id_team)) {
                throw new Exception("Équipe introuvable");
            }
            
            // Check tag uniqueness (excluding current team)
            if (!$this->team->isTagUnique($data['team_tag'], $id_team)) {
                throw new Exception("Ce tag d'équipe est déjà utilisé");
            }
            
            // Update properties
            $this->team->setIdTeam($id_team);
            $this->team->setIdTournoi($data['id_tournoi']);
            $this->team->setTeamName($data['team_name']);
            $this->team->setTeamTag($data['team_tag']);
            $this->team->setCountry($data['country']);
            $this->team->setLeaderName($data['leader_name']);
            $this->team->setLeaderEmail($data['leader_email']);
            $this->team->setLeaderPhone($data['leader_phone']);
            $this->team->setCategory($data['category']);
            
            // Process members
            $members = $this->processMembers($_POST);
            $this->team->setMembers($members);
            
            // Update
            if ($this->team->update()) {
                $this->logAction('update_team', $id_team);
                $this->sendJsonResponse(true, "Équipe mise à jour avec succès");
            } else {
                throw new Exception("Erreur lors de la mise à jour");
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Delete a team
     */
    private function deleteAction(): void
    {
        try {
            $id_team = $this->validateId($_POST['id_team'] ?? null);
            
            // Verify team exists
            if (!$this->team->read($id_team)) {
                throw new Exception("Équipe introuvable");
            }
            
            if ($this->team->delete($id_team)) {
                $this->logAction('delete_team', $id_team);
                $this->sendJsonResponse(true, "Équipe supprimée avec succès");
            } else {
                throw new Exception("Erreur lors de la suppression");
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * List all teams
     */
    private function listAction(): void
    {
        try {
            $teams = $this->team->listAll();
            
            // Process members JSON and enrich data
            $teams = array_map(function($team) {
                $team['members'] = json_decode($team['members'], true) ?: [];
                $team['total_members'] = count($team['members']) + 1;
                return $team;
            }, $teams);
            
            $this->sendJsonResponse(true, "Liste récupérée", [
                'teams' => $teams,
                'count' => count($teams)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * List teams by tournament
     */
    private function listByTournoiAction(): void
    {
        try {
            $id_tournoi = $this->validateId($_GET['id_tournoi'] ?? null);
            $teams = $this->team->listByTournoi($id_tournoi);
            
            $teams = array_map(function($team) {
                $team['members'] = json_decode($team['members'], true) ?: [];
                $team['total_members'] = count($team['members']) + 1;
                return $team;
            }, $teams);
            
            $this->sendJsonResponse(true, "Liste récupérée", [
                'teams' => $teams,
                'count' => count($teams)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * List teams by category
     */
    private function listByCategoryAction(): void
    {
        try {
            $category = $this->sanitizeInput($_GET['category'] ?? '');
            if (empty($category)) {
                throw new Exception("Catégorie non spécifiée");
            }
            
            $teams = $this->team->listByCategory($category);
            
            $teams = array_map(function($team) {
                $team['members'] = json_decode($team['members'], true) ?: [];
                $team['total_members'] = count($team['members']) + 1;
                return $team;
            }, $teams);
            
            $this->sendJsonResponse(true, "Liste récupérée", [
                'teams' => $teams,
                'count' => count($teams)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Search teams
     */
    private function searchAction(): void
    {
        try {
            $keyword = $this->sanitizeInput($_GET['keyword'] ?? '');
            if (empty($keyword)) {
                throw new Exception("Mot-clé de recherche vide");
            }
            
            $teams = $this->team->search($keyword);
            
            $teams = array_map(function($team) {
                $team['members'] = json_decode($team['members'], true) ?: [];
                $team['total_members'] = count($team['members']) + 1;
                return $team;
            }, $teams);
            
            $this->sendJsonResponse(true, "Résultats de recherche", [
                'teams' => $teams,
                'count' => count($teams)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Join a team
     */
    private function joinAction(): void
    {
        try {
            $id_team = $this->validateId($_POST['id_team'] ?? null);
            $memberData = $this->validateMemberData($_POST);
            
            if ($this->team->joinTeam($id_team, $memberData)) {
                $this->logAction('join_team', $id_team);
                $this->sendJsonResponse(true, "Vous avez rejoint l'équipe avec succès");
            } else {
                throw new Exception("Erreur lors de l'ajout à l'équipe");
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Get available tournaments
     */
    private function getTournoisAction(): void
    {
        try {
            $tournois = $this->team->getAvailableTournois();
            $this->sendJsonResponse(true, "Liste des tournois récupérée", [
                'tournois' => $tournois,
                'count' => count($tournois)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Check tag availability
     */
    private function checkTagAction(): void
    {
        try {
            $tag = $this->sanitizeInput($_GET['tag'] ?? '');
            if (empty($tag)) {
                throw new Exception("Tag vide");
            }
            
            if (strlen($tag) < self::MIN_TAG_LENGTH || strlen($tag) > self::MAX_TAG_LENGTH) {
                throw new Exception("Le tag doit contenir entre " . self::MIN_TAG_LENGTH . " et " . self::MAX_TAG_LENGTH . " caractères");
            }
            
            $exclude_id = isset($_GET['exclude_id']) ? $this->validateId($_GET['exclude_id']) : null;
            $available = $this->team->isTagUnique($tag, $exclude_id);
            
            $this->sendJsonResponse(true, "", [
                'available' => $available,
                'tag' => strtoupper($tag)
            ]);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Get statistics
     */
    private function getStatisticsAction(): void
    {
        try {
            $stats = $this->team->getStatistics();
            $this->sendJsonResponse(true, "Statistiques récupérées", $stats);
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    // ============ VALIDATION METHODS ============
    
    /**
     * Validate ID
     */
    private function validateId($id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            throw new Exception("ID invalide");
        }
        return $id;
    }
    
    /**
     * Validate team creation/update data
     */
    private function validateCreateData(array $data): array
    {
        $validated = [];
        
        // Required fields
        $required = [
            'id_tournoi' => FILTER_VALIDATE_INT,
            'team_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'team_tag' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'country' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'leader_name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'leader_email' => FILTER_VALIDATE_EMAIL,
            'leader_phone' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'category' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
        ];
        
        foreach ($required as $field => $filter) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception("Le champ '$field' est requis");
            }
            
            $value = ($filter === FILTER_SANITIZE_FULL_SPECIAL_CHARS)
                ? $this->sanitizeInput($data[$field])
                : filter_var($data[$field], $filter);
            
            if ($value === false) {
                throw new Exception("Le champ '$field' contient une valeur invalide");
            }
            
            $validated[$field] = $value;
        }
        
        // Specific validations
        if (strlen($validated['team_name']) < self::MIN_NAME_LENGTH ||
            strlen($validated['team_name']) > self::MAX_NAME_LENGTH) {
            throw new Exception("Le nom de l'équipe doit contenir entre " . self::MIN_NAME_LENGTH . " et " . self::MAX_NAME_LENGTH . " caractères");
        }
        
        if (strlen($validated['team_tag']) < self::MIN_TAG_LENGTH ||
            strlen($validated['team_tag']) > self::MAX_TAG_LENGTH) {
            throw new Exception("Le tag doit contenir entre " . self::MIN_TAG_LENGTH . " et " . self::MAX_TAG_LENGTH . " caractères");
        }
        
        if (!preg_match(self::PHONE_PATTERN, $validated['leader_phone'])) {
            throw new Exception("Format de téléphone invalide");
        }
        
        return $validated;
    }
    
    /**
     * Validate member data
     */
    private function validateMemberData(array $data): array
    {
        $memberData = [];
        
        // Required fields
        $fields = ['member_name', 'member_email', 'member_phone'];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Informations du membre incomplètes");
            }
        }
        
        // Validate name
        $name = $this->sanitizeInput($data['member_name']);
        if (strlen($name) < self::MIN_NAME_LENGTH) {
            throw new Exception("Le nom doit contenir au moins " . self::MIN_NAME_LENGTH . " caractères");
        }
        $memberData['name'] = $name;
        
        // Validate email
        $email = filter_var($data['member_email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception("Format d'email invalide");
        }
        $memberData['email'] = $email;
        
        // Validate phone
        $phone = $this->sanitizeInput($data['member_phone']);
        if (!preg_match(self::PHONE_PATTERN, $phone)) {
            throw new Exception("Format de téléphone invalide");
        }
        $memberData['phone'] = $phone;
        
        return $memberData;
    }
    
    /**
     * Process members from form
     */
    private function processMembers(array $data): array
    {
        $members = [];
        
        if (isset($data['member_names']) && is_array($data['member_names'])) {
            $count = count($data['member_names']);
            
            // Limit to 4 additional members
            if ($count > 4) {
                throw new Exception("Maximum 4 membres additionnels autorisés");
            }
            
            for ($i = 0; $i < $count; $i++) {
                // Skip empty fields
                if (!empty($data['member_names'][$i]) &&
                    !empty($data['member_emails'][$i]) &&
                    !empty($data['member_phones'][$i])) {
                    
                    // Validate each member
                    $name = $this->sanitizeInput($data['member_names'][$i]);
                    $email = filter_var($data['member_emails'][$i], FILTER_VALIDATE_EMAIL);
                    $phone = $this->sanitizeInput($data['member_phones'][$i]);
                    
                    if (!$email) {
                        throw new Exception("Email invalide pour le membre #" . ($i + 1));
                    }
                    
                    if (!preg_match(self::PHONE_PATTERN, $phone)) {
                        throw new Exception("Téléphone invalide pour le membre #" . ($i + 1));
                    }
                    
                    $members[] = [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone
                    ];
                }
            }
        }
        
        return $members;
    }
    
    // ============ SECURITY METHODS ============
    
    /**
     * Sanitize user input
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token (DISABLED)
     */
    private function validateCsrfToken(): void
    {
        // CSRF validation disabled for development
        // To re-enable, uncomment the code below and ensure frontend sends csrf_token
        
        /*
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            $this->sendJsonResponse(false, "Token de sécurité invalide", null, 403);
            exit;
        }
        */
    }
    
    /**
     * Generate CSRF token (call from view)
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Basic rate limiting
     */
    private function checkRateLimit(): bool
    {
        $key = 'rate_limit_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $limit = 60; // Requests per minute
        $window = 60; // Seconds
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'start' => time()
            ];
            return true;
        }
        
        $elapsed = time() - $_SESSION[$key]['start'];
        
        if ($elapsed > $window) {
            $_SESSION[$key] = [
                'count' => 1,
                'start' => time()
            ];
            return true;
        }
        
        $_SESSION[$key]['count']++;
        return $_SESSION[$key]['count'] <= $limit;
    }
    
    /**
     * Log action
     */
    private function logAction(string $action, ?int $entity_id = null): void
    {
        $log_data = [
            'action' => $action,
            'entity_id' => $entity_id,
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        error_log("TEAM_ACTION: " . json_encode($log_data));
    }
    
    /**
     * Send standardized JSON response
     */
    private function sendJsonResponse(bool $success, string $message, $data = null, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        $response = [
            'success' => $success,
            'message' => $message,
            'timestamp' => time()
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Entry point
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $controller = new TeamController();
    $controller->handleRequest();
}
?>