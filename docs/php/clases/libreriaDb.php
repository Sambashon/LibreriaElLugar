<?php
class LibreriaDB{
    protected PDO $pdo;

    public function __construct(string $host, string $username, string $dbname, string $password, int $port){
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4",
            $username,
            $password,
            $options
        );
    }
}