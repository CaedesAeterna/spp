<?php
declare(strict_types=1);

/**
 * Delete one news item (task 5).
 * Prints nothing: it works, leaves a message and goes back to the list.
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

if (!is_post()) {
    flash('error', 'Deleting is only possible through the Delete button.');
    redirect(url('task5-news/index.php'));
}

if (!csrf_check()) {
    flash('error', 'The form expired. Please try again.');
    redirect(url('task5-news/index.php'));
}

$item = news_find(post_int('id'));

if ($item === null) {
    flash('error', 'That news item no longer exists.');
    redirect(url('task5-news/index.php'));
}

// Your own item, or anything at all when you are an administrator.
if (!news_can_edit($item)) {
    flash('error', 'You may only delete your own news.');
    redirect(url('task5-news/index.php'));
}

if (news_delete((int)$item['id'])) {
    flash('success', 'The news item "' . $item['title'] . '" was deleted.');
} else {
    flash('error', 'The news item could not be deleted.');
}

redirect(url('task5-news/index.php'));
