<?php
declare(strict_types=1);

/**
 * The page frame every screen shares: <head>, header bar, messages, footer.
 *
 *   render_header('Users', [['label' => 'Back', 'href' => url('task2-users/index.php')]]);
 *   ... your page ...
 *   render_footer();
 */

/** Open the page and print the header bar + any queued flash messages. */
function render_header(string $title, array $nav = []): void
{
    $appName = defined('APP_NAME') ? APP_NAME : 'SPP';
    $user    = function_exists('current_user') ? current_user() : null;
    ?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> &middot; <?= e($appName) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <span class="brand"><?= e($appName) ?></span>
        <nav>
            <?php foreach ($nav as $item): ?>
                <a href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php if ($user !== null): ?>
            <span class="who">
                <?= e($user['username']) ?><?= !empty($user['is_admin']) ? ' <span class="badge">admin</span>' : '' ?>
            </span>
        <?php endif; ?>
    </div>
</header>
<main>
    <h1><?= e($title) ?></h1>
    <?php render_flashes(); ?>
<?php
}

/** Print (and clear) the queued messages. Used by render_header(). */
function render_flashes(): void
{
    if (!function_exists('flash_take')) {
        return;                              // pages without a session have none
    }
    foreach (flash_take() as $flash) {
        $type = in_array($flash['type'], ['success', 'error', 'info'], true) ? $flash['type'] : 'info';
        echo '<p class="alert alert-' . $type . '">' . e($flash['message']) . '</p>';
    }
}

/**
 * The drop-down under a page title that says which task this page is, and what
 * it is meant to show. Open it on camera and read down the list.
 */
function render_task_banner(int $number): void
{
    $task = task($number);
    if ($task['title'] === '') {
        return;
    }
    ?>
    <details class="task-strip">
        <summary>
            <span class="task-no">Task <?= $number ?></span>
            <?= e($task['title']) ?>
            <span class="muted">&mdash; <?= e($task['blurb']) ?></span>
        </summary>
        <ul>
            <?php foreach ($task['shows'] as $point): ?>
                <li><?= e($point) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="muted"><strong>To demonstrate:</strong> <?= e($task['demo']) ?></p>
    </details>
    <?php
}

/** Print one message straight away, without going through the session. */
function render_message(string $type, string $message): void
{
    echo '<p class="alert alert-' . e($type) . '">' . e($message) . '</p>';
}

/** Close the page. */
function render_footer(): void
{
    $appName = defined('APP_NAME') ? APP_NAME : 'SPP';
    ?>
</main>
<footer class="foot"><?= e($appName) ?> &middot; SPP homework</footer>
</body>
</html>
<?php
}

