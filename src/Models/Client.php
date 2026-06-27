<?php

namespace App\Models;

use App\Core\Database;

class Client {
    public int $id;
    public string $name;
    public string $description;
    public bool $is_active;
    public string $created_at;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM clients WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function all($active = true): array {
        $sql = 'SELECT * FROM clients';
        $params = [];
        if ($active) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY name';
        $rows = Database::all($sql, $params);
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function hydrate(array $data): self {
        $client = new self();
        foreach ($data as $key => $value) {
            $client->$key = $value;
        }
        return $client;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE clients SET name = ?, description = ?, is_active = ? WHERE id = ?',
                [$this->name, $this->description, $this->is_active ? 1 : 0, $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO clients (name, description, is_active, created_at) VALUES (?, ?, ?, ?)',
                [$this->name, $this->description, $this->is_active ? 1 : 0, now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM clients WHERE id = ?', [$this->id]) > 0;
    }

    public function projects(): array {
        return Project::where('client_id', $this->id);
    }
}
