<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process;

use RuntimeException;
use Throwable;

/**
 * Process lock.
 *
 * @todo Cover by tests.
 */
class Lock
{
    public const CANNOT_CREATE_LOCK = 1;
    public const CANNOT_DELETE_LOCK = 2;
    public const CANNOT_DESTROY_PREVIOUS_LOCK = 3;
    public const CANNOT_UPDATE_LOCK = 4;
    public const LOCK_EXISTS = 5;
    public const LOCK_DESTROYED = 6;
    public const LOCK_CONTAINS_WRONG_PROCESS_ID = 7;
    public const PREVIOUS_LOCK_IS_VALID = 8;

    protected StorageLayerInterface $storage;
    protected string $processId;

    /**
     * @throws RuntimeException
     */
    public function __construct(
        StorageLayerInterface $storage,
        int $timeToLive,
        ?bool $destroyPreviousLock = false,
        ?string $processId = ''
    ) {
        $this->storage = $storage;
        $this->processId = '' === $processId ? $this->generateProcessId() : $processId;
        if ($this->storage->exists()) {
            if (\time() - $this->storage->getModificationTime() < $timeToLive) {
                throw new RuntimeException(
                    "Previous lock is still valid",
                    self::PREVIOUS_LOCK_IS_VALID,
                );
            }
            if ($destroyPreviousLock) {
                try {
                    $this->storage->delete();
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "Cannot destroy previous lock: {$e->getMessage()}",
                        self::CANNOT_DESTROY_PREVIOUS_LOCK,
                    );
                }
            } else {
                throw new RuntimeException(
                    "Lock already exists",
                    self::LOCK_EXISTS,
                );
            }
        }
        try {
            $this->storage->set($this->processId);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Cannot create lock: {$e->getMessage()}",
                self::CANNOT_CREATE_LOCK,
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function __destruct()
    {
        $this->validate();
        try {
            $this->storage->delete();
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Cannot delete lock: {$e->getMessage()}",
                self::CANNOT_DELETE_LOCK,
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
            throw new RuntimeException("Lock destroyed", self::LOCK_DESTROYED);
        }
        $processId = $this->storage->get();
        if ($processId !== $this->processId) {
            throw new RuntimeException(
                "Lock contains wrong process Id '{$processId}' instead of '$this->processId'",
                self::LOCK_CONTAINS_WRONG_PROCESS_ID,
            );
        }
    }

    /**
     * Update lock modification time.
     *
     * @throws RuntimeException
     */
    public function update(): void
    {
        $this->validate();
        try {
            $this->storage->updateModificationTime();
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Cannot update lock: {$e->getMessage()}",
                self::CANNOT_UPDATE_LOCK,
            );
        }
    }

    // phpcs: disable Squiz.Commenting.FunctionComment.Missing
    protected function generateProcessId(): string
    {
        return \mt_rand() . '.' . \microtime(true);
    }
}
