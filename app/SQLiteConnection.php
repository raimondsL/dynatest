<?php

namespace App;

class SQLiteConnection
{
    public function __construct(
        private $pdo = null
    )
    {
        if (!$this->pdo) {
            $this->pdo = new \PDO("sqlite:" . Config::PATH_TO_SQLITE_FILE);
        }
    }

    public function prepare(string $sql): mixed
    {
        return $this->pdo->prepare($sql);
    }

    public function lastInsertId(): mixed
    {
        return $this->pdo->lastInsertId();
    }
}