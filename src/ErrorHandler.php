<?php

/**
 * ErrorHandler is used to catch all php runtime errors and supports reporting to monolog or sentry
 *
 * @author gaowei <huyao9950@hotmail.com>
 * @date 2022-05-26
 *
 * @copyright gaowei
 *
 */

namespace GaoweiSpace\ErrorHandler;

use Psr\Log\LogLevel;

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
        self::_setDefaultOptions($options);

        error_reporting($options['report_level']);
        ini_set('log_errors', 1);

        $handler = new static($options);
        $handler->_register();
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
            $this->_sentry($exception);

            $type = $error['type'];
            if ($type & $this->report_level) {
                $this->_log($type, $this->_formatMessage($error['message'], $error['file'], $error['line']));
            }
        }
    }

    public function handleError($type, $message, $file, $line)
    {
        if ($type & $this->report_level) {
            $this->_sentry(new \ErrorException($message, 0, $type, $file, $line));

            $this->_log($type, $message);
        }
    }

    public function handleException(\Throwable $exception)
    {
        $this->_sentry($exception);

        $message = $this->_formatMessage(
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->_log($exception->getCode(), $message);
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

        if ($this->handler == 'logger') {
            if ($this->logger != null) {
                $this->logger->log($level, $message);
            } else {
                error_log("{$level}: {$message}");
            }
        }

        if ($this->display_errors) {
            print "{$level}: {$message}";
        }
    }

    private function _sentry(\Throwable $exception)
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
