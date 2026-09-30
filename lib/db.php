<?php
declare(strict_types=1);

/**
 * PDO connection + automatic database/table creation.
 *
 * The first request creates the database (if missing) and runs sql/schema.sql,
 * so the app works on a fresh XAMPP with no manual import. The same
 * sql/schema.sql is the file you hand in / import by hand if you prefer.
 */

/** The shared PDO connection. Opens it on first use. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Report rows MATCHED rather than CHANGED, so re-saving a row with the
        // same values still counts as a successful update.
        PDO::MYSQL_ATTR_FOUND_ROWS   => true,
    ];

    try {
        $pdo = new PDO($dsn . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
    } catch (PDOException $first) {
        // Most likely the database does not exist yet - create it and retry.
        try {
            $server = new PDO($dsn, DB_USER, DB_PASS, $options);
            $server->exec(
                'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`'
                . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_hungarian_ci'
            );
            $pdo = new PDO($dsn . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
        } catch (PDOException $second) {
            db_fail($first, $second);
        }
    }

    db_install_schema($pdo);
    return $pdo;
}

/** Run sql/schema.sql once, when the marker table is still missing. */
function db_install_schema(PDO $pdo): void
{
    $marker = defined('DB_MARKER_TABLE') ? DB_MARKER_TABLE : 'users';
    $exists = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($marker))->fetch();
    if ($exists) {
        return;
    }

    $file = defined('DB_SCHEMA_FILE') ? DB_SCHEMA_FILE : __DIR__ . '/../sql/schema.sql';
    if (!is_readable($file)) {
        throw new RuntimeException('Schema file not found: ' . $file);
    }

    foreach (sql_statements((string)file_get_contents($file)) as $statement) {
        $pdo->exec($statement);
    }
}

/**
 * Cut a .sql file into single statements.
 * Handles `-- comments` and quoted strings that contain a semicolon.
 */
function sql_statements(string $sql): array
{
    $statements = [];
    $current    = '';
    $quote      = null;

    foreach (preg_split('/\R/', $sql) as $line) {
        $trimmed = ltrim($line);
        if ($quote === null && ($trimmed === '' || str_starts_with($trimmed, '--'))) {
            continue;                       // blank line or comment, outside a string
        }

        $length = strlen($line);
        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($quote !== null) {
                $current .= $char;
                if ($char === '\\' && $i + 1 < $length) {
                    $current .= $line[++$i];            // escaped char inside a string
                } elseif ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }
        $current .= "\n";
    }

    if (trim($current) !== '') {
        $statements[] = trim($current);
    }
    return $statements;
}

/* ------------------------------------------------------------------ *
 * Short query helpers - they keep the pages readable.
 * Always pass values as parameters, never glue them into the SQL.
 * ------------------------------------------------------------------ */

/** Every matching row. */
function db_all(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

/** The first matching row, or null. */
function db_one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetch() ?: null;
}

/** The first column of the first row (COUNT, AVG, ...), or null. */
function db_value(string $sql, array $params = []): mixed
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $value = $statement->fetchColumn();
    return $value === false ? null : $value;
}

/** Run an INSERT/UPDATE/DELETE. Returns the number of affected rows. */
function db_run(string $sql, array $params = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->rowCount();
}

/** Run an INSERT and return the new id. */
function db_insert(string $sql, array $params = []): int
{
    db_run($sql, $params);
    return (int)db()->lastInsertId();
}

/** Stop with a readable message instead of a stack trace full of passwords. */
function db_fail(PDOException $first, PDOException $second): never
{
    http_response_code(500);
    $hint = 'Could not connect to MySQL as "' . DB_USER . '" on ' . DB_HOST . ':' . DB_PORT . '.';
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $hint . "\n" . $second->getMessage() . "\n");
        exit(1);
    }
    echo '<!doctype html><meta charset="utf-8"><title>Database error</title>';
    echo '<div style="font:16px/1.6 system-ui;max-width:42rem;margin:4rem auto;padding:0 1rem">';
    echo '<h1>Database error</h1><p>' . htmlspecialchars($hint) . '</p>';
    echo '<p>Start MySQL in the XAMPP control panel, then check <code>lib/config.php</code>.</p>';
    echo '<pre style="white-space:pre-wrap;background:#f4f4f5;padding:1rem;border-radius:.5rem">';
    echo htmlspecialchars($second->getMessage());
    echo '</pre></div>';
    exit;
}
