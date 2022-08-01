<?php

use GaoweiSpace\ErrorHandler\Examples\ErrorHandlerExample;

require __DIR__.'/ErrorHandlerExample.php';

$test = new ErrorHandlerExample();
$test->testInitForSentry(3);

$test->testCaptureMessageForSentry('测试', 'error');
