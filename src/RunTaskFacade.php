<?php

namespace App;

use App\ConfGenerator\ConfigGeneratorInterface;
use App\Reader\FileReader;
use App\Writer\FileWriter;

class RunTaskFacade
{
    public function __construct(
        private readonly ConfigGeneratorInterface $configGenerator,
        private readonly FileReader $fileReader,
        private readonly FileWriter $fileWriter,
        private readonly string $baseFilePath,
        private readonly string $paramsFilePath,
        private readonly string $outputFilePath,
    ) {}

    public function run(): void
    {
        $baseFile = $this->fileReader->read($this->baseFilePath);
        $paramsFile = $this->fileReader->read($this->paramsFilePath);

        $output = $this->configGenerator->generate($baseFile, $paramsFile);

        $filePath = $this->fileWriter->write($output, $this->outputFilePath . '.json');

        echo sprintf("Generated %s file\n", $filePath);
    }
}
