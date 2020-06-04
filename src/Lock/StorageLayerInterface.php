<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process\Lock;

// phpcs:disable Squiz.Commenting.FunctionComment.Missing

/**
 * Storage layer interface.
 */
interface StorageLayerInterface
{
    public function __construct(array $options);

    public function delete(): void;

    public function exists(): bool;

    /**
     * @return mixed
     */
    public function get();

    public function getModificationTime(): int;

    /**
     * @param mixed $data
     */
    public function set($data): void;

    public function updateModificationTime(?int $time = null): void;
}
