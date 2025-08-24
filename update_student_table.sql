-- Update student_registration table to add new fields
-- Run this SQL in your MySQL database (clg_ass)

-- Add new columns to student_registration table
ALTER TABLE student_registration 
ADD COLUMN gender VARCHAR(10) NOT NULL AFTER email,
ADD COLUMN date_of_birth DATE NOT NULL AFTER gender,
ADD COLUMN phone VARCHAR(15) NOT NULL AFTER date_of_birth,
ADD COLUMN village_town VARCHAR(100) NOT NULL AFTER phone,
ADD COLUMN post VARCHAR(100) NOT NULL AFTER village_town,
ADD COLUMN pin VARCHAR(10) NOT NULL AFTER post,
ADD COLUMN police_station VARCHAR(100) NOT NULL AFTER pin,
ADD COLUMN district VARCHAR(100) NOT NULL AFTER police_station;

-- Add indexes for better performance
CREATE INDEX idx_student_phone ON student_registration(phone);
CREATE INDEX idx_student_district ON student_registration(district); 