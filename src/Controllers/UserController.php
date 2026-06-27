<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\User;

class UserController {
    public function index() {
        Auth::guard('admin');
        $users = User::all();
        echo view('users.index', ['users' => $users]);
    }

    public function create() {
        Auth::guard('admin');

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User();
            $user->email = $_POST['email'] ?? '';
            $user->name = $_POST['name'] ?? '';
            $user->global_role = $_POST['global_role'] ?? 'developer';
            $user->is_active = true;

            // Check if user exists
            if (User::findByEmail($user->email)) {
                $message = 'Email już istnieje';
            } elseif ($user->save()) {
                $password = substr(bin2hex(random_bytes(4)), 0, 8);
                $user->setPassword($password);
                // TODO: Send email with temporary password
                redirect('/users');
            }
        }

        echo view('users.create', ['message' => $message]);
    }

    public function edit() {
        Auth::guard('admin');

        $id = $_GET['id'] ?? null;
        $user = User::find($id);
        if (!$user) abort(404);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->name = $_POST['name'] ?? $user->name;
            $user->global_role = $_POST['global_role'] ?? $user->global_role;
            $user->is_active = isset($_POST['is_active']);

            if (isset($_POST['password']) && $_POST['password']) {
                $user->setPassword($_POST['password']);
            }

            $user->save();
            redirect('/users');
        }

        echo view('users.edit', ['user' => $user]);
    }

    public function delete() {
        Auth::guard('admin');

        $id = $_GET['id'] ?? null;
        $user = User::find($id);
        if (!$user) abort(404);

        $user->delete();
        redirect('/users');
    }
}
