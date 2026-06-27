<?php

namespace App\Models;

use App\Core\Database;

class Project {
    public int $id;
    public int $client_id;
    public string $name;
    public string $description;
    public string $color;
    public float $hourly_rate;
    public bool $is_active;
    public string $created_at;

    private ?Client $client = null;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM projects WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function all($active = true): array {
        $sql = 'SELECT * FROM projects';
        if ($active) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY name';
        $rows = Database::all($sql);
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function where(string $column, $value): array {
        $rows = Database::all('SELECT * FROM projects WHERE ' . $column . ' = ? ORDER BY name', [$value]);
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function hydrate(array $data): self {
        $project = new self();
        foreach ($data as $key => $value) {
            $project->$key = $value;
        }
        return $project;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE projects SET client_id = ?, name = ?, description = ?, color = ?, hourly_rate = ?, is_active = ? WHERE id = ?',
                [$this->client_id, $this->name, $this->description, $this->color ?? '#1e90ff', $this->hourly_rate ?? 0, $this->is_active ? 1 : 0, $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO projects (client_id, name, description, color, hourly_rate, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$this->client_id, $this->name, $this->description, $this->color ?? '#1e90ff', $this->hourly_rate ?? 0, $this->is_active ? 1 : 0, now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM projects WHERE id = ?', [$this->id]) > 0;
    }

    public function client(): Client {
        if ($this->client === null) {
            $this->client = Client::find($this->client_id);
        }
        return $this->client;
    }

    public function members(): array {
        return ProjectMember::where('project_id', $this->id);
    }

    public function activities(array $filters = []): array {
        return Activity::where('project_id', $this->id, $filters);
    }
}
