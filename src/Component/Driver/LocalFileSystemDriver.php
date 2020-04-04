<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\Driver;

use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Exception\FileNotFoundException;
use GrizzIt\Vfs\Common\FileSystemDriverInterface;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
use GrizzIt\Vfs\Component\FileSystem\LocalFileSystem;
use GrizzIt\Vfs\Component\FileSystem\VoidFileSystemNormalizer;

class LocalFileSystemDriver implements FileSystemDriverInterface
{
    /**
     * Contains the file system normalizer.
     *
     * @var FileSystemNormalizerInterface
     */
    private $fileSystemNormalizer;

    /**
     * Constructor.
     *
     * @param FileSystemNormalizerInterface $fileSystemNormalizer
     */
    public function __construct(
        FileSystemNormalizerInterface $fileSystemNormalizer = null
    ) {
        $this->fileSystemNormalizer = $fileSystemNormalizer
            ?? new VoidFileSystemNormalizer();
    }

    /**
     * Retrieves the registered file system normalizer.
     *
     * @return FileSystemNormalizerInterface
     */
    public function getFileSystemNormalizer(): FileSystemNormalizerInterface
    {
        return $this->fileSystemNormalizer;
    }

    /**
     * Connects to the file system.
     *
     * @param string $path
     *
     * @return FileSystemInterface
     *
     * @throws FileNotFoundException When the path can not be resolved.
     */
    public function connect(string $path): FileSystemInterface
    {
        $absolutePath = realpath($path);
        if ($absolutePath) {
            return new LocalFileSystem($absolutePath);
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Disconnects from the file system.
     *
     * @param FilesystemInterface $filesystem
     *
     * @return void
     */
    public function disconnect(FilesystemInterface $filesystem): void
    {
        // Explicit disconnect is not required.
        return;
    }
}
