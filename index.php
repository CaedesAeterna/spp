<?php
declare(strict_types=1);

/**
 * The front door: one card per task.
 *
 * The five tasks are areas of this one application, and this page is the index
 * of them - open it, pick a task, present it, come back. Tasks 2-5 all need a
 * login (task 1 IS the login), so a guest sees this index and the login form.
 */

require __DIR__ . '/lib/bootstrap.php';

render_header('Five tasks, one application', main_nav());
?>

<?php if (is_logged_in()): ?>
    <p class="alert alert-info">
        Logged in as <strong><?= e(current_user()['username']) ?></strong><?= is_admin() ? ' (administrator)' : '' ?>.
        <a href="<?= e(url('task1-login/logout.php')) ?>">Log out</a> to show the guest side.
    </p>
<?php else: ?>
    <p class="alert alert-info">
        Not logged in - this is what a guest sees. Accounts:
        <code>admin / titok123</code> (administrator),
        <code>anna / anna123</code>, <code>bela / bela123</code>.
    </p>
<?php endif; ?>

<?php foreach (tasks() as $number => $task): ?>
    <section class="task-card">
        <div class="task-card-head">
            <span class="task-no">Task <?= (int)$number ?></span>
            <h2><?= e($task['title']) ?></h2>
        </div>

        <p class="muted"><?= e($task['blurb']) ?></p>

        <ul>
            <?php foreach ($task['shows'] as $point): ?>
                <li><?= e($point) ?></li>
            <?php endforeach; ?>
        </ul>

        <p class="muted"><strong>To demonstrate:</strong> <?= e($task['demo']) ?></p>

        <p class="actions">
            <a class="button" href="<?= e(url($task['url'])) ?>">Open task <?= (int)$number ?></a>
        </p>
    </section>
<?php endforeach; ?>

<?php render_footer(); ?>
