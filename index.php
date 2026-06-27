<?php

require __DIR__ . '/config/config.php';
require __DIR__ . '/src/helpers.php';

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

// Composer autoload (for PHPMailer, mPDF, etc.)
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

use App\Core\Auth;
use App\Core\Router;
use App\Controllers\{
    AuthController,
    DashboardController,
    ActivityController,
    ProjectController,
    ClientController,
    UserController,
    TeamController,
    ReportController
};

Auth::start();

$router = new Router();

// Auth routes
$router->match(['GET', 'POST'], '/login', [AuthController::class, 'login']);
$router->match(['GET', 'POST'], '/logout', [AuthController::class, 'logout']);
$router->match(['GET', 'POST'], '/reset-password', [AuthController::class, 'resetRequest']);
$router->match(['GET', 'POST'], '/reset-password/{token}', [AuthController::class, 'resetConfirm']);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Activities
$router->get('/tracker', [ActivityController::class, 'tracker']);
$router->get('/activities/manage', [ActivityController::class, 'manage']);
$router->post('/activities', [ActivityController::class, 'store']);
$router->match(['GET', 'POST'], '/activities/{id}/edit', [ActivityController::class, 'edit']);
$router->delete('/activities/{id}', [ActivityController::class, 'delete']);

// Projects
$router->get('/projects', [ProjectController::class, 'index']);
$router->match(['GET', 'POST'], '/projects/new', [ProjectController::class, 'create']);
$router->match(['GET', 'POST'], '/projects/{id}/edit', [ProjectController::class, 'edit']);
$router->post('/projects/{id}/set-default', [ProjectController::class, 'setDefault']);
$router->get('/projects/{id}', [ProjectController::class, 'show']);

// Clients
$router->get('/clients', [ClientController::class, 'index']);
$router->match(['GET', 'POST'], '/clients/new', [ClientController::class, 'create']);
$router->match(['GET', 'POST'], '/clients/{id}/edit', [ClientController::class, 'edit']);

// Users (admin)
$router->get('/users', [UserController::class, 'index']);
$router->match(['GET', 'POST'], '/users/new', [UserController::class, 'create']);
$router->match(['GET', 'POST'], '/users/{id}/edit', [UserController::class, 'edit']);
$router->delete('/users/{id}', [UserController::class, 'delete']);

// Teams
$router->match(['GET', 'POST'], '/projects/{project_id}/team', [TeamController::class, 'manage']);

// Reports
$router->get('/reports', [ReportController::class, 'index']);
$router->get('/reports/export', [ReportController::class, 'export']);

// API routes
$router->get('/api/descriptions', [ActivityController::class, 'apiDescriptions']);
$router->get('/api/more-activities', [ActivityController::class, 'apiMoreActivities']);
$router->post('/api/bulk-update', [ActivityController::class, 'apiBulkUpdate']);

$router->dispatch();
