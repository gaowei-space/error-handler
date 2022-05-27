<h1 align="center"> 🌈 error-handler </h1>

<p> ErrorHandler is used to catch all php runtime errors and supports reporting to monolog or sentry. </p>

> Compared with the official instantiation method of sentry, it consumes less server resources because it instantiates sentry and reports the exception only when an exception is caught, which is why this package was born.


## Installing

```shell
$ composer require gaowei-space/error-handler -vvv
```

## Usage

### 1. sentry
```php
$options = [
    'report_level'   => E_ALL,
    'display_errors' => true, // prite errors
    'handler'        => 'sentry', // sentry or logger
    'sentry_options' => [
        'dsn'          => 'http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3', // sentry website dsn
        'environment'  => 'test',
        'sample_rate'  => 1, // report rate, float range 0-1
        'http_timeout' => 0.5,
    ],
];
ErrorHandler::init($options);
```
- [sentry doc](https://docs.sentry.io/platforms/php/)
- [sentry options](https://docs.sentry.io/platforms/php/configuration/options/)


### 2. monolog
```php
$logger   = new Logger("php_errors");
$log_name = sprintf('php_errors_%s.log', date('Ymd'));
$logger->pushHandler(new StreamHandler("./log/" . $log_name, Logger::DEBUG, true, 0666));

$options = [
    'report_level'   => E_ALL, // error report level
    'display_errors' => true, // prite errors
    'handler'        => 'logger', // sentry or logger
    'logger'         => $logger, // monolog loogger
];
ErrorHandler::init($options);
```

## Test

### 1. cp env file
```
cp examples/.env.example examples/.env
```
### 2. edit env file
```
SENTRY_DSN = "http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3"
```
### 3. run test code
```php
php index.php examples/ErrorHandlerTest.php
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/gaowei-space/error-handler/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/gaowei-space/error-handler/issues).

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines._

## License

MIT
