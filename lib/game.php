<?php
declare(strict_types=1);

/**
 * The number guessing game (tasks 3 and 4).
 *
 * The session remembers WHICH round is being played ($_SESSION['game_id']),
 * the database stores the round itself and every single step, so the
 * statistics page can count them later.
 */

const MARK_EXACT   = 'exact';     // right digit, right place
const MARK_PRESENT = 'present';   // the number contains it, but somewhere else
const MARK_ABSENT  = 'absent';    // not in the number at all

/**
 * Make a new secret number as a string of SECRET_LENGTH DIFFERENT digits.
 * The digits are drawn out of a bag holding 0-9 and each one is taken out of
 * the bag once drawn, so no digit can come up twice: 28061 is a possible
 * secret, 28066 is not. (This caps SECRET_LENGTH at 10.)
 */
function game_generate_secret(): string
{
    $bag    = range(0, 9);
    $secret = '';
    for ($i = 0; $i < SECRET_LENGTH; $i++) {
        $pick    = random_int(0, count($bag) - 1);
        $secret .= $bag[$pick];
        array_splice($bag, $pick, 1);     // out of the bag, cannot be drawn again
    }
    return $secret;
}


/**
 * Compare one guess with the secret, digit by digit.
 * Returns SECRET_LENGTH marks: MARK_EXACT, MARK_PRESENT or MARK_ABSENT.
 *
 * It still takes two passes, because a GUESS may repeat a digit even though the
 * secret no longer does - and typing 2 2 2 2 2 is a normal way to ask "is there
 * a 2 in there". With secret 1 2 3 and guess 2 2 2 a single loop would call the
 * first 2 "present" even though the secret's only 2 is already used up by the
 * exact hit in the middle. So pass 1 claims the exact hits and subtracts them
 * from a counter of what the secret still has to give, and only pass 2 hands
 * out MARK_PRESENT from what is left.
 */
function game_evaluate(string $guess, string $secret): array
{
    $length = strlen($secret);
    $marks  = array_fill(0, $length, MARK_ABSENT);

    // How many of each digit the secret can still account for.
    $left = array_count_values(str_split($secret));

    // Pass 1 - right digit, right place. Each one uses up a secret digit.
    for ($i = 0; $i < $length; $i++) {
        if ($guess[$i] === $secret[$i]) {
            $marks[$i] = MARK_EXACT;
            $left[$guess[$i]]--;
        }
    }

    // Pass 2 - in the number, but somewhere else, while one is still left over.
    for ($i = 0; $i < $length; $i++) {
        if ($marks[$i] === MARK_EXACT) {
            continue;
        }
        $digit = $guess[$i];
        if (($left[$digit] ?? 0) > 0) {
            $marks[$i] = MARK_PRESENT;
            $left[$digit]--;
        }
    }

    return $marks;
}

/* ------------------------------------------------------------------ *
 * Marks are stored as one letter per digit: E = exact, P = present, A = absent
 * ------------------------------------------------------------------ */

function game_marks_encode(array $marks): string
{
    $letters = [MARK_EXACT => 'E', MARK_PRESENT => 'P', MARK_ABSENT => 'A'];
    $encoded = '';
    foreach ($marks as $mark) {
        $encoded .= $letters[$mark] ?? 'A';
    }
    return $encoded;
}

function game_marks_decode(string $encoded): array
{
    $byLetter = ['E' => MARK_EXACT, 'P' => MARK_PRESENT, 'A' => MARK_ABSENT];
    $out = [];
    foreach (str_split($encoded) as $letter) {
        $out[] = $byLetter[$letter] ?? MARK_ABSENT;
    }
    return $out;
}

/* ------------------------------------------------------------------ *
 * Rounds - session pointer + database rows
 * ------------------------------------------------------------------ */

/**
 * Start a new round for this user and return its id (0 if no usable secret
 * came back). Any round still running is closed as "abandoned" first.
 */
function game_start(int $userId): int
{
    session_boot();

    $running = game_current($userId);
    if ($running !== null) {
        game_abandon((int)$running['id']);
    }

    $secret = game_generate_secret();
    if (!game_secret_is_valid($secret)) {
        unset($_SESSION['game_id']);
        return 0;
    }

    $gameId = db_insert(
        'INSERT INTO games (user_id, secret, status) VALUES (?, ?, ?)',
        [$userId, $secret, 'playing']
    );

    $_SESSION['game_id'] = $gameId;
    return $gameId;
}

/** The round this user is playing right now, or null. */
function game_current(int $userId): ?array
{
    session_boot();

    $gameId = (int)($_SESSION['game_id'] ?? 0);
    if ($gameId > 0) {
        $game = db_one(
            'SELECT * FROM games WHERE id = ? AND user_id = ? AND status = ?',
            [$gameId, $userId, 'playing']
        );
        if ($game !== null) {
            return $game;
        }
        unset($_SESSION['game_id']);      // finished or not ours any more
    }

    // The session may have been lost (new browser) while a round is still open.
    $game = db_one(
        'SELECT * FROM games WHERE user_id = ? AND status = ? ORDER BY id DESC LIMIT 1',
        [$userId, 'playing']
    );
    if ($game !== null) {
        $_SESSION['game_id'] = (int)$game['id'];
    }
    return $game;
}

function game_find(int $gameId): ?array
{
    return db_one('SELECT * FROM games WHERE id = ?', [$gameId]);
}

/** Every step of a round, oldest first - "save all steps". */
function game_steps(int $gameId): array
{
    return db_all(
        'SELECT * FROM guesses WHERE game_id = ? ORDER BY step_no',
        [$gameId]
    );
}

/** Close a round without a win (the player asked for a new number). */
function game_abandon(int $gameId): void
{
    db_run(
        'UPDATE games SET status = ?, finished_at = NOW() WHERE id = ? AND status = ?',
        ['abandoned', $gameId, 'playing']
    );
    session_boot();
    if ((int)($_SESSION['game_id'] ?? 0) === $gameId) {
        unset($_SESSION['game_id']);
    }
}

/**
 * Record one guess: evaluate it, save the step, and close the round on a win.
 * Returns the stored step so the page can show it right away.
 */
function game_submit(array $game, string $guess): array
{
    $marks  = game_evaluate($guess, $game['secret']);
    $stepNo = (int)$game['steps'] + 1;

    $step = [
        'step_no'       => $stepNo,
        'guess'         => $guess,
        'marks'         => game_marks_encode($marks),
        'exact_count'   => count(array_keys($marks, MARK_EXACT, true)),
        'present_count' => count(array_keys($marks, MARK_PRESENT, true)),
    ];

    db_insert(
        'INSERT INTO guesses (game_id, step_no, guess, marks, exact_count, present_count)
         VALUES (?, ?, ?, ?, ?, ?)',
        [
            (int)$game['id'], $step['step_no'], $step['guess'],
            $step['marks'], $step['exact_count'], $step['present_count'],
        ]
    );

    $won = $guess === $game['secret'];

    db_run(
        'UPDATE games SET steps = ?, status = ?, finished_at = ? WHERE id = ?',
        [
            $stepNo,
            $won ? 'won' : 'playing',
            $won ? date('Y-m-d H:i:s') : null,
            (int)$game['id'],
        ]
    );

    if ($won) {
        session_boot();
        unset($_SESSION['game_id']);
    }

    $step['won'] = $won;
    return $step;
}

/* ------------------------------------------------------------------ *
 * Input
 * ------------------------------------------------------------------ */

/**
 * Turn the five input boxes into one string: ['4','8','2','1','3'] -> '48213'.
 * Returns null when a box is empty or holds something that is not a digit.
 */
function game_read_guess(array $digits): ?string
{
    $guess = '';
    for ($i = 0; $i < SECRET_LENGTH; $i++) {
        $digit = trim((string)($digits[$i] ?? ''));
        if (strlen($digit) !== 1 || !ctype_digit($digit)) {
            return null;
        }
        $guess .= $digit;
    }
    return $guess;
}

/** SECRET_LENGTH digits, all different, and nothing else. */
function game_secret_is_valid(string $secret): bool
{
    return strlen($secret) === SECRET_LENGTH
        && ctype_digit($secret)
        && count(array_unique(str_split($secret))) === SECRET_LENGTH;
}
