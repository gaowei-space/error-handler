<?php

use GaoweiSpace\ErrorHandler\Examples\ErrorHandlerExample;

require __DIR__ . '/ErrorHandlerExample.php';

$test  = new ErrorHandlerExample();
$test->testInitLogger(3);
