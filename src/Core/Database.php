<?php
declare(strict_types=1);

namespace src\Core;

use PDO;
use PDOException;

class Database
{
    private string $host;
    private string $port;
    private string $dbName;
    private string $user;
    private string $password;
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: 'db';
        $this->port     = getenv('DB_PORT') ?: '5432';
        $this->dbName   = getenv('DB_NAME') ?: 'kamper_app';
        $this->user     = getenv('DB_USER') ?: 'postgres';
        $this->password = getenv('DB_PASSWORD') ?: 'password';
    }

    public function connect(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;options=--client_encoding=UTF8',
                $this->host,
                $this->port,
                $this->dbName
            );

            try {
                $this->pdo = new PDO($dsn, $this->user, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                 $this->pdo->exec("SET client_encoding TO 'UTF8'");
            } catch (PDOException $e) {
                throw new PDOException(
                    'Nie udało się połączyć z bazą danych.',
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return $this->pdo;
    }
}
