ALTER TABLE zoom_links
    ADD COLUMN audience VARCHAR(10) NOT NULL DEFAULT 'student' AFTER session;
ALTER TABLE zoom_links
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (session, audience);
