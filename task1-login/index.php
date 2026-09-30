<?php
declare(strict_types=1);

/**
 * Log in (tasks 1 and 2).
 *
 * Two ways in:
 *   1. the user/password pair written into lib/config.php  (task 1)
 *   2. any row of the `users` table                        (task 2)
 */

require __DIR__ . '/../lib/bootstrap.php';

if (is_logged_in()) {
    redirect(url('task5-news/index.php'));
}


/**
 * Does the typed pair match the one written into lib/config.php?
 *
 * hash_equals() instead of === : it always compares the whole string, so the
 * time it takes gives nothing away about how much of the password was right.
 */
function builtin_credentials_match(string $username, string $password): bool
{
    return hash_equals(BUILTIN_USERNAME, $username)
        && hash_equals(BUILTIN_PASSWORD, $password);
}

$username = '';
$errors   = [];

if (is_post()) {
    if (!csrf_check()) {
        $errors['form'] = 'The form expired. Please try again.';
    } else {
        $username = post('username');
        $password = post('password');

        check($errors, $username !== '', 'username', 'Please type your username.');
        check($errors, $password !== '', 'password', 'Please type your password.');

        if (!$errors) {
            $user = user_find_by_username($username);

            // Task 1: the pair from the code. It signs you in as that database
            // row, so games and news still have a real owner.
            if (builtin_credentials_match($username, $password)) {
                $user = $user ?? user_find_by_username(BUILTIN_USERNAME);
                if ($user === null) {
                    $errors['form'] = 'The built-in account has no row in the users table.';
                } else {
                    login_user($user);
                    flash('success', 'Welcome, ' . $user['username'] . '! (signed in with the built-in pair)');
                    redirect(url('task5-news/index.php'));
                }
            }

            // Task 2: a normal account from the database.
            if (!$errors && $user !== null && user_password_matches($user, $password)) {
                login_user($user);
                flash('success', 'Welcome back, ' . $user['username'] . '!');
                redirect(url('task5-news/index.php'));
            }

            // One message for both cases - it tells an attacker nothing about
            // which usernames exist.
            $errors['form'] = $errors['form'] ?? 'Wrong username or password.';
        }
    }
}

render_header('Log in', main_nav());
render_task_banner(1);
?>

<?php if (isset($errors['form'])): ?>
    <p class="alert alert-error"><?= e($errors['form']) ?></p>
<?php endif; ?>

<form class="card card-narrow" method="post" action="<?= e(url('task1-login/index.php')) ?>">
    <?= csrf_field() ?>
    <p>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" autofocus>
        <?php if (isset($errors['username'])): ?>
            <span class="field-error"><?= e($errors['username']) ?></span>
        <?php endif; ?>
    </p>
    <p>
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <span class="field-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </p>
    <p class="actions"><button type="submit">Log in</button></p>
</form>

<p class="muted">
    Seeded accounts: <code>admin / titok123</code> (administrator),
    <code>anna / anna123</code>, <code>bela / bela123</code>
</p>

<?php render_footer(); ?>
