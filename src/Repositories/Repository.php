<?php

declare(strict_types=1);

namespace src\Repositories;

use PDO;

abstract class Repository
{
    protected PDO $db;

    /**
     * @param PDO $db 
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}
