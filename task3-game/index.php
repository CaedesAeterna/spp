<?php
declare(strict_types=1);

/**
 * The guessing game (tasks 3 and 4).
 *
 * Five boxes, one digit each. After every guess the page shows, digit by digit,
 * what was right. Every step is stored, so it stays on screen and the
 * statistics page can count it later.
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$userId = current_user_id();

/* ---------------------------------------------------------------- *
 * A guess was submitted
 * ---------------------------------------------------------------- */
if (is_post()) {
    if (!csrf_check()) {
        flash('error', 'The form expired. Please try again.');
        redirect(url('task3-game/index.php'));
    }

    $game = game_current($userId);

    if ($game === null) {
        flash('error', 'There is no round running. Start a new number first.');
        redirect(url('task3-game/index.php'));
    }

    $digits = $_POST['digit'] ?? [];
    $guess  = is_array($digits) ? game_read_guess($digits) : null;

    if ($guess === null) {
        flash('error', 'Please type exactly one digit (0-9) in each of the ' . SECRET_LENGTH . ' boxes.');
        redirect(url('task3-game/index.php'));
    }

    $step = game_submit($game, $guess);

    if ($step['won']) {
        $_SESSION['last_game_id'] = (int)$game['id'];
        flash('success', 'You found it! ' . $guess . ' in ' . $step['step_no'] . ' steps.');
    } else {
        flash(
            'info',
            'Step ' . $step['step_no'] . ': ' . $step['exact_count'] . ' digit(s) in the right place, '
            . $step['present_count'] . ' more that the number contains somewhere else.'
        );
    }

    // Redirect after POST, so a refresh does not send the guess twice.
    redirect(url('task3-game/index.php'));
}

/* ---------------------------------------------------------------- *
 * Show the page
 * ---------------------------------------------------------------- */
$game     = game_current($userId);
$finished = null;

if ($game === null && !empty($_SESSION['last_game_id'])) {
    $finished = game_find((int)$_SESSION['last_game_id']);
    if ($finished !== null && (int)$finished['user_id'] !== $userId) {
        $finished = null;                    // not ours, do not show it
    }
}

$shown = $game ?? $finished;                 // whose steps the table lists
$steps = $shown !== null ? game_steps((int)$shown['id']) : [];

render_header('Guess the number', main_nav());
render_task_banner(3);
?>

<!-- "In all cases, give the possibility to generate a new number" -->
<form method="post" action="<?= e(url('task3-game/new.php')) ?>" class="actions" style="margin-bottom:1.25rem">
    <?= csrf_field() ?>
    <button type="submit"><?= $game !== null ? 'New number' : 'Start a new number' ?></button>
    <?php if ($game !== null): ?>
        <span class="muted">this gives up the round you are playing</span>
    <?php endif; ?>
</form>

<?php if ($finished !== null && $finished['status'] === 'won'): ?>
    <p class="alert alert-success">
        Round #<?= (int)$finished['id'] ?> is over - the number was
        <strong><?= e($finished['secret']) ?></strong>, found in
        <strong><?= (int)$finished['steps'] ?></strong> steps.
    </p>
<?php endif; ?>

<?php if ($game === null && $finished === null): ?>

    <p class="alert alert-info">No round is running. Press the button above to get a number.</p>

<?php elseif ($game !== null): ?>

    <?php if (SHOW_SECRET_TO_ADMIN && is_admin()): ?>
        <p class="alert alert-info">
            Administrator view, for presenting: the number is
            <strong><?= e($game['secret']) ?></strong>.
            Turn this off with <code>SHOW_SECRET_TO_ADMIN</code> in <code>lib/config.php</code>.
        </p>
    <?php endif; ?>

    <div class="card">
        <form method="post" action="<?= e(url('task3-game/index.php')) ?>">
            <?= csrf_field() ?>
            <label>Your guess</label>
            <div class="digits">
                <?php for ($i = 0; $i < SECRET_LENGTH; $i++): ?>
                    <input type="text" name="digit[<?= $i ?>]"
                           inputmode="numeric" pattern="[0-9]" maxlength="1"
                           aria-label="Digit <?= $i + 1 ?>"
                           <?= $i === 0 ? 'autofocus' : '' ?>>
                <?php endfor; ?>
            </div>
            <p class="actions" style="margin-top:1rem">
                <button type="submit">Guess</button>
                <span class="muted">round #<?= (int)$game['id'] ?>, step <?= (int)$game['steps'] + 1 ?></span>
            </p>
        </form>
    </div>

<?php endif; ?>

<?php if ($steps): ?>

    <h2>Steps so far</h2>

    <p class="legend">
        <span><span class="swatch digit-exact"></span> right digit, right place</span>
        <span><span class="swatch digit-present"></span> the number contains it elsewhere</span>
        <span><span class="swatch digit-absent"></span> not in the number</span>
    </p>

    <div class="card">
        <?php foreach (array_reverse($steps) as $step): ?>
            <div class="guess-row">
                <span class="muted" style="width:2rem"><?= (int)$step['step_no'] ?>.</span>
                <?php
                $marks  = game_marks_decode((string)$step['marks']);
                $digits = str_split((string)$step['guess']);
                ?>
                <?php foreach ($digits as $index => $digit): ?>
                    <span class="digit digit-<?= e($marks[$index] ?? MARK_ABSENT) ?>"><?= e($digit) ?></span>
                <?php endforeach; ?>
                <span class="muted">
                    <?= (int)$step['exact_count'] ?> exact,
                    <?= (int)$step['present_count'] ?> elsewhere
                    &middot; <?= e(substr((string)$step['created_at'], 11)) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php render_footer(); ?>
