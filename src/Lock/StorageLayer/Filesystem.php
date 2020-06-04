<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process\Lock\StorageLayer;

use donbidon\Lib\Process\Lock\StorageLayerInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Filesystem storage layer.
 */
class Filesystem implements StorageLayerInterface
{
    protected string $path;
    protected int $mode = 0666;

    /**
     * @param array $options
     *   'path' key contains path,
     *   'mode' key contains mode for \chmod() (optional, default is 0666)
     * @throws InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (!isset($options['path'])) {
            throw new InvalidArgumentException("\$options['path'] required");
        }
        if (!\is_string($options['path'])) {
            throw new InvalidArgumentException("\$options['path'] must be a string");
        }
        $this->path = $options['path'];
        if (isset($options['mode'])) {
            if (!\is_int($options['mode'])) {
                throw new InvalidArgumentException("\$options['mode'] must be an integer");
            }
            $this->mode = $options['mode'];
        }
    }

    /**
     * @throws RuntimeException
     */
    public function delete(): void
    {
        if (!@\unlink($this->path)) {
            throw new RuntimeException("Cannot delete data to '{$this->path}'");
        }
    }

    // phpcs:disable Squiz.Commenting.FunctionComment.Missing
    public function exists(): bool
    {
        return \file_exists($this->path);
    }
    // phpcs:enable

    /**
     * @return mixed
     * @throws RuntimeException
     */
    public function get()
    {
        $data = @\file_get_contents($this->path);
        if (false === $data) {
            throw new RuntimeException("Cannot get record data from '{$this->path}'");
        }
        return $data;
    }

    /**
     * @throws RuntimeException
     */
    public function getModificationTime(): int
    {
        $time = @\filemtime($this->path);
        if (false === $time) {
            throw new RuntimeException("Cannot get record modification time from '{$this->path}'");
        }
        return $time;
    }

    /**
     * @param mixed $data
     * @throws RuntimeException
     */
    public function set($data): void
    {
        if (!@\file_put_contents($this->path, (string)$data)) {
            throw new RuntimeException("Cannot set record data to '{$this->path}'");
        }
        @\chmod($this->path, $this->mode);
    }

    /**
     * @throws RuntimeException
     */
    public function updateModificationTime(?int $time = null): void
    {
        if (null === $time) {
            $time = \time();
        }
        if (!@\touch($this->path, $time)) {
            throw new RuntimeException("Cannot update modification time of record at '{$this->path}'");
        }
        \clearstatcache(false, $this->path);
    }
}