<?php

class Database {
    private $host;
    private $db_name;
    private $user;
    private $pass;
    private $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
    }

    public function connect() {
        // Create connection
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);

        // Check connection
        if ($this->conn->connect_error) {
            error_log("Database Connection Error [" . date('Y-m-d H:i:s') . "]: " . $this->conn->connect_error);
            die("Database connection error. Please try again later.");
        }

        // Set charset to UTF-8
        $this->conn->set_charset("utf8mb4");

        // Set timezone to UTC (critical for token expiry comparisons)
        $this->conn->query("SET time_zone='+00:00'");

        return $this->conn;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getLastError() {
        return $this->conn->error ?? 'Unknown error';
    }

    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
}

// Initialize database connection
$db = new Database();
$conn = $db->connect();

// If connection failed, $conn will be null and script will die above
if (!$conn) {
    die("Failed to establish database connection.");
}
?>

