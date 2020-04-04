<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Common;

interface FileSystemNormalizerInterface
{
    /**
     * Decodes a file.
     *
     * @param FileSystemInterface $fileSystem
     * @param string $filename
     *
     * @return mixed
     */
    public function normalizeFromFile(
        FileSystemInterface $fileSystem,
        string $filename
    );

    /**
     * Encodes and writes to a file.
     *
     * @param FileSystemInterface $fileSystem
     * @param string $filename
     * @param mixed $value
     *
     * @return void
     */
    public function denormalizeToFile(
        FileSystemInterface $fileSystem,
        string $filename,
        $value
    ): void;
}
