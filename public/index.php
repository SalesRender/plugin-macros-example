<?php

use Leadvertex\Plugin\Core\Macros\Factories\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../medoo.php';

$factory = new AppFactory();
$application = $factory->web();
$application->run();