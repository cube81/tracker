<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Activity;
use App\Models\Project;
use App\Models\ProjectMember;

class ActivityController {
    public function tracker() {
        Auth::guard();

        $user = Auth::user();
        $projects = [];

        if ($user->isAdmin()) {
            $projects = Project::all(true);
        } else {
            // Get projects where user is a member
            $memberRows = Database::all(
                'SELECT DISTINCT p.* FROM projects p
                 JOIN project_members pm ON p.id = pm.project_id
                 WHERE pm.user_id = ? AND p.is_active = 1',
                [$user->id]
            );
            $projects = array_map(fn($r) => Project::hydrate($r), $memberRows);
        }

        // Get activities from current and previous month
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $prevMonthEnd = date('Y-m-d', strtotime('first day of this month -1 day'));
        $prevMonthStart = date('Y-m-01', strtotime('first day of this month -1 month'));

        $activities = Activity::where('user_id', $user->id, [
            'date_from' => $prevMonthStart,
            'date_to' => $today
        ]);

        echo view('activities.tracker', [
            'user' => $user,
            'projects' => $projects,
            'activities' => $activities,
            'today' => $today,
            'monthStart' => $monthStart,
            'prevMonthStart' => $prevMonthStart,
            'prevMonthEnd' => $prevMonthEnd,
            'defaultProjectId' => $user->default_project_id
        ]);
    }

    public function manage() {
        Auth::guard();

        $user = Auth::user();
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $projectId = $_GET['project_id'] ?? null;

        // Build query
        $sql = 'SELECT a.* FROM activities a WHERE a.user_id = ? AND a.date BETWEEN ? AND ?';
        $params = [$user->id, $dateFrom, $dateTo];

        if ($projectId) {
            $sql .= ' AND a.project_id = ?';
            $params[] = $projectId;
        }

        $sql .= ' ORDER BY a.date DESC, a.time_from DESC';
        $rows = Database::all($sql, $params);
        $activities = array_map(fn($r) => Activity::hydrate($r), $rows);

        // Get projects
        $projects = [];
        if ($user->isAdmin()) {
            $projects = Project::all(true);
        } else {
            $memberRows = Database::all(
                'SELECT DISTINCT p.* FROM projects p
                 JOIN project_members pm ON p.id = pm.project_id
                 WHERE pm.user_id = ? AND p.is_active = 1',
                [$user->id]
            );
            $projects = array_map(fn($r) => Project::hydrate($r), $memberRows);
        }

        echo view('activities.manage', [
            'activities' => $activities,
            'projects' => $projects,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'projectId' => $projectId
        ]);
    }

    public function apiBulkUpdate() {
        Auth::guard();

        $user = Auth::user();
        $activityIds = $_POST['activity_ids'] ?? [];
        $action = $_POST['action'] ?? '';

        if (!$activityIds || !$action) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parameters']);
            return;
        }

        // Verify ownership
        $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
        $params = array_merge([$user->id], $activityIds);
        $owned = Database::one(
            'SELECT COUNT(*) as count FROM activities WHERE user_id = ? AND id IN (' . $placeholders . ')',
            $params
        );

        if ($owned['count'] != count($activityIds)) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Update
        if ($action === 'mark_billed') {
            Database::exec(
                'UPDATE activities SET is_billed = 1 WHERE id IN (' . $placeholders . ')',
                $activityIds
            );
        } elseif ($action === 'mark_unbilled') {
            Database::exec(
                'UPDATE activities SET is_billed = 0 WHERE id IN (' . $placeholders . ')',
                $activityIds
            );
        } elseif ($action === 'delete') {
            Database::exec(
                'DELETE FROM activities WHERE id IN (' . $placeholders . ')',
                $activityIds
            );
        }

        echo json_encode(['success' => true, 'count' => count($activityIds)]);
    }

    public function apiMoreActivities() {
        Auth::guard();

        $user = Auth::user();
        $monthsBack = (int)($_GET['months_back'] ?? 2);

        // Get activities for the month (months_back)
        $firstDayOfMonth = date('Y-m-01', strtotime('-' . $monthsBack . ' months'));
        $lastDayOfMonth = date('Y-m-t', strtotime('-' . $monthsBack . ' months'));

        $sql = 'SELECT a.*, p.name as project_name, p.color
                FROM activities a
                JOIN projects p ON a.project_id = p.id
                WHERE a.user_id = ? AND a.date BETWEEN ? AND ?
                ORDER BY a.date DESC, a.time_from DESC';

        $rows = Database::all($sql, [$user->id, $firstDayOfMonth, $lastDayOfMonth]);

        header('Content-Type: application/json');
        echo json_encode([
            'month' => date('F Y', strtotime($firstDayOfMonth)),
            'monthShort' => date('m/Y', strtotime($firstDayOfMonth)),
            'activities' => $rows
        ]);
    }

    public function store() {
        Auth::guard();

        $user = Auth::user();
        $project_id = $_POST['project_id'] ?? null;
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $time_from = $_POST['time_from'] ?? '';
        $time_to = $_POST['time_to'] ?? '';

        // Validate
        if (!$project_id || !$description || !$time_from || !$time_to) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fields']);
            return;
        }

        // Check permission
        if (!$user->isAdmin()) {
            $member = ProjectMember::findByProjectAndUser($project_id, $user->id);
            if (!$member) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
        }

        $duration = time_to_minutes($time_from, $time_to);
        if ($duration <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid time range']);
            return;
        }

        $activity = new Activity();
        $activity->project_id = $project_id;
        $activity->user_id = $user->id;
        $activity->description = substr($description, 0, 500);
        $activity->date = $date;
        $activity->time_from = $time_from;
        $activity->time_to = $time_to;
        $activity->duration_minutes = $duration;
        $activity->is_billed = false;

        if ($activity->save()) {
            echo json_encode(['success' => true, 'activity' => $activity]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save']);
        }
    }

    public function edit() {
        Auth::guard();

        $id = $_GET['id'] ?? null;
        $activity = Activity::find($id);

        if (!$activity) {
            abort(404);
        }

        $user = Auth::user();
        if (!$user->isAdmin() && $activity->user_id !== $user->id) {
            abort(403);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $activity->description = substr($_POST['description'] ?? '', 0, 500);
            $activity->date = $_POST['date'] ?? $activity->date;
            $activity->time_from = $_POST['time_from'] ?? $activity->time_from;
            $activity->time_to = $_POST['time_to'] ?? $activity->time_to;
            $activity->is_billed = isset($_POST['is_billed']);

            $duration = time_to_minutes($activity->time_from, $activity->time_to);
            if ($duration > 0) {
                $activity->duration_minutes = $duration;
            }

            $activity->save();
            redirect('/tracker');
        }

        $projects = Project::all(true);
        echo view('activities.edit', [
            'activity' => $activity,
            'projects' => $projects
        ]);
    }

    public function delete() {
        Auth::guard();

        $id = $_GET['id'] ?? null;
        $activity = Activity::find($id);

        if (!$activity) {
            abort(404);
        }

        $user = Auth::user();
        if (!$user->isAdmin() && $activity->user_id !== $user->id) {
            abort(403);
        }

        $activity->delete();
        redirect('/tracker');
    }

    public function apiDescriptions() {
        Auth::guard();

        $q = $_GET['q'] ?? '';
        $user = Auth::user();

        $descriptions = Database::all(
            'SELECT DISTINCT description FROM activities
             WHERE user_id = ? AND description LIKE ?
             ORDER BY description LIMIT 10',
            [$user->id, '%' . $q . '%']
        );

        header('Content-Type: application/json');
        echo json_encode(array_column($descriptions, 'description'));
    }
}
