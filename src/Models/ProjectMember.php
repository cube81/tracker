<?php

namespace App\Models;

use App\Core\Database;

class ProjectMember {
    public int $id;
    public int $project_id;
    public int $user_id;
    public string $role; // 'pm' or 'developer'
    public string $created_at;

    private ?Project $project = null;
    private ?User $user = null;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM project_members WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function where(string $column, $value): array {
        $rows = Database::all('SELECT * FROM project_members WHERE ' . $column . ' = ? ORDER BY created_at', [$value]);
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function findByProjectAndUser(int $project_id, int $user_id): ?self {
        $row = Database::one('SELECT * FROM project_members WHERE project_id = ? AND user_id = ?', [$project_id, $user_id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function hydrate(array $data): self {
        $member = new self();
        foreach ($data as $key => $value) {
            $member->$key = $value;
        }
        return $member;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE project_members SET project_id = ?, user_id = ?, role = ? WHERE id = ?',
                [$this->project_id, $this->user_id, $this->role, $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO project_members (project_id, user_id, role, created_at) VALUES (?, ?, ?, ?)',
                [$this->project_id, $this->user_id, $this->role, now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM project_members WHERE id = ?', [$this->id]) > 0;
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
