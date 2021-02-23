<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use SplFileInfo;
use SplFileObject;
use FilesystemIterator;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Component\File\FileIterable;
use GrizzIt\Vfs\Common\FileIterableInterface;
use GrizzIt\Vfs\Exception\FileNotFoundException;
use GrizzIt\Vfs\Exception\InaccessibleFileException;

class LocalFileSystem implements FileSystemInterface
{
    /** @var string */
    private string $root;

    /**
     * Constructor
     *
     * @param string $path The path to the directory.
     */
    public function __construct(string $root)
    {
        $this->root = realpath($root);
    }

    /**
     * Checks if the path is not trying to reach outside its boundary.
     *
     * @param  string $path The path for which the boundary should be checked.
     *
     * @return bool
     *
     * @throws InaccessibleFileException When a path is out of bounds.
     */
    private function inBoundary(string $path): bool
    {
        if (strpos($this->toRealPath($path), $this->root) === 0) {
            return true;
        }

        throw new InaccessibleFileException($path);
    }

    /**
     * Converts the requested path to the real path on the filesystem.
     *
     * @param  string $path
     *
     * @return string
     */
    private function toRealPath(string $path): string
    {
        return str_replace(
            '/./',
            '/',
            preg_replace(
                '/([^<>:"?*|\/]+\/(?=\.\.)\.\.\/)|((?<!:)\/(?=\/))/',
                '',
                $this->root .
                    (substr($path, 0, 1) !== '/' ? '/' : '') .
                    $path
            )
        );
    }

    /**
     * Creates a file without content.
     *
     * @param string $filename The name of the file.
     *
     * @return void
     */
    public function touch(string $filename): void
    {
        $this->inBoundary($filename);
        touch($this->toRealPath($filename));
    }

    /**
     * Creates a directory.
     *
     * @param string $filename The name of the directory file.
     *
     * @return void
     */
    public function makeDirectory(string $filename): void
    {
        $this->inBoundary($filename);
        mkdir($this->toRealPath($filename));
    }

    /**
     * Removes a directory.
     *
     * @param string $filename The name of the directory file.
     *
     * @return void
     */
    public function removeDirectory(string $filename): void
    {
        $this->inBoundary($filename);
        rmdir($this->toRealPath($filename));
    }

    /**
     * Moves a file to a new location.
     *
     * @param string $currentFilename The current filename.
     * @param string $newFilename     The new filename.
     *
     * @return void
     */
    public function move(string $current, string $newFilename): void
    {
        $this->inBoundary($newFilename);
        rename(
            $this->realpath($current),
            $this->toRealPath($newFilename)
        );
    }

    /**
     * Writes to a file, if it doesn't exists, creates it.
     *
     * @param string $filename The filename to write to.
     * @param string $content  The content for the file.
     *
     * @return void
     */
    public function put(string $filename, string $content): void
    {
        $this->inBoundary($filename);
        file_put_contents($this->toRealPath($filename), $content);
    }

    /**
     * Writes to a file, but only if it exists.
     *
     * @param string $filename The filename which needs to be written to.
     * @param string $content  The content for the file.
     *
     * @return void
     */
    public function write(string $filename, string $content): void
    {
        file_put_contents(
            $this->realpath($filename),
            $content,
            FILE_APPEND
        );
    }

    /**
     * Truncates the contents of a file.
     *
     * @param string $filename The name of the file that needs to be truncated.
     *
     * @return void
     */
    public function truncate(string $filename): void
    {
        fclose(fopen($this->realpath($filename), 'w'));
    }

    /**
     * Retrieves the contents of the file.
     *
     * @param string $filename The name of the file which contents need to be retrieved.
     *
     * @return string
     */
    public function get(string $filename): string
    {
        return file_get_contents($this->realpath($filename));
    }

    /**
     * Removes the link to a file.
     *
     * @param string $filename The file which needs to be unlinked.
     *
     * @return void
     */
    public function unlink(string $filename): void
    {
        unlink($this->realpath($filename));
    }

    /**
     * Copies a file to a destination.
     *
     * @param string $source      The source filename.
     * @param string $destination The destination filename.
     *
     * @return void
     */
    public function copy(string $source, string $destination): void
    {
        $this->inBoundary($destination);
        copy($this->realpath($source), $this->toRealPath($destination));
    }

    /**
     * Retrieves the size of the file in bytes.
     *
     * @param string $filename The file which size needs to be determined.
     *
     * @return int
     */
    public function size(string $filename): int
    {
        return filesize($this->realpath($filename));
    }

    /**
     * Returns the absolute path of the file.
     *
     * @param  string $filename The name of the file which paths needs to be determined.
     *
     * @return string
     */
    public function realpath(string $filename): string
    {
        $this->inBoundary($filename);

        return realpath($this->toRealPath($filename));
    }

    /**
     * Changes the file mode.
     *
     * @param string $filename The name of the file.
     * @param int    $mode     The new mode for the file (in octal notation e.g. 0644).
     *
     * @return void
     */
    public function setFileMode(string $filename, int $mode): void
    {
        chmod($this->realpath($filename), $mode);
    }

    /**
     * Retrieves the file mode.
     *
     * @param string $filename The name of the file.
     *
     * @return int
     */
    public function getFileMode(string $filename): int
    {
        return decoct(fileperms($this->realpath($filename)) & 0777);
    }

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
    ): FileIterableInterface {
        if ($this->isFile($filename) && !$this->isDirectory($filename)) {
            return new FileIterable(
                fopen($this->realpath($filename), 'r+'),
                $mode,
                $chunkSize
            );
        }

        throw new FileNotFoundException($filename);
    }

    /**
     * Retrieves a iterable directory reader.
     *
     * @param  string   $path The path of the directory.
     *
     * @return FilesystemIterator
     *
     * @throws FileNotFoundException If the directory can not be found.
     */
    public function getDirectoryIterable(string $path): FilesystemIterator
    {
        if ($this->isDirectory($path)) {
            return new FilesystemIterator($this->realpath($path));
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Retrieves a list of files from a directory.
     *
     * @param  string $path The path which needs to be read.
     *
     * @return array
     */
    public function list(string $path): array
    {
        if ($this->isDirectory($path)) {
            return array_values(
                array_diff(
                    scandir($this->realpath($path)),
                    array('..', '.')
                )
            );
        }

        return [];
    }

    /**
     * Determines if the file is readable.
     *
     * @param string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isReadable(string $filename): bool
    {
        return is_readable($this->realpath($filename));
    }

    /**
     * Determines if the file is writeable.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isWriteable(string $filename): bool
    {
        return is_writable($this->realpath($filename));
    }

    /**
     * Determines if the file is executable.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isExecutable(string $filename): bool
    {
        return is_executable($this->realpath($filename));
    }

    /**
     * Check if the file exists.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isFile(string $filename): bool
    {
        return is_file($this->realpath($filename));
    }

    /**
     * Check if the file is a directory.
     *
     * @param  string $filename The name of the file which needs to be checked.
     *
     * @return bool
     */
    public function isDirectory(string $filename): bool
    {
        return is_dir($this->realpath($filename));
    }

    /**
     * Retrieves the path info about a file.
     *
     * @param string $filename
     *
     * @return array
     */
    public function getPathInfo(string $filename): array
    {
        return pathinfo($this->realpath($filename));
    }

    /**
     * Retrieves a file info instance for a file.
     *
     * @param string $filename
     *
     * @return SplFileInfo
     */
    public function getFileInfo(
        string $filename
    ): SplFileInfo {
        return new SplFileInfo($this->realpath($filename));
    }

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
    ): SplFileObject {
        return new SplFileObject(
            $this->realpath($filename),
            $mode,
            $useIncludePath,
            $context
        );
    }
}
