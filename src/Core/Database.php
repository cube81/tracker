<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;

    public static function connect(): PDO {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    DB_HOST,
                    DB_NAME
                );
                self::$connection = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die('Database connection failed: ' . ($e->getMessage()));
            }
        }
        return self::$connection;
    }

    public static function pdo(): PDO {
        return self::connect();
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []) {
        return self::query($sql, $params)->fetch();
    }

    public static function exec(string $sql, array $params = []): int {
        return self::query($sql, $params)->rowCount();
    }

    public static function lastInsertId(): string {
        return self::pdo()->lastInsertId();
    }
}
