<?php

namespace App\Models;

use App\Core\Database;

class Activity {
    public int $id;
    public int $project_id;
    public int $user_id;
    public string $description;
    public string $date;
    public string $time_from;
    public string $time_to;
    public int $duration_minutes;
    public bool $is_billed;
    public string $created_at;
    public string $updated_at;

    private ?Project $project = null;
    private ?User $user = null;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM activities WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function all(): array {
        $rows = Database::all('SELECT * FROM activities ORDER BY date DESC, time_from DESC');
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function where(string $column, $value, array $filters = []): array {
        $sql = 'SELECT * FROM activities WHERE ' . $column . ' = ?';
        $params = [$value];

        $sql .= ' ORDER BY date DESC, time_from DESC';
        $rows = Database::all($sql, $params);
        $activities = array_map(fn($r) => self::hydrate($r), $rows);

        // Filter in PHP
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $dateFrom = !empty($filters['date_from']) ? $filters['date_from'] : null;
            $dateTo = !empty($filters['date_to']) ? $filters['date_to'] : null;
            $activities = array_filter($activities, function($a) use ($dateFrom, $dateTo) {
                if ($dateFrom && $a->date < $dateFrom) return false;
                if ($dateTo && $a->date > $dateTo) return false;
                return true;
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'billed') {
                $activities = array_filter($activities, fn($a) => $a->is_billed);
            } elseif ($filters['status'] === 'unbilled') {
                $activities = array_filter($activities, fn($a) => !$a->is_billed);
            }
        }

        if (!empty($filters['user_id'])) {
            $activities = array_filter($activities, fn($a) => $a->user_id == $filters['user_id']);
        }

        return $activities;
    }

    public static function hydrate(array $data): self {
        $activity = new self();
        foreach ($data as $key => $value) {
            if ($key === 'is_billed') {
                $activity->$key = (bool)$value;
            } else {
                $activity->$key = $value;
            }
        }
        return $activity;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE activities SET project_id = ?, user_id = ?, description = ?, date = ?, time_from = ?, time_to = ?, duration_minutes = ?, is_billed = ?, updated_at = ? WHERE id = ?',
                [$this->project_id, $this->user_id, $this->description, $this->date, $this->time_from, $this->time_to, $this->duration_minutes, $this->is_billed ? 1 : 0, now(), $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO activities (project_id, user_id, description, date, time_from, time_to, duration_minutes, is_billed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$this->project_id, $this->user_id, $this->description, $this->date, $this->time_from, $this->time_to, $this->duration_minutes, $this->is_billed ? 1 : 0, now(), now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM activities WHERE id = ?', [$this->id]) > 0;
    }

    public function project(): Project {
        if ($this->project === null) {
            $this->project = Project::find($this->project_id);
        }
        return $this->project;
    }

    public function user(): User {
        if ($this->user === null) {
            $this->user = User::find($this->user_id);
        }
        return $this->user;
    }
}
