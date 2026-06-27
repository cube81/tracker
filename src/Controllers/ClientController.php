<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Client;

class ClientController {
    public function index() {
        Auth::guard('admin');
        $clients = Client::all(false);
        echo view('clients.index', ['clients' => $clients]);
    }

    public function create() {
        Auth::guard('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client = new Client();
            $client->name = $_POST['name'] ?? '';
            $client->description = $_POST['description'] ?? '';
            $client->is_active = true;

            if ($client->save()) {
                redirect('/clients');
            }
        }

        echo view('clients.create', []);
    }

    public function edit() {
        Auth::guard('admin');

        $id = $_GET['id'] ?? null;
        $client = Client::find($id);
        if (!$client) abort(404);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client->name = $_POST['name'] ?? $client->name;
            $client->description = $_POST['description'] ?? $client->description;
            $client->is_active = isset($_POST['is_active']);
            $client->save();
            redirect('/clients');
        }

        echo view('clients.edit', ['client' => $client]);
    }
}
