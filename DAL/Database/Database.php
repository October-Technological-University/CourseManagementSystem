<?php
class Database
{
    private $conn;

    public function __construct()
    {
        $dbServer = $_ENV['DATABASE_SERVER'] ?? 'localhost';
        $dbUsername = $_ENV['DATABASE_USERNAME'] ?? '';
        $dbPassword = $_ENV['DATABASE_PASSWORD'] ?? '';
        $dbName = $_ENV['DATABASE_NAME'] ?? '';

        // 1. Initialize mysqli
        $this->conn = mysqli_init();

        if (!$this->conn) {
            die("mysqli_init failed");
        }

        // 2. Tell mysqli to use SSL (The parameters are: key, cert, ca, capath, cipher)
        // For Azure, passing NULLs is often enough to initiate a basic secure handshake
        $this->conn->ssl_set(NULL, NULL, '/var/www/ssl/DigiCertGlobalRootG2.crt.pem', NULL, NULL);

        // 3. Connect using real_connect
        // The flag MYSQLI_CLIENT_SSL is the key here
        $success = $this->conn->real_connect($dbServer, $dbUsername, $dbPassword, $dbName, 3306, NULL, MYSQLI_CLIENT_SSL);

        if (!$success) {
            die("Connect Error (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
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
    public function testConnection(): mysqli_result
    {
        return $this->conn->execute_query("SELECT 1;");
    }
}
?>