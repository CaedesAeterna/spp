<?php
declare(strict_types=1);

/**
 * Statistics (task 4): how many users, how many games, with what result,
 * how many moves on average, and so on.
 */


/** The numbers shown in the tiles at the top of the statistics page. */
function stats_overview(): array
{
    return [
        'users'      => (int)db_value('SELECT COUNT(*) FROM users'),
        'games'      => (int)db_value('SELECT COUNT(*) FROM games'),
        'won'        => (int)db_value('SELECT COUNT(*) FROM games WHERE status = ?', ['won']),
        'abandoned'  => (int)db_value('SELECT COUNT(*) FROM games WHERE status = ?', ['abandoned']),
        'playing'    => (int)db_value('SELECT COUNT(*) FROM games WHERE status = ?', ['playing']),
        'guesses'    => (int)db_value('SELECT COUNT(*) FROM guesses'),
        'avg_steps'  => (float)db_value('SELECT AVG(steps) FROM games WHERE status = ?', ['won']),
        'best_steps' => (int)db_value('SELECT MIN(steps) FROM games WHERE status = ?', ['won']),
    ];
}

/**
 * One row per player: how many rounds, how many guessed, average and best step
 * count, and when they last played.
 *
 * LEFT JOIN keeps players who have never started a round; the CASE expressions
 * make AVG and MIN look only at the rounds that were actually won.
 */
function stats_per_user(): array
{
    return db_all(
        "SELECT u.username,
                COUNT(g.id)                                      AS games,
                SUM(g.status = 'won')                            AS won,
                AVG(CASE WHEN g.status = 'won' THEN g.steps END) AS avg_steps,
                MIN(CASE WHEN g.status = 'won' THEN g.steps END) AS best_steps,
                MAX(g.started_at)                                AS last_played
         FROM users u
         LEFT JOIN games g ON g.user_id = u.id
         GROUP BY u.id, u.username
         ORDER BY won DESC, games DESC, u.username"
    );
}

/** The last few rounds, newest first. Worked example - copy this shape above. */
function stats_recent_games(int $limit = 10): array
{
    $limit = max(1, min(100, $limit));       // inlined below, so keep it sane

    return db_all(
        'SELECT g.id, g.status, g.steps, g.started_at, g.finished_at, u.username
         FROM games g
         JOIN users u ON u.id = g.user_id
         ORDER BY g.id DESC
         LIMIT ' . $limit
    );
}

/** Percentage of won rounds, for the "win rate" tile. */
function stats_win_rate(array $overview): float
{
    $games = (int)$overview['games'];
    return $games === 0 ? 0.0 : round((int)$overview['won'] / $games * 100, 1);
}
