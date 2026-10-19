<?php
/**
 * Stores PHP sessions in Supabase so logins survive Vercel's stateless filesystem.
 */
class PgSessionHandler implements SessionHandlerInterface {
    public function open(string $path, string $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        try {
            $row = getDB()->fetchOne(
                "SELECT data FROM php_sessions WHERE id = ? AND expires > NOW()",
                [$id]
            );
            if (!$row || !isset($row['data'])) {
                return '';
            }
            $decoded = base64_decode($row['data'], true);
            return $decoded === false ? '' : $decoded;
        } catch (Exception $e) {
            error_log('Session read failed: ' . $e->getMessage());
            return '';
        }
    }

    public function write(string $id, string $data): bool {
        try {
            getDB()->query(
                "INSERT INTO php_sessions (id, data, expires)
                 VALUES (?, ?, NOW() + INTERVAL '7 days')
                 ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, expires = EXCLUDED.expires",
                [$id, base64_encode($data)]
            );
            return true;
        } catch (Exception $e) {
            error_log('Session write failed: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy(string $id): bool {
        try {
            getDB()->query("DELETE FROM php_sessions WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Session destroy failed: ' . $e->getMessage());
            return false;
        }
    }

    public function gc(int $maxLifetime): int|false {
        try {
            $stmt = getDB()->query("DELETE FROM php_sessions WHERE expires <= NOW()");
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log('Session gc failed: ' . $e->getMessage());
            return false;
        }
    }
}
