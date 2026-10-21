<?php
/**
 * Supabase Postgres connection for Excellence Academy.
 * Existing API queries were written for MySQL. They are translated here
 * so the rest of the app can stay as it is.
 */

function appEnv(string $key, string $default = ''): string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }
    return $default;
}

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $url = appEnv('DATABASE_URL', appEnv('SUPABASE_DB_URL'));
        if ($url === '') {
            throw new Exception('Database is not configured. Set DATABASE_URL to the Supabase connection string.');
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            throw new Exception('DATABASE_URL is not a valid Postgres connection string.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme !== 'postgres' && $scheme !== 'postgresql') {
            throw new Exception('DATABASE_URL must start with postgresql://');
        }

        if (!in_array('pgsql', PDO::getAvailableDrivers(), true)) {
            throw new Exception('This PHP runtime does not include the PostgreSQL driver.');
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s;connect_timeout=5',
            $parts['host'],
            $parts['port'] ?? 5432,
            ltrim($parts['path'] ?? '/postgres', '/'),
            $query['sslmode'] ?? 'require'
        );

        $this->conn = new PDO($dsn, rawurldecode($parts['user'] ?? ''), rawurldecode($parts['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        $this->ensureSchema();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }

    public function query($sql, $params = []) {
        try {
            $statements = $this->toPostgres($sql);
            $params = $this->normalizeParams($params);
            $last = null;
            $lastIndex = count($statements) - 1;
            foreach ($statements as $index => $statement) {
                $last = $this->conn->prepare($statement);
                $last->execute($index === $lastIndex ? $params : []);
            }
            return $last;
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($sql, $params = []) {
        $sql = rtrim($sql, " \t\n\r;");
        if (!preg_match('/\bRETURNING\b/i', $sql)) {
            $sql .= ' RETURNING id';
        }
        $row = $this->query($sql, $params)->fetch();
        return $row['id'] ?? null;
    }

    public function update($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($sql, $params = []) {
        return $this->update($sql, $params);
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollBack();
    }

    public function inTransaction() {
        return $this->conn->inTransaction();
    }

    private function normalizeParams(array $params): array {
        foreach ($params as $key => $value) {
            if ($value === true) {
                $params[$key] = 1;
            } elseif ($value === false) {
                $params[$key] = 0;
            }
        }
        return $params;
    }

    private function toPostgres($sql): array {
        if (preg_match("/SHOW\\s+TABLES\\s+LIKE\\s+['\"]([^'\"]+)['\"]/i", $sql, $matches)) {
            return ["SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = " . $this->conn->quote($matches[1])];
        }

        if (preg_match('/SHOW\\s+COLUMNS\\s+FROM\\s+`?([A-Za-z0-9_]+)`?/i', $sql, $matches)) {
            $table = $this->conn->quote($matches[1]);
            return ["SELECT column_name AS \"Field\", data_type AS \"Type\" FROM information_schema.columns WHERE table_schema = 'public' AND table_name = {$table} ORDER BY ordinal_position"];
        }

        if (preg_match('/ALTER\\s+TABLE\\s+`?([A-Za-z0-9_]+)`?\\s+CHANGE\\s+COLUMN\\s+`?([A-Za-z0-9_]+)`?\\s+`?([A-Za-z0-9_]+)`?\\s+(.+)$/i', $sql, $matches)) {
            $table = $matches[1];
            $from = $matches[2];
            $to = $matches[3];
            $type = $this->translateTypes(trim($matches[4], " \t;"));
            return [
                "ALTER TABLE {$table} RENAME COLUMN {$from} TO {$to}",
                "ALTER TABLE {$table} ALTER COLUMN {$to} TYPE {$type} USING {$to}::{$type}",
            ];
        }

        $sql = str_replace('`', '', $sql);
        $sql = preg_replace('/\\s+ON\\s+DUPLICATE\\s+KEY\\s+UPDATE\\s+.+$/i', ' ON CONFLICT DO NOTHING', $sql);

        if (preg_match('/^\\s*(CREATE|ALTER)\\b/i', $sql)) {
            $sql = preg_replace('/\\s+ON\\s+UPDATE\\s+CURRENT_TIMESTAMP/i', '', $sql);
            $sql = preg_replace('/\\s+AFTER\\s+[A-Za-z_][A-Za-z0-9_]*/i', '', $sql);
            $sql = $this->translateTypes($sql);
            $sql = preg_replace('/,?\\s*(UNIQUE\\s+)?(INDEX|KEY)\\s+[A-Za-z0-9_]+\\s*\\([^)]*\\)/i', '', $sql);
            $sql = preg_replace('/\\)\\s*ENGINE\\s*=\\s*\\w+.*/i', ')', $sql);
            $sql = preg_replace('/\\)\\s*DEFAULT\\s+CHARSET\\s*=\\s*\\w+.*/i', ')', $sql);
            $sql = preg_replace('/,\\s*,/', ',', $sql);
            $sql = preg_replace('/,\\s*\\)/', ')', $sql);
        }

        return [trim($sql)];
    }

    private function translateTypes(string $sql): string {
        $sql = preg_replace('/\\bUNSIGNED\\b/i', '', $sql);
        $sql = preg_replace('/\\b(?:TINY|MEDIUM|LONG)TEXT\\b/i', 'TEXT', $sql);
        $sql = preg_replace('/\\bDATETIME\\b/i', 'TIMESTAMP', $sql);
        $sql = preg_replace('/\\bBIGINT(?:\\(\\d+\\))?\\s+AUTO_INCREMENT\\s+PRIMARY\\s+KEY/i', 'BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY', $sql);
        $sql = preg_replace('/\\bINT(?:EGER)?(?:\\(\\d+\\))?\\s+AUTO_INCREMENT\\s+PRIMARY\\s+KEY/i', 'INTEGER GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY', $sql);
        $sql = preg_replace('/\\bINT\\(\\d+\\)/i', 'INTEGER', $sql);
        $sql = preg_replace('/\\bBIGINT\\(\\d+\\)/i', 'BIGINT', $sql);
        $sql = preg_replace('/\\bTINYINT\\s*\\(\\s*1\\s*\\)\\s+DEFAULT\\s+1/i', 'SMALLINT DEFAULT 1', $sql);
        $sql = preg_replace('/\\bTINYINT\\s*\\(\\s*1\\s*\\)\\s+DEFAULT\\s+0/i', 'SMALLINT DEFAULT 0', $sql);
        $sql = preg_replace('/\\bTINYINT\\s*\\(\\s*\\d+\\s*\\)/i', 'SMALLINT', $sql);
        $sql = preg_replace('/\\bBOOLEAN\\s+DEFAULT\\s+TRUE\\b/i', 'SMALLINT DEFAULT 1', $sql);
        $sql = preg_replace('/\\bBOOLEAN\\s+DEFAULT\\s+FALSE\\b/i', 'SMALLINT DEFAULT 0', $sql);
        $sql = preg_replace('/\\bBOOLEAN\\b/i', 'SMALLINT', $sql);
        $sql = preg_replace('/\\bJSON\\b/i', 'TEXT', $sql);
        $sql = preg_replace('/\\s{2,}/', ' ', $sql);
        return $sql;
    }

    private function ensureSchema(): void {
        static $ready = false;
        if ($ready) {
            return;
        }

        // The full schema is dozens of statements. Running it on every request
        // made the whole site wait on the database. Skip it once the tables exist.
        $readyRow = $this->conn->query(
            "SELECT to_regclass('public.users') IS NOT NULL
                AND to_regclass('public.php_sessions') IS NOT NULL
                AND to_regclass('public.hero_content') IS NOT NULL AS ready"
        )->fetch();
        $readyValue = $readyRow['ready'] ?? false;
        $isReady = $readyValue === true || $readyValue === 1 || $readyValue === '1' || $readyValue === 't';
        if (!$isReady) {
            $schemaFile = dirname(__DIR__) . '/database/schema-supabase.sql';
            if (!is_file($schemaFile)) {
                throw new Exception('Missing database/schema-supabase.sql');
            }

            $sql = preg_replace('/--.*$/m', '', file_get_contents($schemaFile));
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                try {
                    $this->conn->exec($statement);
                } catch (PDOException $e) {
                    error_log('Schema statement failed: ' . $e->getMessage());
                }
            }

            try {
                $this->bootstrapAdmin();
            } catch (Exception $e) {
                error_log('Admin bootstrap failed: ' . $e->getMessage());
            }
        }

        // Existing databases already have hero_content, so the full schema
        // file is skipped. These columns were added after that table existed.
        $this->ensureHeroColumns();
        $this->ensureAboutColumns();
        $this->ensureImageUrlColumns();
        $ready = true;
    }

    private function ensureHeroColumns(): void {
        $table = $this->conn->query("SELECT to_regclass('public.hero_content')")->fetchColumn();
        if (!$table) {
            return;
        }

        $meta = $this->conn->query(
            "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'hero_content'"
        )->fetchAll();
        $have = [];
        $types = [];
        foreach ($meta as $row) {
            $name = strtolower($row['column_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $have[] = $name;
            $types[$name] = strtolower($row['data_type'] ?? '');
        }

        $columns = [
            'description' => 'TEXT',
            'background_image' => 'TEXT',
            'slides' => 'TEXT',
            'headline' => 'TEXT',
            'subheadline' => 'TEXT',
            'slide_interval_ms' => 'INTEGER DEFAULT 5000',
            'slide_direction' => "VARCHAR(10) DEFAULT 'left'",
            'slide_mode' => "VARCHAR(20) DEFAULT 'marquee'",
            'background_size' => "VARCHAR(20) DEFAULT 'contain'",
            'background_position' => "VARCHAR(20) DEFAULT 'center'",
            'image_scale' => 'NUMERIC(4,2) DEFAULT 1',
            'headline_color' => "VARCHAR(50) DEFAULT '#ffffff'",
            'subheadline_color' => "VARCHAR(50) DEFAULT '#ffffff'",
            'description_color' => "VARCHAR(50) DEFAULT '#ffffff'",
        ];

        foreach ($columns as $name => $type) {
            if (in_array($name, $have, true)) {
                continue;
            }
            try {
                $this->conn->exec("ALTER TABLE hero_content ADD COLUMN IF NOT EXISTS {$name} {$type}");
            } catch (PDOException $e) {
                error_log("Hero column {$name} was not added: " . $e->getMessage());
            }
        }

        if (isset($types['image_scale']) && in_array($types['image_scale'], ['integer', 'smallint', 'bigint'], true)) {
            try {
                $this->conn->exec('ALTER TABLE hero_content ALTER COLUMN image_scale TYPE NUMERIC(4,2) USING image_scale::numeric');
            } catch (PDOException $e) {
                error_log('Hero image_scale was not widened: ' . $e->getMessage());
            }
        }

        foreach (['background_image', 'slides'] as $wideColumn) {
            if (!isset($types[$wideColumn]) || $types[$wideColumn] === 'text') {
                continue;
            }
            try {
                $this->conn->exec("ALTER TABLE hero_content ALTER COLUMN {$wideColumn} TYPE TEXT USING {$wideColumn}::text");
            } catch (PDOException $e) {
                error_log("Hero column {$wideColumn} was not widened: " . $e->getMessage());
            }
        }

        try {
            if (in_array('title', $have, true)) {
                $this->conn->exec("UPDATE hero_content SET headline = title WHERE (headline IS NULL OR btrim(headline) = '') AND title IS NOT NULL AND btrim(title) <> ''");
            }
            if (in_array('subtitle', $have, true)) {
                $this->conn->exec("UPDATE hero_content SET subheadline = subtitle WHERE (subheadline IS NULL OR btrim(subheadline) = '') AND subtitle IS NOT NULL AND btrim(subtitle) <> ''");
            }
        } catch (PDOException $e) {
            error_log('Hero text backfill skipped: ' . $e->getMessage());
        }
    }

    private function ensureAboutColumns(): void {
        $table = $this->conn->query("SELECT to_regclass('public.about_content')")->fetchColumn();
        if (!$table) {
            return;
        }

        $columns = [
            'image' => 'TEXT',
            'hero_image' => 'TEXT',
            'about_image_2' => 'TEXT',
            'hero_caption' => 'VARCHAR(200)',
            'mission_title' => 'VARCHAR(200)',
            'mission_content' => 'TEXT',
            'mission_paragraph2' => 'TEXT',
            'core_values' => 'TEXT',
            'statistics' => 'TEXT',
        ];

        $meta = $this->conn->query(
            "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'about_content'"
        )->fetchAll();
        $have = [];
        $types = [];
        foreach ($meta as $row) {
            $name = strtolower($row['column_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $have[] = $name;
            $types[$name] = strtolower($row['data_type'] ?? '');
        }

        foreach ($columns as $name => $type) {
            if (!in_array($name, $have, true)) {
                try {
                    $this->conn->exec("ALTER TABLE about_content ADD COLUMN IF NOT EXISTS {$name} {$type}");
                } catch (PDOException $e) {
                    error_log("About column {$name} was not added: " . $e->getMessage());
                }
                continue;
            }
            if ($type === 'TEXT' && isset($types[$name]) && $types[$name] !== 'text') {
                try {
                    $this->conn->exec("ALTER TABLE about_content ALTER COLUMN {$name} TYPE TEXT");
                } catch (PDOException $e) {
                    error_log("About column {$name} was not widened: " . $e->getMessage());
                }
            }
        }
    }

    private function ensureImageUrlColumns(): void {
        $rows = $this->conn->query(
            "SELECT table_name, column_name
             FROM information_schema.columns
             WHERE table_schema = 'public'
               AND data_type <> 'text'
               AND (
                 column_name IN ('image', 'photo', 'logo', 'slides', 'additional_slides', 'file_path', 'file_url')
                 OR column_name LIKE '%\\_image'
                 OR column_name LIKE '%\\_photo'
                 OR column_name LIKE '%\\_logo'
               )"
        )->fetchAll();
        foreach ($rows as $row) {
            $table = strtolower($row['table_name'] ?? '');
            $column = strtolower($row['column_name'] ?? '');
            if (!preg_match('/^[a-z0-9_]+$/', $table) || !preg_match('/^[a-z0-9_]+$/', $column)) {
                continue;
            }
            try {
                $this->conn->exec("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE TEXT USING {$column}::text");
            } catch (PDOException $e) {
                error_log("Image column {$table}.{$column} was not widened: " . $e->getMessage());
            }
        }
    }

    private function bootstrapAdmin(): void {
        $email = appEnv('ADMIN_EMAIL');
        $password = appEnv('ADMIN_PASSWORD');
        if ($email === '' || $password === '') {
            return;
        }

        $existing = $this->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
        if ($existing) {
            return;
        }

        $this->insert(
            'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)',
            [appEnv('ADMIN_USERNAME', 'admin'), $email, password_hash($password, PASSWORD_DEFAULT), 'admin']
        );
    }
}

function getDB() {
    return Database::getInstance();
}
