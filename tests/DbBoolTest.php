<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/Value.php';

class DbBoolTest extends TestCase {
    public function truthyProvider(): array {
        return [
            [true],
            [1],
            ['1'],
            ['true'],
            ['yes'],
            ['Y'],
            ['vip'],
            [2],
        ];
    }

    public function falsyProvider(): array {
        return [
            [false],
            [0],
            ['0'],
            [''],
            [null],
            ['false'],
            ['no'],
            ['student'],
            ['normal'],
        ];
    }

    /**
     * @dataProvider truthyProvider
     */
    public function testTruthyValues(mixed $value): void {
        $this->assertTrue(db_bool($value));
    }

    /**
     * @dataProvider falsyProvider
     */
    public function testFalsyValues(mixed $value): void {
        $this->assertFalse(db_bool($value));
    }
}
