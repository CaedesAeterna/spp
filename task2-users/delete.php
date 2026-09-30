<?php
declare(strict_types=1);

/**
 * Task 2 - delete one user.
 * This page never prints anything: it works, sets a message and goes back.
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

if (!is_post()) {
    flash('error', 'Deleting is only possible through the Delete button.');
    redirect(url('task2-users/index.php'));
}

if (!csrf_check()) {
    flash('error', 'The form expired. Please try again.');
    redirect(url('task2-users/index.php'));
}

$id   = post_int('id');
$user = user_find($id);

if ($user === null) {
    flash('error', 'That user no longer exists.');
    redirect(url('task2-users/index.php'));
}

if ($id === current_user_id()) {
    flash('error', 'You cannot delete the account you are logged in with.');
    redirect(url('task2-users/index.php'));
}

if (user_delete($id)) {
    flash('success', 'User "' . $user['username'] . '" was deleted.');
} else {
    flash('error', 'User "' . $user['username'] . '" could not be deleted.');
}

redirect(url('task2-users/index.php'));
