<?php

namespace App\Writer;

class JsonFileWriter implements FileWriter
{
    public function write(array $jsonData, string $toFilePath): string
    {
        $data = @file_put_contents($toFilePath, json_encode($jsonData));

        if ($data === false) {
            throw new \RuntimeException('File could not be written!');
        }

        return $toFilePath;
    }
}
