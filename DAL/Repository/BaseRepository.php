<?php
require_once BASE_PATH . 'DAL/Database/DBContext.php';

abstract class BaseRepository
{
    protected $conn;
    protected $table;

    public function __construct()
    {
        $this->conn = DBContext::getInstance()->getConnection();
    }

    /**
     * Get connection for direct query execution
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Execute a query and return results
     */
    protected function executeQuery($sql)
    {
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result;
    }

    /**
     * Execute a prepared statement
     */
    protected function executePreparedStatement($sql, $types, $params)
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Get last inserted ID
     */
    protected function getLastInsertId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Get affected rows
     */
    protected function getAffectedRows()
    {
        return $this->conn->affected_rows;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->conn->rollback();
    }

    /**
     * Escape string for safe SQL queries
     */
    protected function escapeString($str)
    {
        return $this->conn->real_escape_string($str);
    }
}
?>