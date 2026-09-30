# SPP homework

One PHP application, five tasks. Open **http://localhost/spp/** — the front
page lists the five tasks and links to each one.

The database creates itself from `sql/schema.sql` on the first request, so
nothing has to be imported. MySQL settings are in `lib/config.php`
(XAMPP defaults: `root`, no password, database `spp`).

## Accounts

| user | password | |
|------|----------|---|
| `admin` | `titok123` | administrator |
| `anna` | `anna123` | |
| `bela` | `bela123` | |

## Where each task is

| | | |
|---|---|---|
| 1 | login form, pair written in the code | `task1-login/` + `BUILTIN_*` in `lib/config.php` |
| 2 | session + database, user CRUD, messages | `task2-users/`, `lib/users.php` |
| 3 | 5-digit game, per-digit feedback, steps kept | `task3-game/`, `lib/game.php` |
| 4 | statistics out of the database | `task4-stats/`, `lib/stats.php` |
| 5 | news, own vs administrator rights | `task5-news/`, `lib/news.php` |

`lib/` is shared by all of them: `db.php` (PDO + auto-install), `session.php`
(session, messages, CSRF), `helpers.php` (`e()`, `url()`), `layout.php`.
Links are built with `url('task3-game/index.php')` so the pages work from any
folder depth.

## Resetting the data

```sql
DELETE FROM guesses; DELETE FROM games;
DELETE FROM news WHERE id > 2; DELETE FROM users WHERE id > 3;
```
