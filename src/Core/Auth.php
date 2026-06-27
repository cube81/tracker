<?php

namespace App\Core;

use App\Models\User;

class Auth {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?User {
        if (!self::check()) {
            return null;
        }
        return User::find($_SESSION['user_id']);
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function login(User $user): void {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
    }

    public static function logout(): void {
        session_destroy();
    }

    public static function guard(?string $role = null): void {
        if (!self::check()) {
            redirect('/login');
        }

        if ($role && self::user()->global_role !== $role) {
            abort(403);
        }
    }

    public static function isAdmin(): bool {
        return self::check() && self::user()->global_role === 'admin';
    }

    public static function isPM(): bool {
        return self::check() && self::user()->global_role === 'pm';
    }

    public static function isDeveloper(): bool {
        return self::check() && self::user()->global_role === 'developer';
    }
}
