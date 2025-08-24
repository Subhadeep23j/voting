-- Add date_of_birth column to student_registration table
-- Run this SQL in your MySQL database (clg_ass)

-- Add date_of_birth column after gender
ALTER TABLE student_registration 
ADD COLUMN date_of_birth DATE NOT NULL AFTER gender; 