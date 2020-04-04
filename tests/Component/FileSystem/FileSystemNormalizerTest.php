<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Tests\Component\FileSystem;

use SplFileInfo;
use PHPUnit\Framework\TestCase;
use GrizzIt\Codec\Common\DecoderInterface;
use GrizzIt\Codec\Common\EncoderInterface;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Codec\Common\CodecRegistryInterface;
use GrizzIt\Translator\Common\TranslatorInterface;
use GrizzIt\Vfs\Exception\CouldNotNormalizeException;
use GrizzIt\Vfs\Exception\CouldNotDenormalizeException;
use GrizzIt\Vfs\Component\FileSystem\FileSystemNormalizer;

/**
 * @coversDefaultClass \GrizzIt\Vfs\Component\FileSystem\FileSystemNormalizer
 * @covers \GrizzIt\Vfs\Exception\CouldNotNormalizeException
 * @covers \GrizzIt\Vfs\Exception\CouldNotDenormalizeException
 */
class FileSystemNormalizerTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::normalizeFromFile
     * @covers ::extensionToCodec
     */
    public function testNormalizeFromFile(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $codecRegistry = $this->createMock(CodecRegistryInterface::class);
        $extensionToMime = $this->createMock(TranslatorInterface::class);
        $mimeToCodec = $this->createMock(TranslatorInterface::class);
        $fileInfo = $this->createMock(SplFileInfo::class);
        $decoder = $this->createMock(DecoderInterface::class);
        $fileContent = '{"foo": "bar"}';
        $data = ['foo' => 'bar'];

        $subject = new FileSystemNormalizer(
            $codecRegistry,
            $extensionToMime,
            $mimeToCodec
        );

        $fileSystem->expects(static::once())
            ->method('isFile')
            ->with('foo.json')
            ->willReturn(true);

        $fileSystem->expects(static::once())
            ->method('isDirectory')
            ->with('foo.json')
            ->willReturn(false);

        $fileInfo->expects(static::once())
            ->method('getExtension')
            ->willReturn('json');

        $fileSystem->expects(static::once())
            ->method('getFileInfo')
            ->with('foo.json')
            ->willReturn($fileInfo);

        $fileSystem->expects(static::once())
            ->method('get')
            ->with('foo.json')
            ->willReturn($fileContent);

        $extensionToMime->expects(static::once())
            ->method('getRight')
            ->with('json')
            ->willReturn('application/json');

        $mimeToCodec->expects(static::once())
            ->method('getRight')
            ->with('application/json')
            ->willReturn('json');

        $codecRegistry->expects(static::once())
            ->method('getDecoder')
            ->with('json')
            ->willReturn($decoder);

        $decoder->expects(static::once())
            ->method('decode')
            ->with($fileContent)
            ->willReturn($data);

        $this->assertEquals(
            $data,
            $subject->normalizeFromFile($fileSystem, 'foo.json')
        );
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::denormalizeToFile
     * @covers ::extensionToCodec
     */
    public function testDenormalizeToFile(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);
        $codecRegistry = $this->createMock(CodecRegistryInterface::class);
        $extensionToMime = $this->createMock(TranslatorInterface::class);
        $mimeToCodec = $this->createMock(TranslatorInterface::class);
        $encoder = $this->createMock(EncoderInterface::class);
        $fileContent = '{"foo": "bar"}';
        $data = ['foo' => 'bar'];

        $subject = new FileSystemNormalizer(
            $codecRegistry,
            $extensionToMime,
            $mimeToCodec
        );

        $fileSystem->expects(static::once())
            ->method('put')
            ->with('foo.json', $fileContent);

        $extensionToMime->expects(static::once())
            ->method('getRight')
            ->with('json')
            ->willReturn('application/json');

        $mimeToCodec->expects(static::once())
            ->method('getRight')
            ->with('application/json')
            ->willReturn('json');

        $codecRegistry->expects(static::once())
            ->method('getEncoder')
            ->with('json')
            ->willReturn($encoder);

        $encoder->expects(static::once())
            ->method('encode')
            ->with($data)
            ->willReturn($fileContent);

        $subject->denormalizeToFile($fileSystem, 'foo.json', $data);
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::normalizeFromFile
     */
    public function testNormalizeFromFileError(): void
    {
        $fileSystem = $this->createMock(FileSystemInterface::class);

        $subject = new FileSystemNormalizer(
            $this->createMock(CodecRegistryInterface::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(TranslatorInterface::class)
        );

        $fileSystem->expects(static::once())
            ->method('isFile')
            ->with('foo.json')
            ->willReturn(false);

        $this->expectException(CouldNotNormalizeException::class);

        $subject->normalizeFromFile($fileSystem, 'foo.json');
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::denormalizeToFile
     */
    public function testDenormalizeToFileError(): void
    {
        $subject = new FileSystemNormalizer(
            $this->createMock(CodecRegistryInterface::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(TranslatorInterface::class)
        );

        $this->expectException(CouldNotDenormalizeException::class);

        $subject->denormalizeToFile(
            $this->createMock(FileSystemInterface::class),
            'foo',
            '{"foo": "bar"}'
        );
    }
}
