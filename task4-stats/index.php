<?php
declare(strict_types=1);

/**
 * Statistics (task 4): how many users, how many rounds, with what result,
 * how many steps on average.
 *
 * Behind the login, like tasks 2, 3 and 5.
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$overview = stats_overview();
$perUser  = stats_per_user();
$recent   = stats_recent_games(10);
$winRate  = stats_win_rate($overview);

render_header('Statistics', main_nav());
render_task_banner(4);
?>

<div class="stat-grid">
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['users'] ?></div>
        <div class="stat-label">registered users</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['games'] ?></div>
        <div class="stat-label">rounds played</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['won'] ?></div>
        <div class="stat-label">guessed</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['abandoned'] ?></div>
        <div class="stat-label">given up</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= number_format($winRate, 1) ?>%</div>
        <div class="stat-label">win rate</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= number_format((float)$overview['avg_steps'], 2) ?></div>
        <div class="stat-label">average steps to win</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['best_steps'] ?: '&ndash;' ?></div>
        <div class="stat-label">fewest steps</div>
    </div>
    <div class="stat">
        <div class="stat-value"><?= (int)$overview['guesses'] ?></div>
        <div class="stat-label">guesses in total</div>
    </div>
</div>

<h2>Players</h2>

<?php if (!$perUser): ?>
    <p class="alert alert-info">Nobody has played yet.</p>
<?php else: ?>
    <table class="card">
        <thead>
        <tr>
            <th>Player</th>
            <th class="right">Rounds</th>
            <th class="right">Guessed</th>
            <th class="right">Average steps</th>
            <th class="right">Best</th>
            <th>Last played</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($perUser as $row): ?>
            <tr>
                <td><strong><?= e($row['username']) ?></strong></td>
                <td class="right"><?= (int)$row['games'] ?></td>
                <td class="right"><?= (int)$row['won'] ?></td>
                <td class="right"><?= $row['avg_steps'] === null ? '&ndash;' : number_format((float)$row['avg_steps'], 2) ?></td>
                <td class="right"><?= $row['best_steps'] === null ? '&ndash;' : (int)$row['best_steps'] ?></td>
                <td class="muted"><?= $row['last_played'] === null ? '&ndash;' : e($row['last_played']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Latest rounds</h2>

<?php if (!$recent): ?>
    <p class="alert alert-info">Nobody has played yet.</p>
<?php else: ?>
    <table class="card">
        <thead>
        <tr>
            <th>#</th>
            <th>Player</th>
            <th>Result</th>
            <th class="right">Steps</th>
            <th>Started</th>
            <th>Finished</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $row): ?>
            <tr>
                <td class="muted"><?= (int)$row['id'] ?></td>
                <td><?= e($row['username']) ?></td>
                <td>
                    <?php if ($row['status'] === 'won'): ?>
                        <span class="badge">guessed</span>
                    <?php elseif ($row['status'] === 'playing'): ?>
                        <span class="muted">in progress</span>
                    <?php else: ?>
                        <span class="muted">given up</span>
                    <?php endif; ?>
                </td>
                <td class="right"><?= (int)$row['steps'] ?></td>
                <td class="muted"><?= e($row['started_at']) ?></td>
                <td class="muted"><?= $row['finished_at'] === null ? '&ndash;' : e($row['finished_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php render_footer(); ?>
