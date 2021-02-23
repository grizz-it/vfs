<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Common;

use SplFileInfo;
use SplFileObject;
use FilesystemIterator;

interface FileSystemInterface
{
    /**
     * Creates a file without content.
     *
     * @param string $filename The name of the file.
     *
     * @return void
     */
    public function touch(string $filename): void;

    /**
     * Creates a directory.
     *
     * @param string $filename The name of the directory file.
     *
     * @return void
     */
    public function makeDirectory(string $filename): void;

    /**
     * Removes a directory.
     *
     * @param string $filename The name of the directory file.
     *
     * @return void
     */
    public function removeDirectory(string $filename): void;

    /**
     * Moves a file to a new location.
     *
     * @param string $currentFilename The current filename.
     * @param string $newFilename     The new filename.
     *
     * @return void
     */
    public function move(string $current, string $newFilename): void;

    /**
     * Writes to a file, if it doesn't exists, creates it.
     *
     * @param string $filename The filename to write to.
     * @param string $content  The content for the file.
     *
     * @return void
     */
    public function put(string $filename, string $content): void;

    /**
     * Writes to a file, but only if it exists.
     *
     * @param string $filename The filename which needs to be written to.
     * @param string $content  The content for the file.
     *
     * @return void
     */
    public function write(string $filename, string $content): void;

    /**
     * Truncates the contents of a file.
     *
     * @param string $filename The name of the file that needs to be truncated.
     *
     * @return void
     */
    public function truncate(string $filename): void;

    /**
     * Retrieves the contents of the file.
     *
     * @param string $filename The name of the file which contents need to be retrieved.
     *
     * @return string
     */
    public function get(string $filename): string;

    /**
     * Removes the link to a file.
     *
     * @param string $filename The file which needs to be unlinked.
     *
     * @return void
     */
    public function unlink(string $filename): void;

    /**
     * Copies a file to a destination.
     *
     * @param string $source      The source filename.
     * @param string $destination The destination filename.
     *
     * @return void
     */
    public function copy(string $source, string $destination): void;

    /**
     * Retrieves the size of the file in bytes.
     *
     * @param string $filename The file which size needs to be determined.
     *
     * @return int
     */
    public function size(string $filename): int;

    /**
     * Returns the absolute path of the file.
     *
     * @param  string $filename The name of the file which paths needs to be determined.
     *
     * @return string
     */
    public function realpath(string $filename): string;

    /**
     * Changes the file mode.
     *
     * @param string $filename The name of the file.
     * @param int    $mode     The new mode for the file (in octal notation e.g. 0644).
     *
     * @return void
     */
    public function setFileMode(string $filename, int $mode): void;

    /**
     * Retrieves the file mode.
     *
     * @param string $filename The name of the file.
     *
     * @return int
     */
    public function getFileMode(string $filename): int;

    /**
     * Retrieves a iterable file reader.
     *
     * @param string $filename The filename which needs to be read.
     * @param string $mode The mode in which the file should be iterated.
     * @param int    $chunkSize The size of the chunk to be read in MODE_CHUNK
     *                          For MODE_LINE this is used as the maximum line length.
     *
     * @return FileIterableInterface
     */
    public function getFileIterable(
        string $filename,
        string $mode = null,
        int $chunkSize = null
    ): FileIterableInterface;

    /**
     * Retrieves a iterable directory reader.
     *
     * @param  string   $path The path of the directory.
     *
     * @return FilesystemIterator
     */
    public function getDirectoryIterable(string $path): FilesystemIterator;

    /**
     * Retrieves a list of files from a directory.
     *
     * @param  string $path The path which needs to be read.
     *
     * @return array
     */
    public function list(string $path): array;

    /**
     * Determines if the file is readable.
     *
     * @param string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isReadable(string $filename): bool;

    /**
     * Determines if the file is writeable.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isWriteable(string $filename): bool;

    /**
     * Determines if the file is executable.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isExecutable(string $filename): bool;

    /**
     * Check if the file exists.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isFile(string $filename): bool;

    /**
     * Check if the file is a directory.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isDirectory(string $filename): bool;

    /**
     * Retrieves the path info about a file.
     *
     * @param string $filename
     *
     * @return array
     */
    public function getPathInfo(string $filename): array;

    /**
     * Retrieves a file info instance for a file.
     *
     * @param string $filename
     *
     * @return SplFileInfo
     */
    public function getFileInfo(
        string $filename
    ): SplFileInfo;

    /**
     * Retrieves a file object instance for a file.
     *
     * @param string $filename
     * @param string $mode
     * @param boolean $useIncludePath
     * @param resource $context
     *
     * @return SplFileObject
     */
    public function getFileObject(
        string $filename,
        string $mode = 'r',
        bool $useIncludePath = false,
        $context = null
    ): SplFileObject;
}
