<?php
declare(strict_types=1);

/**
 * Write or edit news (task 5).
 *   news_form.php         -> write a new item
 *   news_form.php?id=7    -> edit item 7, if you are allowed to
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$id        = query_int('id');
$isEditing = $id > 0;
$errors    = [];
$values    = ['title' => '', 'body' => ''];

if ($isEditing) {
    $item = news_find($id);

    if ($item === null) {
        flash('error', 'That news item no longer exists.');
        redirect(url('task5-news/index.php'));
    }

    // Your own item, or anything at all when you are an administrator.
    if (!news_can_edit($item)) {
        flash('error', 'You may only edit your own news.');
        redirect(url('task5-news/index.php'));
    }

    $values = ['title' => $item['title'], 'body' => $item['body']];
}

if (is_post()) {
    if (!csrf_check()) {
        flash('error', 'The form expired. Please try again.');
        redirect($isEditing ? url('task5-news/form.php') . '?id=' . $id : url('task5-news/form.php'));
    }

    $values = ['title' => post('title'), 'body' => post('body')];
    $errors = news_validate($values);

    if (!$errors) {
        if ($isEditing) {
            if (news_update($id, $values['title'], $values['body'])) {
                flash('success', 'The news item was updated.');
                redirect(url('task5-news/index.php'));
            }
            flash('error', 'The news item could not be updated.');
        } else {
            if (news_create(current_user_id(), $values['title'], $values['body']) > 0) {
                flash('success', 'Your news item was published.');
                redirect(url('task5-news/index.php'));
            }
            flash('error', 'The news item could not be saved.');
        }
    } else {
        flash('error', 'Please fix the fields marked below.');
    }
}

render_header($isEditing ? 'Edit news' : 'Write news', main_nav());
render_task_banner(5);
?>

<form class="card" method="post"
      action="<?= e($isEditing ? url('task5-news/form.php') . '?id=' . (int)$id : url('task5-news/form.php')) ?>">
    <?= csrf_field() ?>

    <p>
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($values['title']) ?>" autofocus>
        <?php if (isset($errors['title'])): ?>
            <span class="field-error"><?= e($errors['title']) ?></span>
        <?php endif; ?>
    </p>

    <p>
        <label for="body">News text</label>
        <textarea id="body" name="body"><?= e($values['body']) ?></textarea>
        <?php if (isset($errors['body'])): ?>
            <span class="field-error"><?= e($errors['body']) ?></span>
        <?php endif; ?>
    </p>

    <p class="actions">
        <button type="submit"><?= $isEditing ? 'Save changes' : 'Publish' ?></button>
        <a class="button button-quiet" href="<?= e(url('task5-news/index.php')) ?>">Cancel</a>
    </p>
</form>

<?php render_footer(); ?>
