-- iStore v1.0: additive security migration. Does not modify existing business tables/data.
CREATE TABLE IF NOT EXISTS request_rate_limits (
 scope_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 bucket BIGINT UNSIGNED NOT NULL,
 hits INT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY (scope_hash,bucket)
) ENGINE=InnoDB;
