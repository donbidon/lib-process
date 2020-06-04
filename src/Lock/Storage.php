<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process\Lock;

use InvalidArgumentException;

/**
 * Storage layer factory.
 */
class Storage
{
    /**
     * @param string $layer Class name having namespace donbidon\Lib\Process\StorageLayer
     *                      or own class starting with '\'
     * @param array $options StorageLayerInterface constructor options
     * @throws InvalidArgumentException
     */
    public static function getLayer(string $layer, array $options = []): StorageLayerInterface
    {
        if (!\preg_match("/^[\\\A-Za-z]+$/", $layer)) {
            throw new InvalidArgumentException("Invalid layer '{$layer}'");
        }
        $class = "\\" !== \substr($layer, 0, 1)
            ? "\\donbidon\\Lib\\Process\\Lock\\StorageLayer\\{$layer}"
            : $layer;
        return new $class($options);
    }
}
