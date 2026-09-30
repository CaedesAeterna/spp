<?php
declare(strict_types=1);

/**
 * What each of the five tasks asks for, in one place.
 *
 * The home page builds its list from this, and every task page shows its own
 * entry as a drop-down under the title - so while presenting you can open the
 * list and walk through the requirements one by one.
 */

function tasks(): array
{
    return [
        1 => [
            'title' => 'Login form',
            'url'   => 'task1-login/index.php',
            'blurb' => 'A form with two inputs, checked against a pair written into the code.',
            'shows' => [
                'a form with two inputs: username and password',
                'the pair is set in the code - BUILTIN_USERNAME / BUILTIN_PASSWORD in lib/config.php',
                'a wrong pair gives an error message; empty fields are caught separately',
                'a matching pair logs you in',
            ],
            'demo'  => 'Try admin / wrong, then admin / titok123.',
        ],
        2 => [
            'title' => 'Users',
            'url'   => 'task2-users/index.php',
            'blurb' => 'Session plus database: list, create, edit and delete users.',
            'shows' => [
                'the login goes through the database and is remembered in the session',
                'the user list, with create / edit / delete',
                'a message in every case - both when it worked and when it did not',
                'the tables build themselves from sql/schema.sql on the first request',
            ],
            'demo'  => 'Create a user, edit it, try a duplicate name, delete it, then try deleting yourself.',
        ],
        3 => [
            'title' => 'Guess the number',
            'url'   => 'task3-game/index.php',
            'blurb' => 'A five-digit number, five input boxes, and per-digit feedback.',
            'shows' => [
                'the running round is held in the session ($_SESSION[\'game_id\'])',
                'a 5-digit number whose five digits are all different',
                'five input boxes, one digit each',
                'green = right digit in the right place, amber = the number contains it elsewhere',
                'guessing it ends the round; a new number can be generated at any time',
                'every step is saved and stays on screen',
            ],
            'demo'  => 'Guess once to show the colours, press New number, then guess the number out.',
        ],
        4 => [
            'title' => 'Statistics',
            'url'   => 'task4-stats/index.php',
            'blurb' => 'How many users, how many rounds, with what result and how many moves.',
            'shows' => [
                'everything is read back out of the database, not the session',
                'users, rounds played, guessed vs given up, win rate',
                'average and fewest steps to a win',
                'a per-player table and the latest rounds',
            ],
            'demo'  => 'Play a round as one user, then open this page and point at the numbers that moved.',
        ],
        5 => [
            'title' => 'News',
            'url'   => 'task5-news/index.php',
            'blurb' => 'Everyone may write; you edit your own, an administrator edits all.',
            'shows' => [
                'reading the news needs a login too, like tasks 2-4',
                'any logged-in user may write news (title + text, stored in the news table)',
                'you get Edit/Delete on your own items only',
                'an administrator gets Edit/Delete on everybody\'s items',
                'the rule is enforced on the pages too, not only by hiding buttons',
            ],
            'demo'  => 'Write one as anna, then log in as admin and edit anna\'s.',
        ],
    ];
}

/** One task's entry. */
function task(int $number): array
{
    return tasks()[$number] ?? ['title' => '', 'url' => '', 'blurb' => '', 'shows' => [], 'demo' => ''];
}
