<?php
declare(strict_types=1);

/** Task 2 - the list of users, with edit/delete/create actions. */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$users = users_all();

render_header('Users', main_nav());
render_task_banner(2);
?>

<p class="actions">
    <a class="button" href="<?= e(url('task2-users/form.php')) ?>">New user</a>
</p>

<?php if (!$users): ?>

    <p class="alert alert-info">There are no users yet.</p>

<?php else: ?>

    <table class="card">
        <thead>
        <tr>
            <th>#</th>
            <th>Username</th>
            <th>Name</th>
            <th>E-mail</th>
            <th>Role</th>
            <th>Created</th>
            <th class="right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td class="muted"><?= (int)$user['id'] ?></td>
                <td><strong><?= e($user['username']) ?></strong></td>
                <td><?= e($user['full_name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= $user['is_admin'] ? '<span class="badge">admin</span>' : '<span class="muted">user</span>' ?></td>
                <td class="muted"><?= e($user['created_at']) ?></td>
                <td class="right">
                    <div class="actions right">
                        <a class="button button-quiet button-small" href="<?= e(url('task2-users/form.php')) ?>?id=<?= (int)$user['id'] ?>">Edit</a>

                        <!-- Deleting changes data, so it goes through a POST form, not a link. -->
                        <form method="post" action="<?= e(url('task2-users/delete.php')) ?>"
                              onsubmit="return confirm('Delete user <?= e($user['username']) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <button class="button-danger button-small" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<?php render_footer(); ?>
