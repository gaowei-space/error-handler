<?php

namespace GaoweiSpace\ErrorHandler\Examples;

use GaoweiSpace\ErrorHandler\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__.'/BaseExample.php';

class ErrorHandlerExample
{
    public function testInitLogger($code)
    {
        $logger = new Logger('errors');
        $logger->pushHandler(new StreamHandler(sprintf('%s/log/errors_%s.log', __DIR__, date('Ymd')), Logger::DEBUG, true, 0666));

        $options = [
            'report_level'   => E_ALL, // error report level
            'display_errors' => true, // prite errors
            'handler'        => 'logger', // sentry or logger
            'logger'         => $logger, // monolog loogger
        ];
        ErrorHandler::init($options);

        $this->_test_sentry($code);
    }

    public function testCaptureMessageForLogger($message, $level)
    {
        $logger = new Logger('errors');
        $logger->pushHandler(new StreamHandler(sprintf('%s/log/errors_%s.log', __DIR__, date('Ymd')), Logger::DEBUG, true, 0666));

        $options = [
            'report_level'   => E_ALL, // error report level
            'display_errors' => true, // prite errors
            'handler'        => 'logger', // sentry or logger
            'logger'         => $logger, // monolog loogger
        ];
        ErrorHandler::create($options)->captureMessage($message, $level);
    }

    public function testInitForSentry($code)
    {
        $options = [
            'report_level'   => E_ALL,
            'display_errors' => true, // prite errors
            'handler'        => 'sentry', // sentry or logger
            'sentry_options' => [
                'dsn'          => $_ENV['SENTRY_DSN'], // eg: http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3
                'environment'  => 'test',
                'sample_rate'  => 1, // report rate, float range 0-1
                'http_timeout' => 0.5,
            ],
        ];
        ErrorHandler::init($options);

        $this->_test_sentry($code);
    }

    public function testCaptureMessageForSentry($message, $level)
    {
        $options = [
            'report_level'   => E_ALL,
            'display_errors' => true, // prite errors
            'handler'        => 'sentry', // sentry or logger
            'sentry_options' => [
                'dsn'          => $_ENV['SENTRY_DSN'], // eg: http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3
                'environment'  => 'test',
                'sample_rate'  => 1, // report rate, float range 0-1
                'http_timeout' => 0.5,
            ],
        ];
        ErrorHandler::create($options)->captureMessage($message, $level);
    }

    private function _test_sentry($code)
    {
        switch ($code) {
            case 1:
                $this->testError2(); // E_ERROR
                break;
            case 2:
                explode('ddd', []); // E_ERROR
                break;
            case 3:
                echo $dd; // E_NOTICE
                break;
        }

        print_r("\n\n####### test done ####### \n");
    }
}
