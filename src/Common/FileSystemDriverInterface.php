<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Common;

interface FileSystemDriverInterface
{
    /**
     * Retrieves the registered file system normalizer.
     *
     * @return FileSystemNormalizerInterface
     */
    public function getFileSystemNormalizer(): FileSystemNormalizerInterface;

    /**
     * Connects to the file system.
     *
     * @param string $path
     *
     * @return FileSystemInterface
     */
    public function connect(string $path): FileSystemInterface;

    /**
     * Disconnects from the file system.
     *
     * @param FilesystemInterface $filesystem
     *
     * @return void
     */
    public function disconnect(FilesystemInterface $filesystem): void;
}
