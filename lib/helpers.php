<?php
declare(strict_types=1);

/**
 * Small helpers used by every page.
 * Nothing here talks to the database or the session.
 */

/* ------------------------------------------------------------------ *
 * Links
 *
 * The pages sit in per-task folders, so "users.php" would mean something
 * different depending on which folder you are in. url() always builds the
 * address from the application root instead:
 *
 *     url('task2-users/index.php')   ->  /task2-users/index.php
 *
 * It also keeps working if the app is served from a subdirectory
 * (http://localhost/spp/), which a plain "/task2-users/..." would not.
 * ------------------------------------------------------------------ */

/** The URL the application is served from, always ending in a slash. */
function app_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $base       = '/';
    $appRoot    = realpath(dirname(__DIR__));                       // .../htdocs
    $scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');      // .../htdocs/task3-game/index.php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';                    // /task3-game/index.php

    // Cut the script's path inside the app off the end of its URL; whatever
    // is left in front is the address the app itself lives at.
    if ($appRoot !== false && $scriptFile !== false && str_starts_with($scriptFile, $appRoot)) {
        $insideApp = substr($scriptFile, strlen($appRoot));
        if ($insideApp !== '' && str_ends_with($scriptName, $insideApp)) {
            $base = substr($scriptName, 0, strlen($scriptName) - strlen($insideApp)) . '/';
        }
    }

    return $base;
}

/** An address inside the application: url('task4-stats/index.php'). */
function url(string $path = ''): string
{
    return app_base() . ltrim($path, '/');
}

/** Escape a value for safe HTML output. Use it on EVERY echoed variable. */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** True when the current request is a form submit. */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Read a POST field as a trimmed string. */
function post(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? null;
    return is_string($value) ? trim($value) : $default;
}

/** Read a GET field as a trimmed string. */
function query(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? null;
    return is_string($value) ? trim($value) : $default;
}

/** Read a POST field as an integer. */
function post_int(string $key, int $default = 0): int
{
    $value = $_POST[$key] ?? null;
    return is_numeric($value) ? (int)$value : $default;
}

/** Read a GET field as an integer. */
function query_int(string $key, int $default = 0): int
{
    $value = $_GET[$key] ?? null;
    return is_numeric($value) ? (int)$value : $default;
}

/** Send the browser to another page and stop. Always use this after a POST. */
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/** Add an error to $errors when $condition fails. Returns the condition. */
function check(array &$errors, bool $condition, string $field, string $message): bool
{
    if (!$condition) {
        $errors[$field] = $message;
    }
    return $condition;
}
