# 🌈 Error-Handler

![GitHub branch checks state](https://img.shields.io/github/checks-status/gaowei-space/error-handler/main)
[![Latest Release](https://img.shields.io/github/v/release/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/releases)
![StyleCI build status](https://github.styleci.io/repos/496875473/shield)
[![PHP Version](https://img.shields.io/packagist/php-v/gaowei-space/error-handler)](https://www.php.net/)
[![License](https://img.shields.io/github/license/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/LICENSE)

<p> [Error-Handler](https://github.com/gaowei-space/error-handler) is used to catch all php runtime errors and supports reporting to monolog or sentry. </p>

> Compared with the official instantiation method of sentry, it consumes less server resources because it instantiates sentry and reports the exception only when an exception is caught, which is why this package was born.


## Installing

```shell
composer require gaowei-space/error-handler
```

## Usage

### 1. sentry
```php
$options = [
    'report_level'   => E_ALL,
    'display_errors' => true,
    'handler'        => 'sentry', // sentry or logger
    'sentry_options' => [
        'dsn'          => 'http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3',
        'environment'  => 'test',
        'sample_rate'  => 1,
        'http_timeout' => 0.5,
    ],
];
ErrorHandler::init($options);
```
- [sentry doc](https://docs.sentry.io/platforms/php/)
- [sentry options](https://docs.sentry.io/platforms/php/configuration/options/)


### 2. monolog
```php
$logger = new Logger("errors");
$logger->pushHandler(new StreamHandler(sprintf('%s/log/errors_%s.log', __DIR__, date('Ymd')), Logger::DEBUG, true, 0666));

$options = [
    'report_level'   => E_ALL,
    'display_errors' => true,
    'handler'        => 'logger', // sentry or logger
    'logger'         => $logger,
];
ErrorHandler::init($options);
```

## Test

### 1. install develop packages
```
composer require gaowei-space/error-handler --dev
```

### 2. cp env file
```
cp examples/.env.example examples/.env
```
### 3. edit env file
```
SENTRY_DSN = "http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3"
```
### 4. run examples
```php
// monolog
php examples/Monolog.php
// sentry
php examples/Sentry.php
```

## Sentry initialization time-consuming comparison
```
$options = [
    'report_level'   => E_ALL, // error report level
    'display_errors' => true, // prite errors
    'handler'        => 'sentry', // sentry or logger
    'sentry_options' => [
        'dsn'          => 'http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3', // sentry website dsn
        'environment'  => 'test',
        'sample_rate'  => 1, // report rate, float range 0-1
        'http_timeout' => 0.5,
    ],
];

Self:
ErrorHandler::init($options); // time consuming: 0.001616

Sentry:
\Sentry\init($options['sentry_options']); // time consuming: 0.146600
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/gaowei-space/error-handler/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/gaowei-space/error-handler/issues).

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines._

## License

MIT
