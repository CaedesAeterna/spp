<?php
declare(strict_types=1);

/**
 * The news list (task 5).
 *
 * Behind the login, like tasks 2, 3 and 4 - you have to be logged in to read it
 * at all. Everybody logged in may write; you edit your own, an administrator
 * edits everybody's.
 */

require __DIR__ . '/../lib/bootstrap.php';
require_login();

$news = news_all();

render_header('News', main_nav());
render_task_banner(5);
?>

<p class="actions"><a class="button" href="<?= e(url('task5-news/form.php')) ?>">Write news</a></p>

<?php if (!$news): ?>

    <p class="alert alert-info">There is no news yet.</p>

<?php else: ?>

    <?php foreach ($news as $item): ?>
        <article class="news-item">
            <h3><?= e($item['title']) ?></h3>
            <p class="news-meta">
                by <strong><?= e($item['author']) ?></strong>
                &middot; <?= e($item['created_at']) ?>
                <?php if (!empty($item['updated_at'])): ?>
                    &middot; edited <?= e($item['updated_at']) ?>
                <?php endif; ?>
            </p>

            <!-- nl2br keeps the line breaks the writer typed; e() runs first,
                 so the text itself can never inject HTML -->
            <p class="news-body"><?= nl2br(e($item['body'])) ?></p>

            <?php if (news_can_edit($item)): ?>
                <div class="actions">
                    <a class="button button-quiet button-small" href="<?= e(url('task5-news/form.php')) ?>?id=<?= (int)$item['id'] ?>">Edit</a>
                    <form method="post" action="<?= e(url('task5-news/delete.php')) ?>"
                          onsubmit="return confirm('Delete this news item?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                        <button class="button-danger button-small" type="submit">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>

<?php endif; ?>

<?php render_footer(); ?>
