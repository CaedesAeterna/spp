<?php
declare(strict_types=1);

/**
 * Session bootstrap, flash messages, CSRF protection and login state.
 * Every page starts by calling session_boot().
 */

/** Start the session once per request. Safe to call repeatedly. */
function session_boot(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/* ------------------------------------------------------------------ *
 * Flash messages - "give a message in all cases, successful or not"
 * ------------------------------------------------------------------ */

/** Queue a message for the NEXT page load. $type: success | error | info */
function flash(string $type, string $message): void
{
    session_boot();
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

/** Return every queued message and clear the queue. */
function flash_take(): array
{
    session_boot();
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $flashes;
}

/* ------------------------------------------------------------------ *
 * CSRF - stops a foreign page from submitting your forms
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Drop this inside every <form> that uses POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** True when the submitted token matches the one in the session. */
function csrf_check(): bool
{
    session_boot();
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/* ------------------------------------------------------------------ *
 * Login state
 * ------------------------------------------------------------------ */

/** Store the logged-in user in the session. Pass the row read from `users`. */
function login_user(array $user): void
{
    session_boot();
    session_regenerate_id(true);          // new id on login - blocks session fixation
    $_SESSION['user'] = [
        'id'       => (int)($user['id'] ?? 0),
        'username' => (string)($user['username'] ?? ''),
        'is_admin' => (bool)($user['is_admin'] ?? false),
    ];
}

/** The logged-in user, or null. */
function current_user(): ?array
{
    session_boot();
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (bool)(current_user()['is_admin'] ?? false);
}

/** The logged-in user's id, or 0 when nobody is logged in. */
function current_user_id(): int
{
    return (int)(current_user()['id'] ?? 0);
}

/** Bounce guests to the login page. Call it at the top of protected pages. */
function require_login(string $loginPage = ''): void
{
    $loginPage = $loginPage !== '' ? $loginPage : url('task1-login/index.php');

    if (!is_logged_in()) {
        flash('error', 'Please log in first.');
        redirect($loginPage);
    }
}

/** Bounce non-admins. Call it after require_login() on admin-only pages. */
function require_admin(string $fallbackPage = ''): void
{
    $fallbackPage = $fallbackPage !== '' ? $fallbackPage : url('task5-news/index.php');

    if (!is_admin()) {
        flash('error', 'You need administrator rights for that.');
        redirect($fallbackPage);
    }
}

/** Forget the user but keep the session (so the flash below survives). */
function logout_user(): void
{
    session_boot();
    $_SESSION = [];
    session_regenerate_id(true);
}
