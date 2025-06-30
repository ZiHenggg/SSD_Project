<?php

namespace App;

class SessionManager
{
    public const STRUCT = [
        'user' => [
            'id' => null,
            'email' => null,
            'name' => null,
        ],
        '2fa' => [
            'pending_email' => null,
            'secret' => null,
        ],
        'otp' => [
            'code' => null,
            'expiry' => null,
        ],
        'registration' => [
            'studentId' => null,
            'studentName' => null,
            'email' => null,
            'password' => null,
        ],
        'forgot_password' => [
            'email' => null,
        ],
        'login_error' => null,
        'last_activity' => null,
        'session_created' => null,
    ];

    public const INACTIVITY_TIMEOUT = 900;   // 15 minutes
    public const ABSOLUTE_TIMEOUT   = 3600;  // 1 hour

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
            session_regenerate_id(true);
        }

        if (!self::has('session_created')) {
            self::set('session_created', time());
        }

        self::set('last_activity', time());
    }

    public static function enforceTimeoutIfLoggedIn(): void
    {
        if (self::getUser()) {
            self::enforceTimeout();
        }
    }

    public static function enforceTimeout(): void
    {
        $now     = time();
        $last    = self::get('last_activity') ?? $now;
        $created = self::get('session_created') ?? $now;

        if ($now - $last > self::INACTIVITY_TIMEOUT) {
            self::set('login_error', 'You were logged out due to inactivity.');
        } elseif ($now - $created > self::ABSOLUTE_TIMEOUT) {
            self::set('login_error', 'Your session has expired.');
        } else {
            self::set('last_activity', $now);
            return;
        }

        self::destroy();

        // Clear session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }

        header('Location: login.php');
        exit;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function setUser(array $user): void
    {
        self::set('user', [
            'id'    => $user['id'] ?? null,
            'email' => $user['email'] ?? null,
            'name'  => $user['name'] ?? null,
        ]);
    }

    public static function getUser(): ?array
    {
        return self::get('user');
    }

    public static function set2FA(?string $pendingEmail = null, ?string $secret = null): void
    {
        self::set('2fa', [
            'pending_email' => $pendingEmail,
            'secret'        => $secret,
        ]);
    }

    public static function get2FA(): ?array
    {
        return self::get('2fa');
    }

    public static function setOTP(string $code, int $expiry): void
    {
        self::set('otp', [
            'code'   => $code,
            'expiry' => $expiry,
        ]);
    }

    public static function getOTP(): ?array
    {
        return self::get('otp');
    }

    public static function setRegistration(array $data): void
    {
        self::set('registration', [
            'studentId'   => $data['studentId'] ?? null,
            'studentName' => $data['studentName'] ?? null,
            'email'       => $data['email'] ?? null,
            'password'    => $data['password'] ?? null,
        ]);
    }

    public static function getRegistration(): ?array
    {
        return self::get('registration');
    }

    public static function setForgotPasswordEmail(string $email): void
    {
        self::set('forgot_password', ['email' => $email]);
    }

    public static function getForgotPasswordEmail(): ?string
    {
        $data = self::get('forgot_password');
        return $data['email'] ?? null;
    }

    public static function setLoginError(?string $message): void
    {
        self::set('login_error', $message);
    }

    public static function getLoginError(): ?string
    {
        return self::get('login_error');
    }

    public static function setError(?string $message): void
    {
        self::set('error', $message);
    }

    public static function getError(): ?string
    {
        return self::get ('error');
    }

    public static function setSuccess(?string $message): void
    {
        self::set('success', $message);
    }

    public static function getSuccess(): ?string
    {
        return self::get('success');
    }

    public static function setReviewContext(array $context): void
    {
        self::set('review_context', [
            'reviewer_id' => $context['reviewer_id'] ?? null,
            'reviewee_id' => $context['reviewee_id'] ?? null,
            'group_id'    => $context['group_id'] ?? null,
        ]);
    }

    public static function getReviewContext(): ?array
    {
        return self::get('review_context');
    }

    public static function clearReviewContext(): void
    {
        self::remove('review_context');
    }

}
