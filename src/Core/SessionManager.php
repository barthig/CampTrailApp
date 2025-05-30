<?php
declare(strict_types=1);

namespace src\Core;

class SessionManager
{
    /**
     * Initialize session if not already started.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Log in the user by setting session variables.
     * @param int $userId
     * @param string $role
     */
    public static function login(int $userId, string $role): void
    {
        self::start();
        // Regenerate session ID for security
        session_regenerate_id(true);
        $_SESSION['user_id']   = $userId;
        $_SESSION['user_role'] = $role;
    }

    /**
     * Log out the user and destroy the session.
     */
    public static function logout(): void
    {
        self::start();
        // Unset all session variables
        $_SESSION = [];
        // Destroy session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    
        session_destroy();
    }

    /**
     * Set a flash message for next request.
     * @param string $key
     * @param string $message
     */
    public static function flash(string $key, string $message): void
    {
        self::start();
        $_SESSION['flash_messages'][$key] = $message;
    }

    /**
     * Alias for flash(), so controllers can call addFlash()
     * @param string $key
     * @param string $message
     */
    public static function addFlash(string $key, string $message): void
    {
        self::flash($key, $message);
    }

    /**
     * Get and clear a flash message by key.
     * @param string $key
     * @return string|null
     */
    public static function getFlash(string $key): ?string
    {
        self::start();
        if (!isset($_SESSION['flash_messages'][$key])) {
            return null;
        }
        $message = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $message;
    }

    /**
     * Retrieve all flash messages and clear them.
     * @return array<string, string>
     */
    public static function getAllFlashes(): array
    {
        self::start();
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Check if a user is currently logged in.
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        self::start();
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get current logged-in user ID.
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user's role.
     * @return string|null
     */
    public static function getUserRole(): ?string
    {
        self::start();
        return $_SESSION['user_role'] ?? null;
    }
}
