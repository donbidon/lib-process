<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process;

use donbidon\Lib\Process\Lock\StorageLayerInterface;
use RuntimeException;

/**
 * Process lock.
 */
class Lock
{
    public const CANNOT_CREATE = 1;
    public const CANNOT_DELETE = 2;
    public const CANNOT_DESTROY_PREVIOUS = 3;
    public const CANNOT_UPDATE = 4;
    public const DESTROYED = 5;
    public const EXISTING_IS_VALID = 6;
    public const EXISTS = 7;
    public const WRONG_PROCESS_ID = 8;

    protected StorageLayerInterface $storage;
    protected int $timeToLive;
    protected string $processId;

    /**
     * @param StorageLayerInterface $storage Storage layer
     * @param int $timeToLive TTL in seconds
     * @param bool|null $destroyPreviousLock Destroy previous lock
     * @param string|null $processId Process Id
     * @throws RuntimeException
     */
    public function __construct(
        StorageLayerInterface $storage,
        int $timeToLive,
        ?bool $destroyPreviousLock = false,
        ?string $processId = null
    ) {
        $this->storage = $storage;
        $this->processId = $processId ?? $this->generateProcessId();
        if ($this->storage->exists()) {
            if (\time() - $this->storage->getModificationTime() < $timeToLive) {
                throw new RuntimeException(
                    "Previous lock is still valid",
                    self::EXISTING_IS_VALID,
                );
            }
            if ($destroyPreviousLock) {
                try {
                    $this->storage->delete();
                } catch (RuntimeException $e) {
                    throw new RuntimeException(
                        "Cannot destroy previous lock: {$e->getMessage()}",
                        self::CANNOT_DESTROY_PREVIOUS,
                    );
                }
            } else {
                throw new RuntimeException(
                    "Lock already exists",
                    self::EXISTS,
                );
            }
        }
        try {
            $this->storage->set($this->processId);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Cannot create lock: {$e->getMessage()}",
                self::CANNOT_CREATE,
            );
        }
        $this->timeToLive = $timeToLive;
    }

    /**
     * @throws RuntimeException
     */
    public function __destruct()
    {
        $this->validate();
        try {
            $this->storage->delete();
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Cannot delete lock: {$e->getMessage()}",
                self::CANNOT_DELETE,
            );
        }
    }

    /**
     * Validates lock presence and process Id.
     *
     * @throws RuntimeException
     */
    public function validate(): void
    {
        if (!$this->storage->exists()) {
            throw new RuntimeException("Lock destroyed", self::DESTROYED);
        }
        $processId = $this->storage->get();
        if ($processId !== $this->processId) {
            throw new RuntimeException(
                "Lock contains wrong process Id '{$processId}' instead of '{$this->processId}'",
                self::WRONG_PROCESS_ID,
            );
        }
    }

    /**
     * Update lock modification time.
     *
     * @param bool $setTimeLimit Flag specifying to call \set_time_limit()
     * @throws RuntimeException
     */
    public function update(bool $setTimeLimit = false): void
    {
        $this->validate();
        try {
            $this->storage->updateModificationTime();
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "Cannot update lock: {$e->getMessage()}",
                self::CANNOT_UPDATE,
            );
        }
        // @codeCoverageIgnoreStart
        if ($setTimeLimit) {
            \set_time_limit($this->timeToLive);
        }
        // @codeCoverageIgnoreEnd
    }

    // phpcs: disable Squiz.Commenting.FunctionComment.Missing
    protected function generateProcessId(): string
    {
        return \mt_rand() . '.' . \microtime(true);
    }
}
