<?php

namespace App\Writer;

interface FileWriter
{
    public function write(array $jsonData, string $toFilePath): string;
}
