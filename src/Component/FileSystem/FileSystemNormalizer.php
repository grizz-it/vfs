<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use Throwable;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Codec\Common\CodecRegistryInterface;
use GrizzIt\Vfs\Exception\FileNotFoundException;
use GrizzIt\Translator\Common\TranslatorInterface;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
use GrizzIt\Vfs\Exception\CouldNotNormalizeException;
use GrizzIt\Vfs\Exception\CouldNotDenormalizeException;

class FileSystemNormalizer implements FileSystemNormalizerInterface
{
    /**
     * Contains the codecs by their keys.
     *
     * @var CodecRegistryInterface
     */
    private CodecRegistryInterface $codecRegistry;

    /**
     * Contains the translator for translating an extension to a MIME type.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $extensionToMime;

    /**
     * Contains the translator for translating a MIME type to a codec.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $mimeToCodec;

    /**
     * Constructor.
     *
     * @param CodecRegistryInterface $codecRegistry
     * @param TranslatorInterface $extensionToMime
     * @param TranslatorInterface $mimeToCodec
     */
    public function __construct(
        CodecRegistryInterface $codecRegistry,
        TranslatorInterface $extensionToMime,
        TranslatorInterface $mimeToCodec
    ) {
        $this->codecRegistry = $codecRegistry;
        $this->extensionToMime = $extensionToMime;
        $this->mimeToCodec = $mimeToCodec;
    }

    /**
     * Decodes a file.
     *
     * @param FileSystemInterface $fileSystem
     * @param string $filename
     *
     * @return mixed
     *
     * @throws CouldNotNormalizeException When the file can not be normalized.
     */
    public function normalizeFromFile(
        FileSystemInterface $fileSystem,
        string $filename
    ): mixed {
        try {
            if (
                $fileSystem->isFile($filename)
                && !$fileSystem->isDirectory($filename)
            ) {
                return $this->codecRegistry->getDecoder(
                    $this->extensionToCodec(
                        $fileSystem
                            ->getFileInfo($filename)
                            ->getExtension()
                    )
                )->decode($fileSystem->get($filename));
            }

            throw new FileNotFoundException($filename);
        } catch (Throwable $exception) {
            throw new CouldNotNormalizeException($filename, $exception);
        }
    }

    /**
     * Encodes and writes to a file.
     *
     * @param FileSystemInterface $fileSystem
     * @param string $filename
     * @param mixed $value
     *
     * @return void
     *
     * @throws CouldNotDenormalizeException When the file can not be denormalized.
     */
    public function denormalizeToFile(
        FileSystemInterface $fileSystem,
        string $filename,
        mixed $value
    ): void {
        try {
            $info = pathinfo($filename);
            if (isset($info['extension'])) {
                $fileSystem->put(
                    $filename,
                    $this->codecRegistry->getEncoder(
                        $this->extensionToCodec($info['extension'])
                    )->encode($value)
                );

                return;
            }

            throw new FileNotFoundException($filename);
        } catch (Throwable $exception) {
            throw new CouldNotDenormalizeException($filename, $exception);
        }
    }

    /**
     * Translates an extension to a codec.
     *
     * @param string $extension
     *
     * @return string
     */
    private function extensionToCodec(string $extension): string
    {
        return $this->mimeToCodec->getRight(
            $this->extensionToMime->getRight($extension)
        );
    }
}
