<?php
declare(strict_types=1);

/**
 * Task 2 - one form for both jobs:
 *   user_form.php          -> create a new user
 *   user_form.php?id=12    -> edit user 12
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$id        = query_int('id');
$isEditing = $id > 0;
$errors    = [];

// What the form shows. On edit we start from the stored row.
$values = ['username' => '', 'full_name' => '', 'email' => '', 'is_admin' => false];

if ($isEditing) {
    $user = user_find($id);
    if ($user === null) {
        flash('error', 'That user no longer exists.');
        redirect(url('task2-users/index.php'));
    }
    $values = [
        'username'  => $user['username'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'is_admin'  => (bool)$user['is_admin'],
    ];
}

if (is_post()) {
    if (!csrf_check()) {
        flash('error', 'The form expired. Please try again.');
        redirect($isEditing ? url('task2-users/form.php') . '?id=' . $id : url('task2-users/form.php'));
    }

    // Keep what was typed, so a failed save does not empty the form.
    $values = [
        'username'  => post('username'),
        'full_name' => post('full_name'),
        'email'     => post('email'),
        'is_admin'  => isset($_POST['is_admin']),
    ];
    $password = post('password');

    $errors = user_validate($values + ['password' => $password], $isEditing ? $id : 0);

    if (!$errors) {
        if ($isEditing) {
            $saved = user_update(
                $id,
                $values['username'],
                $password,
                $values['full_name'],
                $values['email'],
                $values['is_admin']
            );
            // A message in BOTH cases, as the task asks.
            if ($saved) {
                flash('success', 'User "' . $values['username'] . '" was updated.');
                redirect(url('task2-users/index.php'));
            }
            flash('error', 'The user could not be updated.');
        } else {
            $newId = user_create(
                $values['username'],
                $password,
                $values['full_name'],
                $values['email'],
                $values['is_admin']
            );
            if ($newId > 0) {
                flash('success', 'User "' . $values['username'] . '" was created.');
                redirect(url('task2-users/index.php'));
            }
            flash('error', 'The user could not be created.');
        }
    } else {
        flash('error', 'Please fix the fields marked below.');
    }
}

render_header($isEditing ? 'Edit user' : 'New user', main_nav());
render_task_banner(2);
?>

<form class="card card-narrow" method="post"
      action="<?= e($isEditing ? url('task2-users/form.php') . '?id=' . (int)$id : url('task2-users/form.php')) ?>">
    <?= csrf_field() ?>

    <p>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($values['username']) ?>" autofocus>
        <?php if (isset($errors['username'])): ?>
            <span class="field-error"><?= e($errors['username']) ?></span>
        <?php endif; ?>
    </p>

    <p>
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span class="field-error"><?= e($errors['password']) ?></span>
        <?php elseif ($isEditing): ?>
            <span class="muted" style="font-size:.85rem">Leave empty to keep the current password.</span>
        <?php endif; ?>
    </p>

    <p>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= e($values['full_name']) ?>">
    </p>

    <p>
        <label for="email">E-mail</label>
        <input type="text" id="email" name="email" value="<?= e($values['email']) ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="field-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </p>

    <p>
        <label>
            <input type="checkbox" name="is_admin" value="1" <?= $values['is_admin'] ? 'checked' : '' ?>>
            Administrator
        </label>
    </p>

    <p class="actions">
        <button type="submit"><?= $isEditing ? 'Save changes' : 'Create user' ?></button>
        <a class="button button-quiet" href="<?= e(url('task2-users/index.php')) ?>">Cancel</a>
    </p>
</form>

<?php render_footer(); ?>
