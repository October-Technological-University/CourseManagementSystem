<?php
require_once BASE_PATH . 'DAL/Repository/BaseRepository.php';
require_once BASE_PATH . 'DAL/Entities/User.php';

class UserRepository extends BaseRepository
{
    protected $table = 'users';

    /**
     * Create a new user
     */
    public function create(User $user)
    {
        $sql = "INSERT INTO `{$this->table}` (email, password, first_name, last_name, role, profile_picture_id)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->executePreparedStatement($sql, 'sssssi', [
            $user->getEmail(),
            $user->getPassword(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRole(),
            $user->getProfilePictureId()
        ]);

        $stmt->close();
        return $this->getLastInsertId();
    }

    /**
     * Get user by ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$id]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE email = ?";
        $stmt = $this->executePreparedStatement($sql, 's', [$email]);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    /**
     * Get all users
     */
    public function getAll()
    {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC";
        $result = $this->executeQuery($sql);
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $this->mapRowToUser($row);
        }

        return $users;
    }

    /**
     * Get users by role
     */
    public function getByRole($role)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->executePreparedStatement($sql, 's', [$role]);
        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $this->mapRowToUser($row);
        }

        $stmt->close();
        return $users;
    }

    /**
     * Get all teachers
     */
    public function getAllTeachers()
    {
        return $this->getByRole('teacher');
    }

    /**
     * Get all students
     */
    public function getAllStudents()
    {
        return $this->getByRole('student');
    }

    /**
     * Update user
     */
    public function update(User $user)
    {
        $sql = "UPDATE `{$this->table}` 
                SET email = ?, password = ?, first_name = ?, last_name = ?, role = ?, profile_picture_id = ?
                WHERE id = ?";

        $stmt = $this->executePreparedStatement($sql, 'sssssii', [
            $user->getEmail(),
            $user->getPassword(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRole(),
            $user->getProfilePictureId(),
            $user->getId()
        ]);

        $stmt->close();
        return $this->getAffectedRows();
    }

    /**
     * Update user profile picture
     */
    public function updateProfilePicture($user_id, $picture_id)
    {
        $sql = "UPDATE `{$this->table}` SET profile_picture_id = ? WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$picture_id, $user_id]);
        $stmt->close();
        return $this->getAffectedRows();
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->executePreparedStatement($sql, 'i', [$id]);
        $stmt->close();
        return $this->getAffectedRows();
    }

    /**
     * Check if email exists
     */
    public function emailExists($email)
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE email = ?";
        $stmt = $this->executePreparedStatement($sql, 's', [$email]);
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /**
     * Get total user count
     */
    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as count FROM `{$this->table}`";
        $result = $this->executeQuery($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    /**
     * Get users with pagination
     */
    public function getPaginated($page = 1, $pageSize = 10)
    {
        $offset = ($page - 1) * $pageSize;
        $sql = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT ?, ?";
        $stmt = $this->executePreparedStatement($sql, 'ii', [$offset, $pageSize]);
        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $this->mapRowToUser($row);
        }

        $stmt->close();
        return $users;
    }

    /**
     * Map database row to User entity
     */
    private function mapRowToUser($row)
    {
        $user = new User(
            $row['email'],
            $row['password'],
            $row['first_name'],
            $row['last_name'],
            $row['role'],
            $row['profile_picture_id']
        );

        $user->setId($row['id']);
        $user->setCreatedAt($row['created_at']);

        return $user;
    }
}
?>