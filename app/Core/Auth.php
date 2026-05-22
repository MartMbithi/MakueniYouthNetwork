<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    public static function attempt(string $email, string $password): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, (string) $row['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = (int) $row['id'];
        return true;
    }

    public static function check(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION[self::SESSION_KEY]]);
        $row = $stmt->fetch();
        return $cache = ($row !== false ? $row : null);
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::redirect('/admin/login');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        $u = self::user();
        if (($u['role'] ?? null) !== $role) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Forbidden';
            exit;
        }
    }
}
