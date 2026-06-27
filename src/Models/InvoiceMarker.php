<?php

namespace App\Models;

use App\Core\Database;

class InvoiceMarker {
    public int $id;
    public int $project_id;
    public string $invoice_date;
    public string $note;
    public string $created_at;

    private ?Project $project = null;

    public static function find(int $id): ?self {
        $row = Database::one('SELECT * FROM invoice_markers WHERE id = ?', [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function where(string $column, $value): array {
        $rows = Database::all('SELECT * FROM invoice_markers WHERE ' . $column . ' = ? ORDER BY invoice_date DESC', [$value]);
        return array_map(fn($r) => self::hydrate($r), $rows);
    }

    public static function hydrate(array $data): self {
        $marker = new self();
        foreach ($data as $key => $value) {
            $marker->$key = $value;
        }
        return $marker;
    }

    public function save(): bool {
        if (isset($this->id)) {
            return Database::exec(
                'UPDATE invoice_markers SET project_id = ?, invoice_date = ?, note = ? WHERE id = ?',
                [$this->project_id, $this->invoice_date, $this->note, $this->id]
            ) > 0;
        } else {
            Database::exec(
                'INSERT INTO invoice_markers (project_id, invoice_date, note, created_at) VALUES (?, ?, ?, ?)',
                [$this->project_id, $this->invoice_date, $this->note, now()]
            );
            $this->id = (int)Database::lastInsertId();
            return true;
        }
    }

    public function delete(): bool {
        return Database::exec('DELETE FROM invoice_markers WHERE id = ?', [$this->id]) > 0;
    }

    public function project(): Project {
        if ($this->project === null) {
            $this->project = Project::find($this->project_id);
        }
        return $this->project;
    }
}
