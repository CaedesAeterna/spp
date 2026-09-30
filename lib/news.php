<?php
declare(strict_types=1);

/**
 * News (task 5).
 *
 * The page needs a login. Everybody logged in may write news; you may edit and
 * delete your own, an administrator may edit and delete everybody's.
 */

/** Every news item, newest first, together with the author's name. */
function news_all(): array
{
    return db_all(
        'SELECT n.*, u.username AS author
         FROM news n
         JOIN users u ON u.id = n.user_id
         ORDER BY n.created_at DESC, n.id DESC'
    );
}

/** One news item with its author, or null. */
function news_find(int $id): ?array
{
    return db_one(
        'SELECT n.*, u.username AS author
         FROM news n
         JOIN users u ON u.id = n.user_id
         WHERE n.id = ?',
        [$id]
    );
}

/**
 * May the logged-in user edit or delete this item?
 * A guest cannot reach the page at all any more, but the check stays as a second
 * lock; an administrator may touch anything, everybody else only their own rows.
 */
function news_can_edit(array $news): bool
{
    if (!is_logged_in()) {
        return false;
    }
    if (is_admin()) {
        return true;
    }
    return (int)$news['user_id'] === current_user_id();
}


/** Insert a news item and return its id. */
function news_create(int $userId, string $title, string $body): int
{
    return db_insert(
        'INSERT INTO news (user_id, title, body) VALUES (?, ?, ?)',
        [$userId, $title, $body]
    );
}

/** Update a news item, stamping updated_at. Returns true when the row was found. */
function news_update(int $id, string $title, string $body): bool
{
    return db_run(
        'UPDATE news SET title = ?, body = ?, updated_at = NOW() WHERE id = ?',
        [$title, $body, $id]
    ) === 1;
}

/** Delete a news item. Returns true when a row was removed. */
function news_delete(int $id): bool
{
    return db_run('DELETE FROM news WHERE id = ?', [$id]) === 1;
}

/** Check the news form. Returns "field name => message"; empty means it is fine. */
function news_validate(array $input): array
{
    $errors = [];

    $title = trim((string)($input['title'] ?? ''));
    $body  = trim((string)($input['body'] ?? ''));

    if ($title === '') {
        $errors['title'] = 'The title is required.';
    } elseif (mb_strlen($title) > 200) {
        $errors['title'] = 'The title may be at most 200 characters.';
    }

    if ($body === '') {
        $errors['body'] = 'The news text is required.';
    }

    return $errors;
}
