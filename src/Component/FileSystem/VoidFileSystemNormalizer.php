<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Exception\FileException;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
use GrizzIt\Vfs\Exception\CouldNotNormalizeException;
use GrizzIt\Vfs\Exception\CouldNotDenormalizeException;

class VoidFileSystemNormalizer implements FileSystemNormalizerInterface
{
    /**
     * Decodes a file.
     *
     * @param FileSystemInterface $fileSystem
     * @param string $filename
     *
     * @return mixed
     *
     * @throws CouldNotNormalizeException Always.
     */
    public function normalizeFromFile(
        FileSystemInterface $fileSystem,
        string $filename
    ): mixed {
        throw new CouldNotNormalizeException(
            $filename,
            new FileException($filename, 'Using void normalizer.')
        );
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
     * @throws CouldNotDenormalizeException Always.
     */
    public function denormalizeToFile(
        FileSystemInterface $fileSystem,
        string $filename,
        mixed $value
    ): void {
        throw new CouldNotDenormalizeException(
            $filename,
            new FileException($filename, 'Using void normalizer.')
        );
    }
}
