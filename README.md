# 🎯 Error-Handler
[中文](https://github.com/gaowei-space/error-handler/blob/main/README.md) | [English](https://github.com/gaowei-space/error-handler/blob/main/README_EN.md)

![GitHub branch checks state](https://img.shields.io/github/checks-status/gaowei-space/error-handler/main)
[![Latest Release](https://img.shields.io/github/v/release/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/releases)
![StyleCI build status](https://github.styleci.io/repos/496875473/shield)
[![PHP Version](https://img.shields.io/packagist/php-v/gaowei-space/error-handler)](https://www.php.net/)
[![License](https://img.shields.io/github/license/gaowei-space/error-handler)](https://github.com/gaowei-space/error-handler/LICENSE)

[Error-Handler](https://github.com/gaowei-space/error-handler) 是用于捕获PHP项目运行期间的各类异常错误，支持通过**monolog**或者**sentry**进行错误上报.

> 与Sentry官方的加载方式相比，该包只在捕获到异常时才进行实例化并报告异常，所以消耗的服务器资源更少，这也是这个包诞生的原因。


## 安装

```shell
composer require gaowei-space/error-handler
```

## 使用

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
- [sentry 文档](https://docs.sentry.io/platforms/php/)
- [sentry 参数配置](https://docs.sentry.io/platforms/php/configuration/options/)


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

## 测试

### 1. 安装开发扩展包
```
composer require gaowei-space/error-handler --dev
```

### 2. 复制配置文件
```
cp examples/.env.example examples/.env
```
### 3. 编辑配置文件
```
SENTRY_DSN = "http://0c2f5aaca4a14eaf958a050157843090@sentry.yoursentrysite.com/3"
```
### 4. 运行测试代码
```php
// monolog
php examples/Monolog.php
// sentry
php examples/Sentry.php
```

## 与Sentry官方实例化进行对比如下
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

本扩展包:
ErrorHandler::init($options); // 耗时: 0.001616

Sentry 官方:
\Sentry\init($options['sentry_options']); // 耗时: 0.146600
```

## 授权许可
本项目采用 MIT 开源授权许可证，完整的授权说明已放置在 [LICENSE](https://github.com/gaowei-space/error-handler/blob/main/LICENSE) 文件中。