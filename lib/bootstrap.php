<?php
declare(strict_types=1);

/** Loaded by every page: settings, helpers, session, database, layout. */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/game.php';
require_once __DIR__ . '/stats.php';
require_once __DIR__ . '/news.php';
require_once __DIR__ . '/tasks.php';
require_once __DIR__ . '/layout.php';

session_boot();

/**
 * The links in the header bar. Tasks 2-5 all need a login, so a guest is only
 * offered the task index and the login form.
 */
function main_nav(): array
{
    $nav = [
        ['label' => 'Tasks', 'href' => url('index.php')],
    ];

    if (is_logged_in()) {
        $nav[] = ['label' => 'News',    'href' => url('task5-news/index.php')];
        $nav[] = ['label' => 'Play',    'href' => url('task3-game/index.php')];
        $nav[] = ['label' => 'Stats',   'href' => url('task4-stats/index.php')];
        $nav[] = ['label' => 'Users',   'href' => url('task2-users/index.php')];
        $nav[] = ['label' => 'Log out', 'href' => url('task1-login/logout.php')];
    } else {
        $nav[] = ['label' => 'Log in',  'href' => url('task1-login/index.php')];
    }

    return $nav;
}
