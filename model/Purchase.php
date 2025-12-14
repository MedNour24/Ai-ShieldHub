<?php
class Purchase {
    private $conn;
    private $table_name = "purchases";

    public $id;
    public $course_id;
    public $user_id;
    public $purchase_date;
    public $amount;
    public $status;
    public $payment_method;
    public $transaction_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new purchase
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET course_id=:course_id, user_id=:user_id, purchase_date=:purchase_date, 
                      amount=:amount, status=:status, payment_method=:payment_method, 
                      transaction_id=:transaction_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));

        // Bind parameters
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":transaction_id", $this->transaction_id);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all purchases with course information
    public function read() {
        $query = "SELECT 
                    p.*, 
                    c.title as course_title, 
                    c.license_type
                  FROM " . $this->table_name . " p
                  LEFT JOIN courses c ON p.course_id = c.id
                  ORDER BY p.purchase_date DESC, p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read purchases by specific user
    public function readByUser($user_id) {
        $query = "SELECT 
                    p.*, 
                    c.title as course_title, 
                    c.description as course_description, 
                    c.duration as course_duration,
                    c.license_type
                  FROM " . $this->table_name . " p
                  LEFT JOIN courses c ON p.course_id = c.id 
                  WHERE p.user_id = :user_id
                  ORDER BY p.purchase_date DESC, p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Read single purchase by ID
    public function readOne() {
        $query = "SELECT 
                    p.*, 
                    c.title as course_title, 
                    c.description as course_description, 
                    c.duration as course_duration,
                    c.license_type
                  FROM " . $this->table_name . " p
                  LEFT JOIN courses c ON p.course_id = c.id
                  WHERE p.id = :id 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Set object properties from row data
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->user_id = $row['user_id'];
            $this->purchase_date = $row['purchase_date'];
            $this->amount = $row['amount'];
            $this->status = $row['status'];
            $this->payment_method = $row['payment_method'];
            $this->transaction_id = $row['transaction_id'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Check if user has already purchased a course
    public function userHasPurchased($user_id, $course_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  AND course_id = :course_id 
                  AND status = 'completed' 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Update purchase status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, 
                      transaction_id = :transaction_id,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get overall purchase statistics
    public function getPurchaseStats() {
        $query = "SELECT 
                    COUNT(*) as total_purchases,
                    COALESCE(SUM(amount), 0) as total_revenue,
                    COALESCE(AVG(amount), 0) as average_purchase,
                    COUNT(DISTINCT user_id) as unique_customers
                  FROM " . $this->table_name . " 
                  WHERE status = 'completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ensure we return proper values even if no purchases exist
        if (!$result) {
            return [
                'total_purchases' => 0,
                'total_revenue' => 0,
                'average_purchase' => 0,
                'unique_customers' => 0
            ];
        }
        
        return $result;
    }

    // Get purchase statistics by course
    public function getCoursePurchaseStats() {
        $query = "SELECT 
                    c.id as course_id,
                    c.title as course_title,
                    c.license_type,
                    c.price,
                    c.status as course_status,
                    COUNT(p.id) as purchase_count,
                    COALESCE(SUM(p.amount), 0) as total_revenue
                  FROM courses c
                  LEFT JOIN " . $this->table_name . " p ON c.id = p.course_id AND p.status = 'completed'
                  GROUP BY c.id, c.title, c.license_type, c.price, c.status
                  ORDER BY purchase_count DESC, total_revenue DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Get purchases by date range (for reporting)
    public function getPurchasesByDateRange($start_date, $end_date) {
        $query = "SELECT 
                    p.*, 
                    c.title as course_title
                  FROM " . $this->table_name . " p
                  LEFT JOIN courses c ON p.course_id = c.id
                  WHERE p.purchase_date BETWEEN :start_date AND :end_date
                  ORDER BY p.purchase_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();
        
        return $stmt;
    }

    // Get revenue statistics by month
    public function getMonthlyRevenue() {
        $query = "SELECT 
                    YEAR(purchase_date) as year,
                    MONTH(purchase_date) as month,
                    COUNT(*) as purchase_count,
                    SUM(amount) as monthly_revenue
                  FROM " . $this->table_name . " 
                  WHERE status = 'completed'
                  GROUP BY YEAR(purchase_date), MONTH(purchase_date)
                  ORDER BY year DESC, month DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Delete purchase (admin function)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get total purchases count (with optional status filter)
    public function getTotalCount($status = null) {
        $query = "SELECT COUNT(*) as total_count FROM " . $this->table_name;
        
        if ($status) {
            $query .= " WHERE status = :status";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total_count'] ?? 0;
    }

    // Get purchases by status
    public function readByStatus($status) {
        $query = "SELECT 
                    p.*, 
                    c.title as course_title
                  FROM " . $this->table_name . " p
                  LEFT JOIN courses c ON p.course_id = c.id
                  WHERE p.status = :status
                  ORDER BY p.purchase_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        
        return $stmt;
    }

    // Process refund
    public function processRefund($refund_reason = '') {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'refunded', 
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>