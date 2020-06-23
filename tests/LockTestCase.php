<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process;

use PHPUnit\Framework\TestCase;

/**
 * Lock tests common class.
 */
class LockTestCase extends TestCase
{
    /**
     * Temporary lock-file path
     */
    protected string $path;

    /**
     * Temporary directory path
     */
    protected static string $tmp = "./build/tmp";

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \mkdir(self::$tmp, 0777, true);
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        \rmdir(self::$tmp);

        parent::tearDownAfterClass();
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = \sprintf("%s/lock", self::$tmp);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        if (\file_exists($this->path)) {
            \unlink($this->path);
        }

        parent::tearDown();
    }
}
