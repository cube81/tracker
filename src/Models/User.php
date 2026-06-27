<?php

namespace App\Models;

use App\Core\Database;

class User {
    public int $id;
    public string $email;
    public string $name;
    public string $global_role;
    public bool $is_active;
    public ?string $remember_token;
    public ?int $default_project_id = null;
    public string $created_at;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function findByEmail(string $email): ?self {
        $row = Database::one('SELECT * FROM users WHERE email = ?', [$email]);
        return $row ? self::hydrate($row) : null;
    }

    public static function all(): array {
        $rows = Database::all('SELECT * FROM users ORDER BY created_at DESC');
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function hydrate(array $data): self {
        $user = new self();
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE users SET email = ?, name = ?, global_role = ?, is_active = ? WHERE id = ?',
                [$this->email, $this->name, $this->global_role, $this->is_active ? 1 : 0, $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO users (email, name, global_role, is_active, created_at) VALUES (?, ?, ?, ?, ?)',
                [$this->email, $this->name, $this->global_role, $this->is_active ? 1 : 0, now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function setPassword(string $password): void {
        Database::exec(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT), $this->id]
        );
    }

    public function verifyPassword(string $password): bool {
        $hash = Database::one('SELECT password_hash FROM users WHERE id = ?', [$this->id]);
        return $hash && password_verify($password, $hash['password_hash']);
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM users WHERE id = ?', [$this->id]) > 0;
    }

    public function isAdmin(): bool {
        return $this->global_role === 'admin';
    }

    public function isPM(): bool {
        return $this->global_role === 'pm';
    }

    public function isDeveloper(): bool {
        return $this->global_role === 'developer';
    }
}
