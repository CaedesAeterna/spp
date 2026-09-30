<?php
declare(strict_types=1);

/** Log out: empty the session, then back to the news page. */

require __DIR__ . '/../lib/bootstrap.php';

logout_user();
flash('info', 'You have been logged out.');
redirect(url('task5-news/index.php'));
