<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $title;
    public $description;
    public $license_type;
    public $price;
    public $duration;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, license_type=:license_type, 
                      price=:price, duration=:duration, status='active'";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->license_type = htmlspecialchars(strip_tags($this->license_type));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->duration = htmlspecialchars(strip_tags($this->duration));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":license_type", $this->license_type);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":duration", $this->duration);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, license_type=:license_type, 
                      price=:price, duration=:duration, status=:status
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->license_type = htmlspecialchars(strip_tags($this->license_type));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->duration = htmlspecialchars(strip_tags($this->duration));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":license_type", $this->license_type);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":duration", $this->duration);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->license_type = $row['license_type'];
            $this->price = $row['price'];
            $this->duration = $row['duration'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
}
?>