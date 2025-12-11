<?php
/**
 * Team Handler - API Entry Point with Validation
 * Validates requests before forwarding to TeamController
 * UPDATED VERSION - Added Tournament-Based Team Management
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Autoload dependencies
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../Model/team.php';
require_once __DIR__ . '/../../Controller/teamC.php';

/**
 * Team Validator Class
 */
class TeamValidator
{
    private array $errors = [];
    
    // Validation constants
    private const MIN_NAME_LENGTH = 2;
    private const MAX_NAME_LENGTH = 100;
    private const MIN_TEAM_NAME_LENGTH = 3;
    private const MIN_TAG_LENGTH = 2;
    private const MAX_TAG_LENGTH = 10;
    private const MIN_COUNTRY_LENGTH = 2;
    private const MAX_COUNTRY_LENGTH = 50;
    private const MIN_PHONE_LENGTH = 8;
    private const MAX_PHONE_LENGTH = 20;
    private const MAX_MEMBERS = 4;
    private const MIN_TOTAL_MEMBERS = 1;
    private const MAX_TOTAL_MEMBERS = 5;
    
    // Validation patterns
    private const PATTERN_TEAM_NAME = '/^[\p{L}\p{N}\s\-_]+$/u';
    private const PATTERN_TEAM_TAG = '/^[A-Z0-9]+$/';
    private const PATTERN_PERSON_NAME = '/^[\p{L}\s\-\']+$/u';
    private const PATTERN_EMAIL = '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i';
    private const PATTERN_PHONE = '/^[\d\s\+\-\(\)\.]+$/';
    
    // Valid categories (lowercase for comparison)
    private const VALID_CATEGORIES = ['amateur', 'semi-pro', 'professional', 'junior', 'senior'];
    
    // ============ FORM VALIDATION METHODS ============
    
    /**
     * Validate creation form
     */
    public function validateCreateForm(array $data): bool
    {
        $this->errors = [];
        
        // Individual validations
        $this->validateIdTournoi($data['id_tournoi'] ?? '');
        $this->validateTeamName($data['team_name'] ?? '');
        $this->validateTeamTag($data['team_tag'] ?? '');
        $this->validateCountry($data['country'] ?? '');
        $this->validateLeaderName($data['leader_name'] ?? '');
        $this->validateLeaderEmail($data['leader_email'] ?? '');
        $this->validateLeaderPhone($data['leader_phone'] ?? '');
        $this->validateCategory($data['category'] ?? '');
        
        // Validate additional members
        $this->validateMembers($data);
        
        // Additional validation: Check if tournament is still open
        if (!empty($data['id_tournoi'])) {
            $this->validateTournamentStatus($data['id_tournoi']);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate update form
     */
    public function validateUpdateForm(array $data): bool
    {
        $this->errors = [];
        
        // Validate team ID for update
        $this->validateTeamId($data['id_team'] ?? '');
        
        // Other validations same as creation
        $this->validateIdTournoi($data['id_tournoi'] ?? '');
        $this->validateTeamName($data['team_name'] ?? '');
        $this->validateTeamTag($data['team_tag'] ?? '');
        $this->validateCountry($data['country'] ?? '');
        $this->validateLeaderName($data['leader_name'] ?? '');
        $this->validateLeaderEmail($data['leader_email'] ?? '');
        $this->validateLeaderPhone($data['leader_phone'] ?? '');
        $this->validateCategory($data['category'] ?? '');
        $this->validateMembers($data);
        
        return empty($this->errors);
    }
    
    /**
     * Validate join team form
     */
    public function validateJoinForm(array $data): bool
    {
        $this->errors = [];
        
        // Validate team ID
        $this->validateTeamId($data['id_team'] ?? '');
        
        // Validate member data
        $this->validateMemberName($data['member_name'] ?? '', 'member_name');
        $this->validateEmail($data['member_email'] ?? '', 'member_email');
        $this->validatePhone($data['member_phone'] ?? '', 'member_phone');
        
        return empty($this->errors);
    }
    
    /**
     * Validate delete request
     */
    public function validateDeleteRequest(array $data): bool
    {
        $this->errors = [];
        $this->validateTeamId($data['id_team'] ?? '');
        return empty($this->errors);
    }
    
    /**
     * Validate read request
     */
    public function validateReadRequest(array $data): bool
    {
        $this->errors = [];
        $this->validateTeamId($data['id_team'] ?? '');
        return empty($this->errors);
    }
    
    // ============ INDIVIDUAL VALIDATIONS ============
    
    /**
     * Validate team ID
     */
    private function validateTeamId($value): void
    {
        if (empty($value)) {
            $this->errors['id_team'] = "L'ID de l'équipe est requis";
            return;
        }
        
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            $this->errors['id_team'] = "ID d'équipe invalide";
        }
    }
    
    /**
     * Validate tournament ID
     */
    private function validateIdTournoi($value): void
    {
        if (empty($value)) {
            $this->errors['id_tournoi'] = "Veuillez sélectionner un tournoi";
            return;
        }
        
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) {
            $this->errors['id_tournoi'] = "ID tournoi invalide";
        }
    }
    
    /**
     * Validate tournament status (check if it's not closed)
     */
    private function validateTournamentStatus($tournoiId): void
    {
        try {
            require_once __DIR__ . '/../../Controller/TournoiC.php';
            $tournoiController = new TournoiC();
            $tournoi = $tournoiController->getTournoiById((int)$tournoiId);
            
            if (!$tournoi) {
                $this->errors['id_tournoi'] = "Ce tournoi n'existe pas";
                return;
            }
            
            // Check if tournament has ended
            $today = new DateTime();
            $endDate = new DateTime($tournoi->getDateFin());
            
            if ($today > $endDate) {
                $this->errors['id_tournoi'] = "Ce tournoi est terminé et n'accepte plus d'inscriptions";
            }
        } catch (Exception $e) {
            error_log("Error validating tournament status: " . $e->getMessage());
            $this->errors['id_tournoi'] = "Erreur lors de la vérification du tournoi";
        }
    }
    
    /**
     * Validate team name
     */
    private function validateTeamName($value): void
    {
        $value = trim($value);
        if (empty($value)) {
            $this->errors['team_name'] = "Le nom de l'équipe est requis";
            return;
        }
        
        $length = mb_strlen($value);
        if ($length < self::MIN_TEAM_NAME_LENGTH) {
            $this->errors['team_name'] = "Le nom doit contenir au moins " . self::MIN_TEAM_NAME_LENGTH . " caractères";
            return;
        }
        
        if ($length > self::MAX_NAME_LENGTH) {
            $this->errors['team_name'] = "Le nom ne peut pas dépasser " . self::MAX_NAME_LENGTH . " caractères";
            return;
        }
        
        if (!preg_match(self::PATTERN_TEAM_NAME, $value)) {
            $this->errors['team_name'] = "Le nom contient des caractères non autorisés";
        }
    }
    
    /**
     * Validate team tag
     */
    private function validateTeamTag($value): void
    {
        $value = trim(strtoupper($value));
        if (empty($value)) {
            $this->errors['team_tag'] = "Le tag de l'équipe est requis";
            return;
        }
        
        $length = strlen($value);
        if ($length < self::MIN_TAG_LENGTH) {
            $this->errors['team_tag'] = "Le tag doit contenir au moins " . self::MIN_TAG_LENGTH . " caractères";
            return;
        }
        
        if ($length > self::MAX_TAG_LENGTH) {
            $this->errors['team_tag'] = "Le tag ne peut pas dépasser " . self::MAX_TAG_LENGTH . " caractères";
            return;
        }
        
        if (!preg_match(self::PATTERN_TEAM_TAG, $value)) {
            $this->errors['team_tag'] = "Le tag ne peut contenir que des lettres majuscules et des chiffres";
        }
    }
    
    /**
     * Validate country
     */
    private function validateCountry($value): void
    {
        $value = trim($value);
        if (empty($value)) {
            $this->errors['country'] = "Le pays est requis";
            return;
        }
        
        $length = mb_strlen($value);
        if ($length < self::MIN_COUNTRY_LENGTH || $length > self::MAX_COUNTRY_LENGTH) {
            $this->errors['country'] = "Le pays doit contenir entre " . self::MIN_COUNTRY_LENGTH . " et " . self::MAX_COUNTRY_LENGTH . " caractères";
        }
    }
    
    /**
     * Validate leader name
     */
    private function validateLeaderName($value): void
    {
        $this->validatePersonName($value, 'leader_name', 'Le nom du leader');
    }
    
    /**
     * Validate generic person name
     */
    private function validatePersonName($value, string $fieldName, string $label): void
    {
        $value = trim($value);
        if (empty($value)) {
            $this->errors[$fieldName] = "$label est requis";
            return;
        }
        
        $length = mb_strlen($value);
        if ($length < self::MIN_NAME_LENGTH) {
            $this->errors[$fieldName] = "$label doit contenir au moins " . self::MIN_NAME_LENGTH . " caractères";
            return;
        }
        
        if ($length > self::MAX_NAME_LENGTH) {
            $this->errors[$fieldName] = "$label ne peut pas dépasser " . self::MAX_NAME_LENGTH . " caractères";
            return;
        }
        
        if (!preg_match(self::PATTERN_PERSON_NAME, $value)) {
            $this->errors[$fieldName] = "$label contient des caractères invalides";
        }
    }
    
    /**
     * Validate leader email
     */
    private function validateLeaderEmail($value): void
    {
        $this->validateEmail($value, 'leader_email');
    }
    
    /**
     * Validate generic email
     */
    private function validateEmail($value, string $fieldName): void
    {
        $value = trim(strtolower($value));
        if (empty($value)) {
            $this->errors[$fieldName] = "L'email est requis";
            return;
        }
        
        // PHP standard validation
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = "Format d'email invalide";
            return;
        }
        
        // Additional regex validation
        if (!preg_match(self::PATTERN_EMAIL, $value)) {
            $this->errors[$fieldName] = "Format d'email invalide";
            return;
        }
        
        // Check length (RFC 5321)
        if (strlen($value) > 254) {
            $this->errors[$fieldName] = "L'email est trop long";
            return;
        }
        
        // Verify domain part
        $parts = explode('@', $value);
        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            $this->errors[$fieldName] = "Format d'email invalide";
            return;
        }
        
        // Domain must contain at least one dot
        if (strpos($parts[1], '.') === false) {
            $this->errors[$fieldName] = "Le domaine de l'email doit contenir un point";
        }
    }
    
    /**
     * Validate leader phone
     */
    private function validateLeaderPhone($value): void
    {
        $this->validatePhone($value, 'leader_phone');
    }
    
    /**
     * Validate generic phone
     */
    private function validatePhone($value, string $fieldName): void
    {
        $value = trim($value);
        if (empty($value)) {
            $this->errors[$fieldName] = "Le numéro de téléphone est requis";
            return;
        }
        
        // Count only digits for length
        $digitsOnly = preg_replace('/[^0-9]/', '', $value);
        $digitCount = strlen($digitsOnly);
        
        if ($digitCount < self::MIN_PHONE_LENGTH) {
            $this->errors[$fieldName] = "Le numéro doit contenir au moins " . self::MIN_PHONE_LENGTH . " chiffres";
            return;
        }
        
        if ($digitCount > self::MAX_PHONE_LENGTH) {
            $this->errors[$fieldName] = "Le numéro ne peut pas dépasser " . self::MAX_PHONE_LENGTH . " chiffres";
            return;
        }
        
        // Validate format with allowed characters
        if (!preg_match(self::PATTERN_PHONE, $value)) {
            $this->errors[$fieldName] = "Format de téléphone invalide";
        }
    }
    
    /**
     * Validate category
     */
    private function validateCategory($value): void
    {
        $value = trim(strtolower($value));
        if (empty($value)) {
            $this->errors['category'] = "La catégorie est requise";
            return;
        }
        
        if (!in_array($value, self::VALID_CATEGORIES, true)) {
            $this->errors['category'] = "Catégorie invalide. Valeurs autorisées : " . implode(', ', self::VALID_CATEGORIES);
        }
    }
    
    /**
     * Validate member name
     */
    private function validateMemberName($value, string $fieldName): void
    {
        $this->validatePersonName($value, $fieldName, 'Le nom du membre');
    }
    
    /**
     * Validate additional members
     */
    private function validateMembers(array $data): void
    {
        // No additional members? That's OK (just the leader)
        if (!isset($data['member_names']) || !is_array($data['member_names'])) {
            return;
        }
        
        $memberCount = 0;
        $emails = [];
        
        // Add leader email to check for duplicates
        if (!empty($data['leader_email'])) {
            $emails[] = strtolower(trim($data['leader_email']));
        }
        
        // Iterate through all members
        $totalSlots = count($data['member_names']);
        for ($i = 0; $i < $totalSlots; $i++) {
            $name = trim($data['member_names'][$i] ?? '');
            $email = trim($data['member_emails'][$i] ?? '');
            $phone = trim($data['member_phones'][$i] ?? '');
            
            // Check if any field is filled
            $hasAnyData = !empty($name) || !empty($email) || !empty($phone);
            
            if ($hasAnyData) {
                $memberCount++;
                $memberLabel = "Membre #" . ($i + 1);
                
                // If one field is filled, all must be filled
                if (empty($name)) {
                    $this->errors["member_names[$i]"] = "Nom du $memberLabel requis";
                } else {
                    // Full name validation
                    $length = mb_strlen($name);
                    if ($length < self::MIN_NAME_LENGTH) {
                        $this->errors["member_names[$i]"] = "Nom du $memberLabel trop court (min. " . self::MIN_NAME_LENGTH . " caractères)";
                    } elseif ($length > self::MAX_NAME_LENGTH) {
                        $this->errors["member_names[$i]"] = "Nom du $memberLabel trop long (max. " . self::MAX_NAME_LENGTH . " caractères)";
                    } elseif (!preg_match(self::PATTERN_PERSON_NAME, $name)) {
                        $this->errors["member_names[$i]"] = "Nom du $memberLabel contient des caractères invalides";
                    }
                }
                
                if (empty($email)) {
                    $this->errors["member_emails[$i]"] = "Email du $memberLabel requis";
                } else {
                    // Email validation
                    $emailLower = strtolower($email);
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match(self::PATTERN_EMAIL, $emailLower)) {
                        $this->errors["member_emails[$i]"] = "Email du $memberLabel invalide";
                    } elseif (in_array($emailLower, $emails, true)) {
                        $this->errors["member_emails[$i]"] = "Cet email est déjà utilisé dans l'équipe";
                    } else {
                        $emails[] = $emailLower;
                    }
                }
                
                if (empty($phone)) {
                    $this->errors["member_phones[$i]"] = "Téléphone du $memberLabel requis";
                } else {
                    // Phone validation
                    $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
                    $digitCount = strlen($digitsOnly);
                    if ($digitCount < self::MIN_PHONE_LENGTH || $digitCount > self::MAX_PHONE_LENGTH) {
                        $this->errors["member_phones[$i]"] = "Téléphone du $memberLabel invalide (8-20 chiffres)";
                    } elseif (!preg_match(self::PATTERN_PHONE, $phone)) {
                        $this->errors["member_phones[$i]"] = "Format de téléphone du $memberLabel invalide";
                    }
                }
            }
        }
        
        // Check total member count
        $totalMembers = $memberCount + 1; // +1 for leader
        if ($totalMembers < self::MIN_TOTAL_MEMBERS) {
            $this->errors['members'] = "Une équipe doit avoir au moins " . self::MIN_TOTAL_MEMBERS . " membre (le leader)";
        }
        
        if ($totalMembers > self::MAX_TOTAL_MEMBERS) {
            $this->errors['members'] = "Une équipe ne peut pas avoir plus de " . self::MAX_TOTAL_MEMBERS . " membres (leader inclus). Vous avez actuellement $totalMembers membres.";
        }
        
        if ($memberCount > self::MAX_MEMBERS) {
            $this->errors['members'] = "Vous ne pouvez ajouter que " . self::MAX_MEMBERS . " membres additionnels (hors leader)";
        }
    }
    
    // ============ ERROR METHODS ============
    
    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError(): string
    {
        return !empty($this->errors) ? reset($this->errors) : '';
    }
    
    /**
     * Check if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }
}

/**
 * Request Handler Class
 */
class RequestHandler
{
    private TeamValidator $validator;
    private array $allowedActions = [
        'create', 'read', 'update', 'delete',
        'list', 'listByTournoi', 'listByCategory', 'search',
        'join', 'getTournois', 'checkTag', 'getStatistics'
    ];
    
    public function __construct()
    {
        $this->validator = new TeamValidator();
    }
    
    /**
     * Handle incoming request
     */
    public function handle(): void
    {
        try {
            // Validate HTTP method
            $this->validateHttpMethod();
            
            // Get action
            $action = $this->getAction();
            
            // Validate action
            $this->validateAction($action);
            
            // Validate request data based on action
            $this->validateRequestData($action);
            
            // If everything is OK, forward to controller
            $this->forwardToController();
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }
    
    /**
     * Validate HTTP method
     */
    private function validateHttpMethod(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        
        // POST required for modification actions
        $postActions = ['create', 'update', 'delete', 'join'];
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        if (in_array($action, $postActions) && $method !== 'POST') {
            throw new Exception("Méthode HTTP non autorisée. POST requis pour cette action.");
        }
    }
    
    /**
     * Get action from request
     */
    private function getAction(): string
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        if (empty($action)) {
            throw new Exception("Action non spécifiée");
        }
        
        return trim($action);
    }
    
    /**
     * Validate action
     */
    private function validateAction(string $action): void
    {
        if (!in_array($action, $this->allowedActions, true)) {
            throw new Exception("Action '$action' non autorisée");
        }
    }
    
    /**
     * Validate request data based on action
     */
    private function validateRequestData(string $action): void
    {
        $isValid = false;
        
        switch ($action) {
            case 'create':
                $isValid = $this->validator->validateCreateForm($_POST);
                break;
            
            case 'update':
                $isValid = $this->validator->validateUpdateForm($_POST);
                break;
            
            case 'join':
                $isValid = $this->validator->validateJoinForm($_POST);
                break;
            
            case 'delete':
                $isValid = $this->validator->validateDeleteRequest($_POST);
                break;
            
            case 'read':
                $isValid = $this->validator->validateReadRequest($_GET);
                break;
            
            case 'list':
            case 'getTournois':
            case 'getStatistics':
                // These actions require no validation
                $isValid = true;
                break;
            
            case 'checkTag':
                // Validate tag parameter
                if (empty($_GET['tag'])) {
                    throw new Exception("Tag non spécifié");
                }
                $isValid = true;
                break;
            
            case 'listByTournoi':
                // Validate tournament ID
                if (empty($_GET['id_tournoi'])) {
                    throw new Exception("ID tournoi manquant");
                }
                $tournoiId = filter_var($_GET['id_tournoi'], FILTER_VALIDATE_INT);
                if ($tournoiId === false || $tournoiId <= 0) {
                    throw new Exception("ID tournoi invalide");
                }
                $isValid = true;
                break;
            
            case 'listByCategory':
                // Validate category
                if (empty($_GET['category'])) {
                    throw new Exception("Catégorie non spécifiée");
                }
                $isValid = true;
                break;
            
            case 'search':
                // Validate search keyword
                if (empty($_GET['keyword'])) {
                    throw new Exception("Mot-clé de recherche vide");
                }
                if (strlen(trim($_GET['keyword'])) < 2) {
                    throw new Exception("Le mot-clé doit contenir au moins 2 caractères");
                }
                $isValid = true;
                break;
            
            default:
                throw new Exception("Action non reconnue pour la validation");
        }
        
        if (!$isValid) {
            $this->sendValidationErrorResponse();
        }
    }
    
    /**
     * Forward to controller
     */
    private function forwardToController(): void
    {
        $controller = new TeamController();
        $controller->handleRequest();
    }
    
    /**
     * Send validation error response
     */
    private function sendValidationErrorResponse(): void
    {
        http_response_code(422); // Unprocessable Entity
        $response = [
            'success' => false,
            'message' => $this->validator->getFirstError(),
            'errors' => $this->validator->getErrors(),
            'error_count' => $this->validator->getErrorCount(),
            'timestamp' => time()
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send generic error response
     */
    private function sendErrorResponse(string $message, int $httpCode = 400): void
    {
        http_response_code($httpCode);
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => time()
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// ============ ENTRY POINT ============

try {
    $handler = new RequestHandler();
    $handler->handle();
} catch (Throwable $e) {
    // Catch all unhandled errors
    error_log("Unhandled error in team_handler: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur serveur s\'est produite',
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}
?>