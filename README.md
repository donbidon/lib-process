```php
use donbidon\Lib\Process\Lock;
use donbidon\Lib\Process\Lock\Storage;

try {
    $storage = new Storage(['path' => "path/to/lock"]);
    $lock = new Lock(
        $storage,
        60 * 5, // 5 minutes
        true
    );
} catch (RuntimeException $e) {
    switch ($e->getCode()) {
        case Lock::PREVIOUS_LOCK_IS_VALID:
            // Previous lock is valid, interrupt process.
            die;
        default:
            throw $e;
    }
}

// Some long time loop
while (true) {
    // ...
    try {
        $lock->update();
    } catch (RuntimeException $e) {
        // ...
    }
}

unset($lock);
```