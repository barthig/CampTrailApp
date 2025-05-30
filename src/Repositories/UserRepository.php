<?php

declare(strict_types=1);

namespace src\Repositories;

use PDO;

class UserRepository
{
    private PDO $db;

    /**
     * @param PDO $db 
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $email
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM vw_user_credentials WHERE email = :email LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * @param string $username
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT * FROM vw_user_credentials WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM vw_user_profile WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * @param array<string, mixed> $data
     * @return int Inserted user ID
     * @throws \InvalidArgumentException
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :role LIMIT 1');
        $stmt->execute(['role' => $data['role']]);
        $roleId = $stmt->fetchColumn();
        if ($roleId === false) {
            throw new \InvalidArgumentException("Unknown role: {$data['role']}");
        }

        $sql = '
            INSERT INTO users
              (email, username, password_hash, first_name, last_name, bio, role_id)
            VALUES
              (:email, :username, :password_hash, :first_name, :last_name, :bio, :role_id)
            RETURNING id
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email'         => $data['email'],
            'username'      => $data['username'],
            'password_hash' => $data['password_hash'],
            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name']  ?? null,
            'bio'           => $data['bio']        ?? null,
            'role_id'       => (int)$roleId,
        ]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['email'])) {
            $fields[]        = 'email = :email';
            $params['email'] = $data['email'];
        }
        if (isset($data['username'])) {
            $fields[]           = 'username = :username';
            $params['username'] = $data['username'];
        }
        if (isset($data['password_hash'])) {
            $fields[]                = 'password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }
        if (isset($data['role'])) {
            $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :role LIMIT 1');
            $stmt->execute(['role' => $data['role']]);
            $roleId = $stmt->fetchColumn();
            if ($roleId === false) {
                throw new \InvalidArgumentException("Unknown role: {$data['role']}");
            }
            $fields[]          = 'role_id = :role_id';
            $params['role_id'] = (int)$roleId;
        }
        if (isset($data['first_name'])) {
            $fields[]             = 'first_name = :first_name';
            $params['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[]            = 'last_name = :last_name';
            $params['last_name'] = $data['last_name'];
        }
        if (array_key_exists('bio', $data)) {
            $fields[]      = 'bio = :bio';
            $params['bio'] = $data['bio'];
        }
        if (array_key_exists('notify_services', $data)) {
            $fields[]                   = 'notify_services = :notify_services';
            $params['notify_services']  = $data['notify_services'] ? 1 : 0;
        }
        if (array_key_exists('notify_routes', $data)) {
            $fields[]                 = 'notify_routes = :notify_routes';
            $params['notify_routes']  = $data['notify_routes'] ? 1 : 0;
        }
        if (array_key_exists('notify_destinations', $data)) {
            $fields[]                      = 'notify_destinations = :notify_destinations';
            $params['notify_destinations'] = $data['notify_destinations'] ? 1 : 0;
        }

        if (empty($fields)) {
            return;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }


    public function getPasswordHashById(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $hash = $stmt->fetchColumn();
        return $hash !== false ? $hash : null;
    }
    public function updateAvatar(int $userId, string $avatarPath): void
    {
        $stmt = $this->db->prepare('
            UPDATE users
            SET avatar = :avatar
            WHERE id = :id
        ');
        $stmt->execute([
            'avatar' => $avatarPath,
            'id'     => $userId,
        ]);
    }

    public function findAllWithRoles(): array
    {
        $sql = <<<SQL
SELECT u.id,
       u.username,
       u.first_name,
       u.last_name,
       u.email,
       r.name AS role_name
  FROM users u
  JOIN roles r ON u.role_id = r.id
 ORDER BY u.username
SQL;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
