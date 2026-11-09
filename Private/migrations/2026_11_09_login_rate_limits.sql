CREATE TABLE IF NOT EXISTS login_rate_limits (
    bucket_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at INT UNSIGNED NOT NULL,
    INDEX idx_login_rate_limits_window (window_started_at)
);
