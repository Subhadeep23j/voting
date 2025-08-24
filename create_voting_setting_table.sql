-- Table to store a single (latest) voting schedule
CREATE TABLE IF NOT EXISTS voting_setting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    result_status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = allow early result viewing',
    force_open TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = force open regardless of start time',
    force_closed TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = force closed regardless of current time'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;