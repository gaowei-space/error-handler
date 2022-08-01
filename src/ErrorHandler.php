<?php

/**
 * ErrorHandler is used to catch all php runtime errors and supports reporting to monolog or sentry
 *
 * @author gaowei <huyao9950@hotmail.com>
 * @version 1.0.2
 * @copyright gaowei
 * @created_at 2022-05-26
 * @updated_at 2022-08-01
 */

namespace GaoweiSpace\ErrorHandler;

use Psr\Log\LogLevel;
use Sentry\Severity;

class ErrorHandler
{
    public $logger;
    public $handler        = 'logger';  // logger | sentry
    public $display_errors = false;
    public $sentry_options = [];
    public $report_level   = E_ALL;

    public function __construct(array $options = [])
    {
        $this->display_errors = $options['display_errors'];
        $this->handler        = $options['handler'];
        $this->logger         = $options['logger'];
        $this->sentry_options = $options['sentry_options'];
        $this->report_level   = $options['report_level'];
    }

    public static function init(array $options = [])
    {
        $handler = self::create($options);

        error_reporting($handler->report_level);
        ini_set('log_errors', 1);

        $handler->_register();
    }

    public static function create(array $options = [])
    {
        self::_setDefaultOptions($options);
        return new static($options);
    }

    private static function _setDefaultOptions(array &$options)
    {
        $options = array_merge([
            'handler'        => 'logger',
            'logger'         => null,
            'sentry_options' => [],
            'display_errors' => false,
            'report_level'   => E_ALL,
        ], $options);
    }

    public function handleFatalError()
    {
        if (!empty($error = error_get_last())) {
            $exception = new \ErrorException(@$error['message'], 0, @$error['type'], @$error['file'], @$error['line']);
            $this->_sentryCaptureException($exception);

            $type = $error['type'];
            if ($type & $this->report_level) {
                $this->_log($type, $this->_formatMessage($error['message'], $error['file'], $error['line']));
            }
        }
    }

    public function handleError($type, $message, $file, $line)
    {
        if ($type & $this->report_level) {
            $this->_sentryCaptureException(new \ErrorException($message, 0, $type, $file, $line));

            $this->_log($type, $message);
        }
    }

    public function handleException(\Throwable $exception)
    {
        $this->_sentryCaptureException($exception);

        $message = $this->_formatMessage(
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->_log($exception->getCode(), $message);
    }

    public function captureMessage(string $message, string $level)
    {
        $this->_sentryCaptureMessage($message, $level);

        $this->_logWrite($message, $level);
    }

    private function _register()
    {
        register_shutdown_function(array($this, 'handleFatalError'));
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }

    private function _log($type, $message)
    {
        $level = $this->_getErrorLevel($type);

        $this->_logWrite($message, $level);

        if ($this->display_errors) {
            print "{$level}: {$message}";
        }
    }

    private function _logWrite(string $message, string $level)
    {
        if ($this->handler !== 'logger') {
            return false;
        }

        if ($this->logger != null) {
            $this->logger->log($level, $message);
        } else {
            error_log("{$level}: {$message}");
        }
    }

    private function _sentryCaptureException(\Throwable $exception)
    {
        if ($this->handler !== 'sentry') {
            return false;
        }

        if (!$this->sentry_options) {
            return false;
        }

        \Sentry\init($this->sentry_options);
        \Sentry\captureException($exception);
    }

    private function _sentryCaptureMessage($message, $level)
    {
        if ($this->handler !== 'sentry') {
            return false;
        }

        if (!$this->sentry_options) {
            return false;
        }

        \Sentry\init($this->sentry_options);
        \Sentry\captureMessage($message, new Severity($level));
    }

    private function _formatMessage($message, $file, $line, $trace = '')
    {
        $message = "{$file}#{$line}: {$message}";
        return <<<MSG
$message
$trace
MSG;
    }

    private function _getErrorLevel($type)
    {
        $level = LogLevel::EMERGENCY;

        switch ($type) {
            case E_ERROR:
            case E_CORE_ERROR:
                $level = LogLevel::CRITICAL;
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_CORE_WARNING:
            case E_USER_WARNING:
                $level = LogLevel::WARNING;
                break;
            case E_PARSE:
            case E_COMPILE_ERROR:
                $level = LogLevel::ALERT;
                break;
            case E_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_NOTICE:
            case E_USER_DEPRECATED:
                $level = LogLevel::NOTICE;
                break;
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $level = LogLevel::ERROR;
                break;
        }

        return $level;
    }
}
