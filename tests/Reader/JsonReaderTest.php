<?php

namespace Test\App\Reader;

use App\Exception\FileNotFoundException;
use App\Reader\JsonFileReader;
use PHPUnit\Framework\TestCase;

class JsonReaderTest extends TestCase
{
    public function testThrowsExceptionOnMissingFile(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new JsonFileReader())->read('missing-file');
    }

    public function testReadsExistingFile(): void
    {
        $data = (new JsonFileReader())->read(__DIR__ . '/testfile.json');

        $this->assertEquals(['test' => 'test'], $data);
    }
}
