<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Project;
use App\Models\Client;

class ProjectController {
    public function index() {
        Auth::guard();
        $projects = Project::all(true);
        $user = Auth::user();
        echo view('projects.index', ['projects' => $projects, 'defaultProjectId' => $user->default_project_id]);
    }

    public function setDefault() {
        Auth::guard();
        $id = (int)($_GET['id'] ?? 0);
        $project = Project::find($id);
        if (!$project) abort(404);

        $user = Auth::user();
        $newDefault = ($user->default_project_id == $id) ? null : $id;
        Database::exec('UPDATE users SET default_project_id = ? WHERE id = ?', [$newDefault, $user->id]);

        redirect('/projects');
    }

    public function show() {
        Auth::guard();
        $id = $_GET['id'] ?? null;
        $project = Project::find($id);
        if (!$project) abort(404);

        echo view('projects.show', ['project' => $project]);
    }

    public function create() {
        Auth::guard('admin');

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $project = new Project();
            $project->client_id = $_POST['client_id'] ?? 0;
            $project->name = $_POST['name'] ?? '';
            $project->description = $_POST['description'] ?? '';
            $project->color = $_POST['color'] ?? '#1e90ff';
            $project->hourly_rate = (float)($_POST['hourly_rate'] ?? 0);
            $project->is_active = true;

            if ($project->save()) {
                redirect('/projects');
            }
            $message = 'Błąd przy zapisywaniu projektu';
        }

        $clients = Client::all(true);
        echo view('projects.create', ['clients' => $clients, 'message' => $message]);
    }

    public function edit() {
        Auth::guard('admin');

        $id = $_GET['id'] ?? null;
        $project = Project::find($id);
        if (!$project) abort(404);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $project->client_id = $_POST['client_id'] ?? $project->client_id;
            $project->name = $_POST['name'] ?? $project->name;
            $project->description = $_POST['description'] ?? $project->description;
            $project->color = $_POST['color'] ?? $project->color;
            $project->hourly_rate = (float)($_POST['hourly_rate'] ?? $project->hourly_rate);
            $project->is_active = isset($_POST['is_active']);
            $project->save();
            redirect('/projects');
        }

        $clients = Client::all(false);
        echo view('projects.edit', ['project' => $project, 'clients' => $clients]);
    }
}
