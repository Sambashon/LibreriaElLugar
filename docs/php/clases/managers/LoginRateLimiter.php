<?php

namespace App\Managers;

class LoginRateLimiter
{
    private const WINDOW_SECONDS = 900;
    private const EMAIL_LIMIT = 5;
    private const IP_LIMIT = 20;

    public function __construct(private readonly \LibreriaDB $db)
    {
    }

    public function isBlocked(string $email, string $ipAddress): bool
    {
        $now = time();
        if (random_int(1, 100) === 1) {
            $this->db->execute(
                'DELETE FROM login_rate_limits WHERE window_started_at < ?',
                [$now - 86400]
            );
        }

        foreach ($this->keys($email, $ipAddress) as [$key, $limit]) {
            $bucket = $this->db->fetch(
                'SELECT attempts, window_started_at
                 FROM login_rate_limits
                 WHERE bucket_key = ?',
                [$key]
            );
            if (
                $bucket
                && (int) $bucket['window_started_at'] > $now - self::WINDOW_SECONDS
                && (int) $bucket['attempts'] >= $limit
            ) {
                return true;
            }
        }

        return false;
    }

    public function recordFailure(string $email, string $ipAddress): void
    {
        $now = time();
        $this->db->beginTransaction();

        try {
            foreach ($this->keys($email, $ipAddress) as [$key]) {
                $this->db->query(
                    'INSERT INTO login_rate_limits (bucket_key, attempts, window_started_at)
                     VALUES (?, 0, ?)
                     ON DUPLICATE KEY UPDATE bucket_key = VALUES(bucket_key)',
                    [$key, $now]
                );
                $bucket = $this->db->fetch(
                    'SELECT attempts, window_started_at
                     FROM login_rate_limits
                     WHERE bucket_key = ?
                     FOR UPDATE',
                    [$key]
                );
                $startedAt = (int) $bucket['window_started_at'];
                $attempts = (int) $bucket['attempts'];
                if ($startedAt <= $now - self::WINDOW_SECONDS) {
                    $startedAt = $now;
                    $attempts = 0;
                }

                $this->db->query(
                    'UPDATE login_rate_limits
                     SET attempts = ?, window_started_at = ?
                     WHERE bucket_key = ?',
                    [$attempts + 1, $startedAt, $key]
                );
            }

            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollback();
            throw $error;
        }
    }

    public function clearEmail(string $email): void
    {
        [$key] = $this->keys($email, '')[0];
        $this->db->execute(
            'DELETE FROM login_rate_limits WHERE bucket_key = ?',
            [$key]
        );
    }

    private function keys(string $email, string $ipAddress): array
    {
        return [
            [
                hash('sha256', 'email:' . strtolower(trim($email))),
                self::EMAIL_LIMIT,
            ],
            [
                hash('sha256', 'ip:' . $ipAddress),
                self::IP_LIMIT,
            ],
        ];
    }
}
