<?php
// config/database.php

class Database
{
    private $host = "localhost";
    private $db_name = "influencerDb";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Database Connection Failed",
                "error" => $e->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
