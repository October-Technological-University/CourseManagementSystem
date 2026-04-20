<?php
class Database
{
    private $conn;

    public function __construct()
    {
        $env = parse_ini_file(BASE_PATH . '/config/.env');
        if ($env === false) {
            die("Failed to read database configuration.");
        }

        $dbServer = $env['DATABASE_SERVER'] ?? 'localhost';
        $dbUsername = $env['DATABASE_USERNAME'] ?? '';
        $dbPassword = $env['DATABASE_PASSWORD'] ?? '';
        $dbName = $env['DATABASE_NAME'] ?? '';

        $this->conn = new mysqli($dbServer, $dbUsername, $dbPassword);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        if (!empty($dbName) && !$this->conn->select_db($dbName)) {
            if ($this->conn->errno === 1049) {
                $escapedDbName = $this->escapeIdentifier($dbName);
                if (!$this->conn->query("CREATE DATABASE IF NOT EXISTS `$escapedDbName`")) {
                    die("Database creation failed: " . $this->conn->error);
                }

                if (!$this->conn->select_db($dbName)) {
                    die("Database selection failed: " . $this->conn->error);
                }
            } else {
                die("Database selection failed: " . $this->conn->error);
            }
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

    private function escapeIdentifier($identifier)
    {
        return str_replace('`', '``', $identifier);
    }

    // A method DB Connection Testing
    public function testConnection() :mysqli_result{
        return $this->conn->execute_query("SELECT 1;");
    }
}
?>