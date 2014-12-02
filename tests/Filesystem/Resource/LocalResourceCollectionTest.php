<?php

/*
 * This file is part of the Puli package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Repository\Tests\Filesystem\Resource;

use Puli\Repository\Filesystem\Resource\LocalDirectoryResource;
use Puli\Repository\Filesystem\Resource\LocalFileResource;
use Puli\Repository\Filesystem\Resource\LocalResourceCollection;
use Puli\Repository\Tests\Resource\TestDirectory;
use Puli\Repository\Tests\Resource\TestFile;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LocalResourceCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $fixturesDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__.'/Fixtures';
    }

    public function testConstruct()
    {
        $collection = new LocalResourceCollection(array(
            $dir = new LocalDirectoryResource($this->fixturesDir.'/dir1'),
            $file = new LocalFileResource($this->fixturesDir.'/file3'),
        ));

        $this->assertCount(2, $collection);
        $this->assertSame(array($dir, $file), $collection->toArray());
        $this->assertSame($dir, $collection->get(0));
        $this->assertSame($file, $collection->get(1));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructFailsIfNoTraversable()
    {
        new LocalResourceCollection('foobar');
    }

    public function testReplace()
    {
        $collection = new LocalResourceCollection(array(
            new LocalDirectoryResource($this->fixturesDir.'/dir1'),
        ));

        $collection->replace(array(
            $dir = new LocalDirectoryResource($this->fixturesDir.'/dir2'),
            $file = new LocalFileResource($this->fixturesDir.'/file3'),
        ));

        $this->assertCount(2, $collection);
        $this->assertSame(array($dir, $file), $collection->toArray());
        $this->assertSame($dir, $collection->get(0));
        $this->assertSame($file, $collection->get(1));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReplaceFailsIfNoTraversable()
    {
        $collection = new LocalResourceCollection();

        $collection->replace('foobar');
    }

    public function testAdd()
    {
        $collection = new LocalResourceCollection(array(
            $dir = new LocalDirectoryResource($this->fixturesDir.'/dir1'),
        ));

        $collection->add($file = new LocalFileResource($this->fixturesDir.'/file3'));

        $this->assertCount(2, $collection);
        $this->assertSame(array($dir, $file), $collection->toArray());
        $this->assertSame($dir, $collection->get(0));
        $this->assertSame($file, $collection->get(1));
    }

    public function testGetLocalPaths()
    {
        $collection = new LocalResourceCollection(array(
            $dir = new LocalDirectoryResource($this->fixturesDir.'/dir1'),
            $file = new LocalFileResource($this->fixturesDir.'/file3'),
            new TestFile(),
            new TestDirectory(),
        ));

        $this->assertSame(array(
            $dir->getLocalPath(),
            $file->getLocalPath(),
            null,
            null
        ), $collection->getLocalPaths());
    }
}
