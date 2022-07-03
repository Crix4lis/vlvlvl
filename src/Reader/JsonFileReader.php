<?php

namespace App\Reader;

use App\Exception\FileNotFoundException;

class JsonFileReader implements FileReader
{
    /**
     * @inheritDoc
     */
    public function read(string $filePath): array
    {
        $data = @file_get_contents($filePath);

        if ($data === false) {
            throw new FileNotFoundException();
        }

        return json_decode($data, true);
    }
}
