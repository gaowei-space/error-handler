<?php

/**
 * ErrorHandler is used to catch all php runtime errors and supports reporting to monolog or sentry.
 *
 * @author gaowei <huyao9950@hotmail.com>
 *
 * @version 1.2.0
 *
 * @copyright gaowei
 * @created_at 2022-05-26
 * @updated_at 2023-04-12
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
    public $scope_user     = [];
    public $scope_tags     = [];

    public function __construct(array $options = [])
    {
        $this->display_errors = $options['display_errors'];
        $this->handler        = $options['handler'];
        $this->logger         = $options['logger'];
        $this->sentry_options = $options['sentry_options'];
        $this->report_level   = $options['report_level'];
        $this->scope_user     = $options['scope_user'];
        $this->scope_tags     = $options['scope_tags'];
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
            'scope_user'     => [],
            'scope_tags'     => [],
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
        register_shutdown_function([$this, 'handleFatalError']);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    private function _log($type, $message)
    {
        $level = $this->_getErrorLevel($type);

        $this->_logWrite($message, $level);

        if ($this->display_errors) {
            echo "{$level}: {$message}";
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

        $this->_setScope();

        \Sentry\captureException($exception);
    }

    private function _setScope()
    {
        $scope_user = $this->scope_user;
        $scope_tags = $this->scope_tags;
        if (!$scope_user && !$scope_tags) {
            return false;
        }

        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($scope_user, $scope_tags): void {
            $scope->setUser($scope_user);

            foreach ($scope_tags as $tag_name => $tag_value) {
                $scope->setTag($tag_name, $tag_value);
            }
        });
    }

    private function _sentryCaptureMessage($message, $level)
    {
        $this->_sentryCaptureException(new \ErrorException($message, 0, $this->_getErrorType($level)));
    }

    private function _getErrorType($level)
    {
        switch ($level) {
            case 'fatal':
                $type = 1;
                break;
            case 'error':
                $type = 0;
                break;
            case 'warning':
                $type = 2;
                break;
            case 'info':
                $type = 8;
                break;
            default:
                $type = 1;
                break;
        }

        return $type;
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

    private function _formatMessage($message, $file, $line, $trace = '')
    {
        $message = "{$file}#{$line}: {$message}";

        return <<<MSG
$message
$trace
MSG;
    }
}
