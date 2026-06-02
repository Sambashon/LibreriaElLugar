<?php

class LibreriaDB
{
    protected readonly PDO $pdo;

    public function __construct()
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $dbHost = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $dbPort = getenv('DB_PORT');
        $dbUser = getenv('DB_USER');
        $dbPassword = getenv('DB_PASSWORD');

        if (!$dbHost || !$dbName || !$dbPort || !$dbUser) {
            throw new RuntimeException(
                'Faltan variables de entorno de la base de datos'
            );
        }

        $this->pdo = new PDO(
            sprintf(
                "mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4",
                $dbHost,
                $dbName,
                $dbPort
            ),
            $dbUser,
            $dbPassword,
            $options
        );
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();

        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() > 0;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->rollBack();
        }

        return false;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}