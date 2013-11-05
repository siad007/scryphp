#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Scryphp\Scrype;
use Scryphp\Command\PlanCommand;

define('SCRYPHP_STORAGE_PATH_TXT', __DIR__ . '/../storage/text');
define('SCRYPHP_STORAGE_PATH_IMG', __DIR__ . '/../storage/images');

$application = new Application('Scryphp', Scrype::VERSION);
$application->add(new PlanCommand());
$application->run();
