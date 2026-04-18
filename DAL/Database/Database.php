<?php
class Database
{
    private $conn;

    public function __construct()
    {
        $env = parse_ini_file(BASE_PATH . '/config/.env');
        $this->conn = new mysqli($env['DATABASE_SERVER'], $env['DATABASE_USERNAME'], $env['DATABASE_PASSWORD']);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function __destruct()
    {
        $this->conn->close();
    }

    // A method DB Connection Testing
    public function testConnection() :mysqli_result{
        return $this->conn->execute_query("SELECT 1;");
    }
}
?>