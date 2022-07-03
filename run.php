<?php

require_once 'vendor/autoload.php';

$run = new \App\RunTaskFacade(
    new \App\ConfGenerator\ConfigGenerator(new \App\ConfGenerator\ConfigValidator()),
    new \App\Reader\JsonFileReader(),
    new \App\Writer\JsonFileWriter(),
    __DIR__.'/source_files/zad1-base.json',
    __DIR__.'/source_files/zad1-params-config.json',
    __DIR__.'/output_files/'.(new DateTime('now'))->format('u'),
);

$run->run();
