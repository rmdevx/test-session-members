<?php

namespace Rmdevx\TestSessionMembers;

use PDO;

class Db
{
    private PDO $pdo;

    public function __construct(string $dbname, string $user, string $pass)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $this->pdo = new PDO(
            "mysql:host=mysql;dbname=" . $dbname,
            $user,
            $pass,
            $options
        );
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $res = $stmt->execute($params);

        return ($res !== false) ? $stmt->fetchAll() : [];
    }

    public function lastInsertId(): false|string
    {
        return $this->pdo->lastInsertId();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}