-- Update users table to new schema
ALTER TABLE users
    CHANGE COLUMN name full_name VARCHAR(255) NOT NULL,
    CHANGE COLUMN pass password VARCHAR(255) NOT NULL,
    ADD COLUMN phone VARCHAR(255) DEFAULT NULL,
    ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'student',
    ADD COLUMN remaining INT NOT NULL DEFAULT 0;
