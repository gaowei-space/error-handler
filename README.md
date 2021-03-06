# ð¯ Error-Handler
[ä¸­æ](https://github.com/gaowei-space/error-handler/blob/main/README.md) | [English](https://github.com/gaowei-space/error-handler/blob/main/README_EN.md)

![GitHub branch checks state](https://img.shields.io/github/checks-status/gaowei-space/error-handler/main)
[![Latest Release](https://img.shields.io/github/v/release/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/releases)
![StyleCI build status](https://github.styleci.io/repos/496875473/shield)
[![PHP Version](https://img.shields.io/packagist/php-v/gaowei-space/error-handler)](https://www.php.net/)
[![License](https://img.shields.io/github/license/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/LICENSE)

[Error-Handler](https://github.com/gaowei-space/error-handler) æ¯ç¨äºæè·PHPé¡¹ç®è¿è¡æé´çåç±»å¼å¸¸éè¯¯ï¼æ¯æéè¿**monolog**æè**sentry**è¿è¡éè¯¯ä¸æ¥.

> ä¸Sentryå®æ¹çå è½½æ¹å¼ç¸æ¯ï¼è¯¥ååªå¨æè·å°å¼å¸¸æ¶æè¿è¡å®ä¾åå¹¶æ¥åå¼å¸¸ï¼æä»¥æ¶èçæå¡å¨èµæºæ´å°ï¼è¿ä¹æ¯è¿ä¸ªåè¯ççåå ã


## å®è£

```shell
composer require gaowei-space/error-handler
```

## ä½¿ç¨

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
- [sentry ææ¡£](https://docs.sentry.io/platforms/php/)
- [sentry åæ°éç½®](https://docs.sentry.io/platforms/php/configuration/options/)


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

## æµè¯

### 1. å®è£å¼åæ©å±å
```
composer require gaowei-space/error-handler --dev
```

### 2. å¤å¶éç½®æä»¶
```
cp examples/.env.example examples/.env
```
### 3. ç¼è¾éç½®æä»¶
```
SENTRY_DSN = "http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3"
```
### 4. è¿è¡æµè¯ä»£ç 
```php
// monolog
php examples/Monolog.php
// sentry
php examples/Sentry.php
```

## ä¸Sentryå®æ¹å®ä¾åè¿è¡å¯¹æ¯å¦ä¸
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

æ¬æ©å±å:
ErrorHandler::init($options); // èæ¶: 0.001616

Sentry å®æ¹:
\Sentry\init($options['sentry_options']); // èæ¶: 0.146600
```

## ææè®¸å¯
æ¬é¡¹ç®éç¨ MIT å¼æºææè®¸å¯è¯ï¼å®æ´çææè¯´æå·²æ¾ç½®å¨ [LICENSE](https://github.com/gaowei-space/error-handler/blob/main/LICENSE) æä»¶ä¸­ã