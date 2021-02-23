<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Tests\Component\File;

use GrizzIt\Vfs\Common\FileIterableInterface;
use GrizzIt\Vfs\Component\File\FileIterable;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \GrizzIt\Vfs\Component\File\FileIterable
 */
class FileIterableTest extends TestCase
{
    /**
     * @return FileIterable
     *
     * @covers ::__construct
     */
    public function testConstructChunkMode(): FileIterable
    {
        $resource = tmpfile();
        $subject = new FileIterable($resource, FileIterableInterface::MODE_CHUNK, 4);
        $this->assertInstanceOf(FileIterable::class, $subject);

        return $subject;
    }

    /**
     * @return FileIterable
     *
     * @covers ::__construct
     */
    public function testConstructLineMode(): FileIterable
    {
        $resource = tmpfile();
        $subject = new FileIterable($resource, FileIterableInterface::MODE_LINE);
        $this->assertInstanceOf(FileIterable::class, $subject);

        return $subject;
    }

    /**
     * @depends testConstructLineMode
     * @depends testConstructChunkMode
     *
     * @param FileIterable $lineSubject
     * @param FileIterable $chunkSubject
     *
     * @return FileIterable[]
     *
     * @covers ::offsetExists
     * @covers ::offsetSet
     * @covers ::offsetUnset
     * @covers ::offsetGet
     * @covers ::handleChunkWrite
     * @covers ::handleLineWrite
     * @covers ::mapToLine
     * @covers ::feof
     * @covers ::handleAppend
     */
    public function testArrayAccess(
        FileIterable $lineSubject,
        FileIterable $chunkSubject
    ): array {
        $this->assertEquals(true, isset($lineSubject[0]));
        $this->assertEquals(false, isset($chunkSubject[0]));
        // Covers appending with fixed value
        $lineSubject[0] = 'foo';
        $chunkSubject[0] = 'foo';

        // Covers appending
        $lineSubject[] = 'baz';
        $chunkSubject[] = 'baz';

        // Covers overwriting
        $chunkSubject[0] = 'bar';
        $lineSubject[0] = 'bar';

        //Covers unsetting
        unset($lineSubject[1]);
        unset($chunkSubject[0]);

        $this->assertEquals('z', $chunkSubject[0]);
        $this->assertEquals('bar', $lineSubject[0]);

        // Covers setting with same chunk size
        $chunkSubject[0] = 'bara';
        $chunkSubject[1] = 'baza';

        // Covers overwriting the last chunk
        $chunkSubject[1] = 'barar';
        $lineSubject[1] = 'bar';
        $lineSubject[2] = 'baz';

        // Covers overwriting the a middle chunk
        $chunkSubject[1] = 'baz';
        $lineSubject[1] = 'ba';

        // Cover appending empty lines to offset
        $lineSubject[10] = 'qux';

        $this->assertEquals('qux', $lineSubject[10]);

        return [$lineSubject, $chunkSubject];
    }

    /**
     * @depends testArrayAccess
     *
     * @param FileIterable[] $subjects
     *
     * @return void
     *
     * @covers ::ensureIteratorPosition
     * @covers ::current
     * @covers ::key
     * @covers ::next
     * @covers ::rewind
     * @covers ::valid
     */
    public function testIterator(array $subjects): void
    {
        $lineMatchArray = [];
        // Line reader iteration
        foreach ($subjects[0] as $key => $line) {
            $lineMatchArray[$key] = $line;
            // This is to throw the internal pointer off balance.
            $this->assertEquals('ba', $subjects[0][1]);
        }

        $this->assertEquals(
            [
                'bar',
                'ba',
                'baz',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'qux'
            ],
            $lineMatchArray
        );

        $chunkMatchArray = [];
        // Chunk reader iteration
        foreach ($subjects[1] as $key => $line) {
            $chunkMatchArray[$key] = $line;
            // This is to throw the internal pointer off balance.
            $this->assertEquals('bazr', $subjects[1][1]);
        }

        $this->assertEquals(
            [
                'bara',
                'bazr'
            ],
            $chunkMatchArray
        );
    }

    /**
     * @depends testConstructLineMode
     *
     * @param FileIterable $lineSubject
     *
     * @return void
     *
     * @covers ::offsetExists
     */
    public function testOffsetExistsFails(FileIterable $lineSubject): void
    {
        $this->expectException(InvalidArgumentException::class);
        $lineSubject->offsetExists('foo');
    }

    /**
     * @depends testConstructLineMode
     *
     * @param FileIterable $lineSubject
     *
     * @return void
     *
     * @covers ::offsetSet
     */
    public function testOffsetSetFails(FileIterable $lineSubject): void
    {
        $this->expectException(InvalidArgumentException::class);
        $lineSubject->offsetSet('foo', 'foo');
    }

    /**
     * @depends testConstructLineMode
     *
     * @param FileIterable $lineSubject
     *
     * @return void
     *
     * @covers ::offsetSet
     */
    public function testOffsetSetFailsValue(FileIterable $lineSubject): void
    {
        $this->expectException(InvalidArgumentException::class);
        $lineSubject->offsetSet(0, ['foo']);
    }

    /**
     * @depends testConstructLineMode
     *
     * @param FileIterable $lineSubject
     *
     * @return void
     *
     * @covers ::offsetGet
     */
    public function testOffsetGetFails(FileIterable $lineSubject): void
    {
        $this->expectException(InvalidArgumentException::class);
        $lineSubject->offsetGet('foo');
    }

    /**
     * @depends testConstructLineMode
     *
     * @param FileIterable $lineSubject
     *
     * @return void
     *
     * @covers ::offsetUnset
     */
    public function testOffsetUnsetFails(FileIterable $lineSubject): void
    {
        $this->expectException(InvalidArgumentException::class);
        $lineSubject->offsetUnset('foo');
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::close
     */
    public function testClose(): void
    {
        $subject = new FileIterable(tmpfile(), FileIterableInterface::MODE_LINE);
        $this->assertInstanceOf(FileIterable::class, $subject);
        $subject->close();
    }
}
