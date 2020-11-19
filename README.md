# PHP-process Lock library
[![PHP Version](https://img.shields.io/packagist/php-v/donbidon/lib-process)](https://www.php.net/)
[![Packagist version](https://img.shields.io/packagist/v/donbidon/lib-process)](https://packagist.org/packages/donbidon/lib-process)
[![GitHub license](https://img.shields.io/github/license/donbidon/lib-process.svg)](https://github.com/donbidon/lib-process/blob/master/LICENSE)
[![Build Status](https://travis-ci.com/donbidon/lib-process.svg?branch=master)](https://travis-ci.com/donbidon/lib-process)
[![Code Coverage](https://codecov.io/gh/donbidon/lib-process/branch/master/graph/badge.svg)](https://codecov.io/gh/donbidon/lib-process)
[![GitHub issues](https://img.shields.io/github/issues-raw/donbidon/lib-process.svg)](https://github.com/donbidon/lib-process/issues)

[![Donate to liberapay](http://img.shields.io/liberapay/receives/don.bidon.svg?logo=liberapay)](https://liberapay.com/don.bidon/donate)

## Installing
Run `composer require donbidon/lib-process`.

## Usage

```php
use donbidon\Lib\Process\Lock;
use donbidon\Lib\Process\Lock\Storage;

try {
    $lock = new Lock(
        new Storage::getLayer("Filesystem", ['path' => "path/to/lock"]),
        60 * 5, // 5 minutes
        true,
    );
} catch (RuntimeException $e) {
    switch ($e->getCode()) {
        case Lock::EXISTING_IS_VALID:
            // Previous lock is valid, interrupt process.
            die;
        default:
            throw $e;
    }
}

// Long time loop
while (true) {
    // ...
    try {
        $lock->update(true); // true to call \set_time_limit(), false by default
    } catch (RuntimeException $e) {
        // ...
    }
}

unset($lock);
```

## Donation
[YooMoney (ex-Yandex.Money), Visa, MasterCard, Maestro](https://yoomoney.ru/to/41001351141494) or visit [Liberapay](https://liberapay.com/don.bidon/donate).
