<?php
require_once BASE_PATH . 'DAL/Database/Database.php';
class DBContext
{
    private static $instance = null;
    private $database;

    private function __construct()
    {
        $this->database = new Database();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DBContext();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->database->getConnection();
    }
}
?>