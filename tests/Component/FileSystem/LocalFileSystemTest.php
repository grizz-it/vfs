<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Tests\Component\FileSystem;

use SplFileInfo;
use SplFileObject;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use GrizzIt\Vfs\Common\FileIterableInterface;
use GrizzIt\Vfs\Exception\FileNotFoundException;
use GrizzIt\Vfs\Exception\InaccessibleFileException;
use GrizzIt\Vfs\Component\FileSystem\LocalFileSystem;

/**
 * @coversDefaultClass \GrizzIt\Vfs\Component\FileSystem\LocalFileSystem
 * @covers \GrizzIt\Vfs\Exception\InaccessibleFileException
 * @covers \GrizzIt\Vfs\Exception\FileNotFoundException
 */
class LocalFileSystemTest extends TestCase
{
    /**
     * @return LocalFileSystem
     *
     * @covers ::__construct
     */
    public function testConstruct(): LocalFileSystem
    {
        $subject = new LocalFileSystem(__DIR__.'/../../test-filesystem');
        $this->assertInstanceOf(LocalFileSystem::class, $subject);

        return $subject;
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::touch
     * @covers ::unlink
     * @covers ::inBoundary
     * @covers ::toRealPath
     */
    public function testTouchAndUnlink(LocalFileSystem $subject): void
    {
        $subject->unlink('/foo.txt');
        $subject->touch('/foo.txt');
        $this->expectException(InaccessibleFileException::class);
        $subject->touch('../foo.txt');
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::makeDirectory
     * @covers ::removeDirectory
     * @covers ::isDirectory
     */
    public function testMakeAndRemoveDirectory(LocalFileSystem $subject): void
    {
        $subject->makeDirectory('foo');
        $this->assertEquals(true, $subject->isDirectory('foo'));
        $subject->removeDirectory('foo');
        $this->assertEquals(false, $subject->isDirectory('foo'));
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::list
     */
    public function testList(LocalFileSystem $subject): void
    {
        $this->assertEquals(['foo.txt'], $subject->list('/'));
        $this->assertEquals([], $subject->list('/bar'));
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::isReadable
     * @covers ::isWriteable
     * @covers ::isExecutable
     * @covers ::isFile
     */
    public function testFileChecks(LocalFileSystem $subject): void
    {
        $this->assertIsBool($subject->isReadable('foo.txt'));
        $this->assertIsBool($subject->isWriteable('foo.txt'));
        $this->assertIsBool($subject->isExecutable('foo.txt'));
        $this->assertIsBool($subject->isFile('foo.txt'));
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::move
     * @covers ::copy
     * @covers ::unlink
     * @covers ::isFile
     */
    public function testFileMove(LocalFileSystem $subject): void
    {
        $subject->move('foo.txt', 'bar.txt');
        $this->assertEquals(false, $subject->isFile('foo.txt'));
        $this->assertEquals(true, $subject->isFile('bar.txt'));
        $subject->copy('bar.txt', 'foo.txt');
        $this->assertEquals(true, $subject->isFile('foo.txt'));
        $this->assertEquals(true, $subject->isFile('bar.txt'));
        $subject->unlink('bar.txt');
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::put
     * @covers ::write
     * @covers ::truncate
     * @covers ::get
     * @covers ::size
     */
    public function testFileWriting(LocalFileSystem $subject): void
    {
        $subject->put('foo.txt', 'foo');
        $subject->write('foo.txt', 'bar');
        $this->assertEquals('foobar', $subject->get('foo.txt'));
        $this->assertEquals(6, $subject->size('foo.txt'));
        $subject->truncate('foo.txt');
        $this->assertEquals('', $subject->get('foo.txt'));
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::realpath
     */
    public function testRealPath(LocalFileSystem $subject): void
    {
        $this->assertRegexp(
            '/test-filesystem\/foo.txt/',
            $subject->realpath('foo.txt')
        );
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::setFileMode
     * @covers ::getFileMode
     */
    public function testFileMode(LocalFileSystem $subject): void
    {
        $subject->setFileMode('foo.txt', 0777);
        $this->assertEquals(777, $subject->getFileMode('foo.txt'));
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::getFileIterable
     * @covers ::getDirectoryIterable
     */
    public function testGetIterators(LocalFileSystem $subject): void
    {
        $this->assertInstanceOf(
            FileIterableInterface::class,
            $subject->getFileIterable('foo.txt', FileIterableInterface::MODE_CHUNK, 4)
        );

        $this->assertInstanceOf(
            FilesystemIterator::class,
            $subject->getDirectoryIterable('/')
        );

        $this->expectException(FileNotFoundException::class);
        $subject->getFileIterable('bar.txt');
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::getDirectoryIterable
     */
    public function testFailedDirectoryIterator(LocalFileSystem $subject): void
    {
        $this->expectException(FileNotFoundException::class);
        $subject->getDirectoryIterable('bar');
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::getPathInfo
     */
    public function testGetPathInfo(LocalFileSystem $subject): void
    {
        $pathInfo = $subject->getPathInfo('foo.txt');
        $pathInfo['dirname'] = basename($pathInfo['dirname']);
        $this->assertEquals(
            [
                'dirname' => 'test-filesystem',
                'basename' => 'foo.txt',
                'extension' => 'txt',
                'filename' => 'foo'
            ],
            $pathInfo
        );
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::getFileInfo
     */
    public function testGetFileInfo(LocalFileSystem $subject): void
    {
        $this->assertInstanceOf(
            SplFileInfo::class,
            $subject->getFileInfo('foo.txt')
        );
    }

    /**
     * @depends testConstruct
     *
     * @param LocalFileSystem $subject
     *
     * @return void
     *
     * @covers ::getFileObject
     */
    public function testGetFileObject(LocalFileSystem $subject): void
    {
        $this->assertInstanceOf(
            SplFileObject::class,
            $subject->getFileObject('foo.txt')
        );
    }
}
