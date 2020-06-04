<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Tests\Lib\Process\Lock\StorageLayer;

use donbidon\Lib\Process\Lock\StorageLayerInterface;

// phpcs:disable

/**
 * Test storage layer.
 */
class Custom implements StorageLayerInterface
{
    public function __construct(array $options)
    {
    }

    public function exists(): bool
    {
    }

    public function delete(): void
    {
    }

    public function get()
    {
    }

    public function getModificationTime(): int
    {
    }

    public function set($data): void
    {
    }

    public function updateModificationTime(?int $time = null): void
    {
    }
}
