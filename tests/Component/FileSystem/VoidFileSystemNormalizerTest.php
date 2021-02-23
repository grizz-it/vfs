<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Tests\Component\FileSystem;

use PHPUnit\Framework\TestCase;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Exception\CouldNotNormalizeException;
use GrizzIt\Vfs\Exception\CouldNotDenormalizeException;
use GrizzIt\Vfs\Component\FileSystem\VoidFileSystemNormalizer;

/**
 * @coversDefaultClass \GrizzIt\Vfs\Component\FileSystem\VoidFileSystemNormalizer
 * @covers \GrizzIt\Vfs\Exception\CouldNotNormalizeException
 * @covers \GrizzIt\Vfs\Exception\CouldNotDenormalizeException
 */
class VoidFileSystemNormalizerTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::normalizeFromFile
     */
    public function testNormalizeFromFileError(): void
    {
        $subject = new VoidFileSystemNormalizer();
        $this->expectException(CouldNotNormalizeException::class);

        $subject->normalizeFromFile(
            $this->createMock(FileSystemInterface::class),
            'foo.json'
        );
    }

    /**
     * @return void
     *
     * @covers ::denormalizeToFile
     */
    public function testDenormalizeToFileError(): void
    {
        $subject = new VoidFileSystemNormalizer();
        $this->expectException(CouldNotDenormalizeException::class);

        $subject->denormalizeToFile(
            $this->createMock(FileSystemInterface::class),
            'foo',
            '{"foo": "bar"}'
        );
    }
}
