-- Migration 025: Add class_teacher_id to sections
ALTER TABLE `sections`
ADD COLUMN `class_teacher_id` INT UNSIGNED DEFAULT NULL AFTER `capacity`;
