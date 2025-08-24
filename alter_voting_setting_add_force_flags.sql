-- Run this if your existing voting_setting table lacks the new force flags
ALTER TABLE voting_setting
ADD COLUMN force_open TINYINT(1) NOT NULL DEFAULT 0 AFTER result_status,
ADD COLUMN force_closed TINYINT(1) NOT NULL DEFAULT 0 AFTER force_open;