<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Tests\Component\Driver;

use PHPUnit\Framework\TestCase;
use GrizzIt\Vfs\Exception\FileNotFoundException;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
use GrizzIt\Vfs\Component\FileSystem\LocalFileSystem;
use GrizzIt\Vfs\Component\Driver\LocalFileSystemDriver;

/**
 * @coversDefaultClass \GrizzIt\Vfs\Component\Driver\LocalFileSystemDriver
 * @covers \GrizzIt\Vfs\Exception\FileNotFoundException
 * @covers \GrizzIt\Vfs\Exception\FileException
 */
class LocalFileSystemDriverTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::getFileSystemNormalizer
     * @covers ::connect
     * @covers ::disconnect
     */
    public function testConnection(): void
    {
        $subject = new LocalFileSystemDriver();

        $this->assertInstanceOf(
            FileSystemNormalizerInterface::class,
            $subject->getFileSystemNormalizer()
        );

        $filesystem = $subject->connect(__DIR__);
        $this->assertInstanceOf(LocalFileSystem::class, $filesystem);
        $subject->disconnect($filesystem);
        $this->expectException(FileNotFoundException::class);
        $subject->connect('Non-existing folder');
    }
}
