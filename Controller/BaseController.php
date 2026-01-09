<?php
/**
 * BaseController - Common functionality for all controllers
 * Provides database connection, error handling, and common operations
 */

require_once __DIR__ . '/../config.php';

abstract class BaseController
{
    protected $db;
    
    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = config::getConnexion();
    }
    
    /**
     * Execute a database query with error handling
     * 
     * @param callable $callback The database operation to execute
     * @param mixed $defaultReturn Default value to return on error
     * @param string $errorMessage Error message prefix for logging
     * @return mixed The result of the callback or default return value
     */
    protected function executeQuery(callable $callback, $defaultReturn = false, string $errorMessage = "Database error")
    {
        try {
            return $callback($this->db);
        } catch (PDOException $e) {
            error_log("$errorMessage: " . $e->getMessage());
            return $defaultReturn;
        }
    }
    
    /**
     * Prepare and bind pagination parameters to a query
     * 
     * @param PDOStatement $query The prepared statement
     * @param int $limit Number of records per page
     * @param int $offset Starting position
     */
    protected function bindPaginationParams(PDOStatement $query, int $limit, int $offset): void
    {
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    
    /**
     * Send a JSON response (for AJAX requests)
     * 
     * @param bool $success Success status
     * @param mixed $data Data to return
     */
    protected function sendJsonResponse(bool $success, $data = null): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data
        ]);
        exit();
    }
}
?>
