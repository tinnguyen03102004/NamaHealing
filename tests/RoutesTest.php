<?php
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase {
    private function request(string $path): int {
        $ch = curl_init('http://localhost:8000' . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }

    public function testArticlesPage() {
        $this->assertEquals(200, $this->request('/articles.php'));
    }

    public function testVideosPage() {
        $this->assertEquals(200, $this->request('/videos.php'));
    }

    public function testDocsPages() {
        $this->assertEquals(200, $this->request('/docs/prayers.php'));
        $this->assertEquals(200, $this->request('/docs/chanting.php'));
        $this->assertEquals(200, $this->request('/docs/reference.php'));
    }
}
