-- ============================================================
--  SPP homework - database
--
--  The application runs this file automatically on the first request
--  (see lib/db.php), so nothing has to be imported by hand.
--  You can also import it yourself:
--      phpMyAdmin -> Import -> choose this file
--      /opt/lampp/bin/mysql -u root < sql/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `spp`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_hungarian_ci;

USE `spp`;

-- ------------------------------------------------------------
--  users - login (task 1/2) and ownership of games and news
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(50)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name`     VARCHAR(100) NOT NULL DEFAULT '',
    `email`         VARCHAR(190) NOT NULL DEFAULT '',
    `is_admin`      TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_username` (`username`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_hungarian_ci;

-- ------------------------------------------------------------
--  games - one row per round of the guessing game (task 4)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `games` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL,
    `secret`      VARCHAR(10)  NOT NULL,
    `status`      ENUM('playing','won','abandoned') NOT NULL DEFAULT 'playing',
    `steps`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `started_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `finished_at` DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ix_games_user` (`user_id`),
    KEY `ix_games_status` (`status`),
    CONSTRAINT `fk_games_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_hungarian_ci;

-- ------------------------------------------------------------
--  guesses - "save all steps" (task 3/4)
--  marks: one letter per digit - E = exact, P = present, A = absent
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `guesses` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id`       INT UNSIGNED NOT NULL,
    `step_no`       SMALLINT UNSIGNED NOT NULL,
    `guess`         VARCHAR(10)  NOT NULL,
    `marks`         VARCHAR(10)  NOT NULL DEFAULT '',
    `exact_count`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `present_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_guesses_step` (`game_id`, `step_no`),
    CONSTRAINT `fk_guesses_game` FOREIGN KEY (`game_id`)
        REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_hungarian_ci;

-- ------------------------------------------------------------
--  news - everybody may write; you edit your own, admin edits all (task 5)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `title`      VARCHAR(200) NOT NULL,
    `body`       TEXT         NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ix_news_user` (`user_id`),
    CONSTRAINT `fk_news_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_hungarian_ci;

-- ------------------------------------------------------------
--  Starting data. The hashes were made with password_hash().
--      admin / titok123   (administrator)
--      anna  / anna123
--      bela  / bela123
-- ------------------------------------------------------------
INSERT IGNORE INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `is_admin`) VALUES
    (1, 'admin', '$2y$10$ortafDZqFLSoU/vC3xW61.nkPcHluMt0OwzqpID34TCB2HOvA2khi', 'Site Administrator', 'admin@example.com', 1),
    (2, 'anna',  '$2y$10$Rv1K8qSmuTZiMpE7iqqhfOCRuriAp7hN/wjs/Z2xb2VaHMeUZs9wu', 'Anna Kovacs',        'anna@example.com',  0),
    (3, 'bela',  '$2y$10$/7mxVolET3n2CpN1oKakBuUePoHZWB705D69tQVnP4bXiA7VEj29a', 'Bela Nagy',          'bela@example.com',  0);

INSERT IGNORE INTO `news` (`id`, `user_id`, `title`, `body`) VALUES
    (1, 1, 'Welcome', 'All five task areas need a login. Everybody logged in may write news here; you edit your own, an administrator edits all.'),
    (2, 2, 'The guessing game is open', 'Five digits, all of them different. Green means right place, amber means the number contains that digit somewhere else.');
