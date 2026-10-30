<?php
class Database {
    public function connect(): mysqli {
        // Default XAMPP credentials
        $conn = new mysqli("localhost", "root", "", "csit314_db");
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}
