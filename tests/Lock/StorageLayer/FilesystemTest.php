<?php
/**
 * PHP-process Lock library.
 *
 * @copyright <a href="http://donbidon.rf.gd/" target="_blank">donbidon</a>
 * @license https://opensource.org/licenses/mit-license.php
 */

declare(strict_types=1);

namespace donbidon\Lib\Process\Lock\StorageLayer;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Filesystem storage layer tests.
 *
 * @coversDefaultClass \donbidon\Lib\Process\Lock\StorageLayer\Filesystem
 */
class FilesystemTest extends TestCase
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
     * Tests exception when required 'path' option missing.
     *
     * @covers ::__construct
     */
    public function testExceptionWhenMissingPathOption(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$options['path'] required");
        new Filesystem([]);
    }

    /**
     * Tests exception when 'path' option isn't a string.
     *
     * @covers ::__construct
     */
    public function testExceptionWhenInvalodPathOption(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$options['path'] must be a string");
        new Filesystem(['path' => []]);
    }

    /**
     * Tests exception when 'mode' option isn't an integer.
     *
     * @covers ::__construct
     */
    public function testExceptionWhenInvalodModeOption(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("\$options['mode'] must be an integer");
        new Filesystem(['path' => "path", 'mode' => ""]);
    }

    /**
     * Tests exception when 'path' deleting invalid record.
     *
     * @covers ::delete
     */
    public function testExceptionWhenDeletingInvalidRecord(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot delete data to '{$this->path}'");
        $storage = new Filesystem(['path' => $this->path]);
        $storage->delete();
    }

    /**
     * Tests exception when 'path' getting invalid record.
     *
     * @covers ::get
     */
    public function testExceptionWhenGettingInvalidRecord(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot get record data from '{$this->path}'");
        $storage = new Filesystem(['path' => $this->path]);
        $storage->get();
    }

    /**
     * Tests exception when 'path' getting invalid record modification time.
     *
     * @covers ::getModificationTime
     */
    public function testExceptionWhenGettingInvalidRecordModificationTime(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot get record modification time from '{$this->path}'");
        $storage = new Filesystem(['path' => $this->path]);
        $storage->getModificationTime();
    }

    /**
     * Tests exception when 'path' setting invalid record.
     *
     * @covers ::set
     */
    public function testExceptionWhenSettingInvalidRecord(): void
    {
        $path = "/invalid/path";
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot set record data to '{$path}'");
        $storage = new Filesystem(['path' => $path]);
        $storage->set('test');
    }

    /**
     * Tests exception when 'path' updating invalid record time.
     *
     * @covers ::updateModificationTime
     */
    public function testExceptionWhenUpdatingInvalidRecordTime(): void
    {
        $path = "/invalid/path";
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot update modification time of record at '{$path}'");
        $storage = new Filesystem(['path' => $path]);
        $storage->updateModificationTime();
    }

    /**
     * Tests common functionality.
     *
     * @covers ::__construct
     * @covers ::delete
     * @covers ::exists
     * @covers ::get
     * @covers ::getModificationTime
     * @covers ::set
     * @covers ::updateModificationTime
     */
    public function testCommonFunctionality(): void
    {
        $storage = new Filesystem([
            'path' => $this->path,
            'mode' => 0666,
        ]);
        self::assertFalse($storage->exists());
        $time = \time();
        $storage->set('test');
        self::assertEquals('test', $storage->get());
        self::assertTrue($storage->exists());
        self::assertEquals($time, $storage->getModificationTime());
        $time += 100500;
        $storage->updateModificationTime();
        self::assertNotEquals($time, $storage->getModificationTime());
        $storage->updateModificationTime($time);
        self::assertEquals($time, $storage->getModificationTime());
        $storage->delete();
        self::assertFalse($storage->exists());
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \mkdir(self::$tmp, 0666);
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