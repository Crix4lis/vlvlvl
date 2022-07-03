<?php

namespace App\Reader;

use App\Exception\FileNotFoundException;

interface FileReader
{
    /**
     * @param string $filePath
     * @return mixed
     *
     * @throws FileNotFoundException()
     */
    public function read(string $filePath): array;
}
