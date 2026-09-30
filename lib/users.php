<?php
declare(strict_types=1);

/**
 * Everything that touches the `users` table lives here, so the pages stay
 * about screens and messages only.
 */

/** Every user, oldest first. */
function users_all(): array
{
    return db_all('SELECT * FROM users ORDER BY id');
}

/** One user by id, or null. */
function user_find(int $id): ?array
{
    return db_one('SELECT * FROM users WHERE id = ?', [$id]);
}

/** One user by username, or null. Used by the login form. */
function user_find_by_username(string $username): ?array
{
    return db_one('SELECT * FROM users WHERE username = ?', [$username]);
}

/** Is this username already taken? $exceptId lets a user keep their own name. */
function username_taken(string $username, int $exceptId = 0): bool
{
    return db_value(
        'SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?',
        [$username, $exceptId]
    ) > 0;
}

/**
 * Does $password belong to $user?
 * The table stores a hash, so the typed password is hashed with the same salt
 * (which is inside the stored hash) and the two are compared.
 */
function user_password_matches(array $user, string $password): bool
{
    return password_verify($password, $user['password_hash']);
}


/** Insert a new user and return its id. The password is hashed, never stored as typed. */
function user_create(string $username, string $password, string $fullName, string $email, bool $isAdmin): int
{
    return db_insert(
        'INSERT INTO users (username, password_hash, full_name, email, is_admin)
         VALUES (?, ?, ?, ?, ?)',
        [
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $fullName,
            $email,
            (int)$isAdmin,
        ]
    );
}

/**
 * Update one user. An empty $password means "keep the current password", so the
 * password column is only added to the statement when something was typed.
 * Returns true when the row was found.
 */
function user_update(int $id, string $username, string $password, string $fullName, string $email, bool $isAdmin): bool
{
    $sql    = 'UPDATE users SET username = ?, full_name = ?, email = ?, is_admin = ?';
    $params = [$username, $fullName, $email, (int)$isAdmin];

    if ($password !== '') {
        $sql     .= ', password_hash = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql     .= ' WHERE id = ?';
    $params[] = $id;

    return db_run($sql, $params) === 1;
}

/** Delete one user. Returns true when a row was removed. */
function user_delete(int $id): bool
{
    return db_run('DELETE FROM users WHERE id = ?', [$id]) === 1;
}

/**
 * Check the fields coming from the user form.
 * Returns an array of "field name => message"; empty means everything is fine.
 */
function user_validate(array $input, int $editingId = 0): array
{
    $errors = [];

    $username = trim((string)($input['username'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $email    = trim((string)($input['email'] ?? ''));

    if ($username === '') {
        $errors['username'] = 'The username is required.';
    } elseif (mb_strlen($username) < 3) {
        $errors['username'] = 'The username must be at least 3 characters.';
    } elseif (username_taken($username, $editingId)) {
        $errors['username'] = 'This username is already taken.';
    }

    // On create a password is required; on edit an empty box keeps the old one.
    if ($editingId === 0 && $password === '') {
        $errors['password'] = 'The password is required.';
    } elseif ($password !== '' && mb_strlen($password) < 5) {
        $errors['password'] = 'The password must be at least 5 characters.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'This is not a valid e-mail address.';
    }

    return $errors;
}
