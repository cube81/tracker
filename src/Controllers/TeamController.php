<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;

class TeamController {
    public function manage() {
        Auth::guard('admin');

        $project_id = $_GET['project_id'] ?? null;
        $project = Project::find($project_id);
        if (!$project) abort(404);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Remove old members
            ProjectMember::where('project_id', $project_id);
            foreach (ProjectMember::where('project_id', $project_id) as $member) {
                $member->delete();
            }

            // Add new members
            $user_ids = $_POST['user_ids'] ?? [];
            $roles = $_POST['roles'] ?? [];

            foreach ($user_ids as $i => $user_id) {
                $member = new ProjectMember();
                $member->project_id = $project_id;
                $member->user_id = $user_id;
                $member->role = $roles[$i] ?? 'developer';
                $member->save();
            }

            redirect('/projects');
        }

        $members = ProjectMember::where('project_id', $project_id);
        $allUsers = User::all();
        $memberUserIds = array_column($members, 'user_id');

        echo view('teams.manage', [
            'project' => $project,
            'members' => $members,
            'allUsers' => $allUsers,
            'memberUserIds' => $memberUserIds
        ]);
    }
}
