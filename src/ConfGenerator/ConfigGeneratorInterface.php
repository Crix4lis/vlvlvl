<?php

namespace App\ConfGenerator;

interface ConfigGeneratorInterface
{
    public function generate(array $base, array $params): array;
}
