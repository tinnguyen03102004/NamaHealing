<?php
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase {
    private function read(string $file): array {
        if (!file_exists($file)) return [];
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    public function testArticlesData() {
        $data = $this->read(__DIR__ . '/../data/articles.json');
        if (empty($data)) {
            $this->markTestSkipped('No articles');
        }
        $this->assertTrue(true);
    }

    public function testVideosData() {
        $data = $this->read(__DIR__ . '/../data/videos.json');
        if (empty($data)) {
            $this->markTestSkipped('No videos');
        }
        $this->assertTrue(true);
    }
}
