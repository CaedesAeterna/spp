<?php
declare(strict_types=1);

/** Start a new round: a new secret number, an empty step list. */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

if (!is_post() || !csrf_check()) {
    flash('error', 'Start a new number with the button on the game page.');
    redirect(url('task3-game/index.php'));
}

unset($_SESSION['last_game_id']);

$gameId = game_start(current_user_id());

if ($gameId > 0) {
    flash('success', 'A new ' . SECRET_LENGTH . '-digit number is ready. Good luck!');
} else {
    flash('error', 'A new number could not be generated.');
}

redirect(url('task3-game/index.php'));
