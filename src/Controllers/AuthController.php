<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Mailer;
use App\Models\User;

class AuthController {
    public function login() {
        if (Auth::check()) {
            redirect('/');
        }

        $error = null;
        $submittedEmail = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $submittedEmail = $email;

            $user = User::findByEmail($email);
            if ($user && $user->verifyPassword($password) && $user->is_active) {
                Auth::login($user);
                redirect('/');
            }

            $error = 'Nieprawidłowy email lub hasło';
        }

        echo view('auth.login', ['error' => $error, 'submittedEmail' => $submittedEmail]);
    }

    public function logout() {
        Auth::logout();
        redirect('/login');
    }

    public function resetRequest() {
        if (Auth::check()) {
            redirect('/');
        }

        $message = '';
        $prefillEmail = $_GET['email'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $user = User::findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                Database::exec(
                    'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))',
                    [$user->id, $token]
                );

                $resetUrl = APP_URL . '/reset-password/' . $token;
                if (Mailer::resetPassword($email, $resetUrl)) {
                    $message = 'Email do resetowania hasła został wysłany.';
                }
            } else {
                $message = 'Jeśli konto istnieje, wysłaliśmy email do resetowania hasła.';
            }
        }

        echo view('auth.reset_request', ['message' => $message, 'prefillEmail' => $prefillEmail]);
    }

    public function resetConfirm() {
        if (Auth::check()) {
            redirect('/');
        }

        $token = $_GET['token'] ?? '';
        $reset = Database::one(
            'SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL',
            [$token]
        );

        if (!$reset) {
            echo view('auth.reset_invalid');
            return;
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (!$password || strlen($password) < 6) {
                $message = 'Hasło musi mieć co najmniej 6 znaków.';
            } elseif ($password !== $password_confirm) {
                $message = 'Hasła nie są identyczne.';
            } else {
                $user = User::find($reset['user_id']);
                $user->setPassword($password);
                Database::exec('UPDATE password_resets SET used_at = NOW() WHERE id = ?', [$reset['id']]);
                $message = 'Hasło zostało zmienione. Możesz się zalogować.';
                // TODO: show success view
            }
        }

        echo view('auth.reset_confirm', ['token' => $token, 'message' => $message]);
    }
}
