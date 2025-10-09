<?php
use PHPUnit\Framework\TestCase;

class ZoomLinksTest extends TestCase {
    private ?PDO $db = null;

    protected function setUp(): void {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $this->markTestSkipped('DB not available');
        }
    }

    public function testInsertAndFetch() {
        if (!$this->db) {
            $this->markTestSkipped('DB not initialized');
        }
        $this->db->exec("CREATE TABLE IF NOT EXISTS zoom_links (session VARCHAR(10) NOT NULL, audience VARCHAR(10) NOT NULL DEFAULT 'student', url TEXT NOT NULL, PRIMARY KEY (session, audience))");
        $url = 'https://example.com/zoom';
        $stmt = $this->db->prepare("INSERT INTO zoom_links(session, audience, url) VALUES ('morning', 'student', ?) ON DUPLICATE KEY UPDATE url=VALUES(url)");
        $stmt->execute([$url]);
        $stmt = $this->db->prepare("SELECT url FROM zoom_links WHERE session='morning' AND audience='student'");
        $stmt->execute();
        $fetched = $stmt->fetchColumn();
        $this->assertSame($url, $fetched);
    }
}
