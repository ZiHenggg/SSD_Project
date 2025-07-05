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

        $fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . ($_SERVER['REMOTE_ADDR'] ?? ''));

        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $fingerprint;
        } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
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
        // Unset all session variables
        $_SESSION = [];

        // If session uses cookies, delete the session cookie (For extra confirmation)
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000, // set expiration in the past
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
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

    public static function setForgotPasswordEmail(?string $email): void
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

    public static function setRegisterStep(string $step): void
    {
        self::set('register_step', $step);
    }

    public static function getRegisterStep(): ?string
    {
        return self::get('register_step');
    }

    public static function setRegisterMessage(string $message): void
    {
        self::set('register_message', $message);
    }

    public static function getRegisterMessage(): ?string
    {
        return self::get('register_message');
    }

    public static function setRegisterMessageType(string $type): void
    {
        self::set('register_message_type', $type);
    }

    public static function getRegisterMessageType(): ?string
    {
        return self::get('register_message_type') ?? 'info';
    }

    public static function setRegisterSuccess(bool $success): void
    {
        self::set('register_success', $success);
    }

    public static function isRegisterSuccess(): bool
    {
        return self::get('register_success') ?? false;
    }

    public static function setForgotStep(string $step): void
    {
        self::set('forgot_step', $step);
    }

    public static function getForgotStep(): ?string
    {
        return self::get('forgot_step');
    }

    public static function setForgotMessage(string $message): void
    {
        self::set('forgot_message', $message);
    }

    public static function getForgotMessage(): ?string
    {
        return self::get('forgot_message');
    }

public static function setForgotStartedAt(int $timestamp): void
    {
        self::set('forgot_started_at', $timestamp);
    }

    public static function getForgotStartedAt(): ?int
    {
        return self::get('forgot_started_at');
    }

    public static function isForgotFlowExpired(int $timeoutSeconds): bool
    {
        $startedAt = self::getForgotStartedAt();
        return $startedAt !== null && (time() - $startedAt) > $timeoutSeconds;
    }

    public static function resetForgotFlow(): void
    {
        self::remove('forgot_step');
        self::remove('forgot_message');
        self::remove('forgot_password'); 
        self::remove('forgot_started_at');
        self::remove('otp');
        self::remove('otp_expiry');
    }

    public static function resetRegisterFlow(): void
    {
        self::remove('register_step');
        self::remove('register_message');
        self::remove('register_message_type');
        self::remove('register_success');
        self::remove('otp');
        self::remove('otp_expiry');
    }

    public static function setChangePWError(?string $message): void
    {
        self::set('change_pw_error', $message);
    }

    public static function getChangePWError(): ?string
    {
        return self::get('change_pw_error');
    }

    public static function is2faVerified(): bool
    {
        return $_SESSION['2fa_verified'] ?? false;
    }

    public static function set2faVerified(bool $value): void
    {
        $_SESSION['2fa_verified'] = $value;
    }

    public static function resetAuth(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['2fa']);
        unset($_SESSION['2fa_verified']);
    }

}
