# GrizzIT Virtual File System - Create a file system

To create a new file system, quite a few simple methods need to be implemented.
This guide will not cover each individual method, but will give a few tips to
improve the setup of the class.

The file system component is responsible for keeping the user within the bound
of the declared file system. This is to prevent the application from
manipulating files in the location and thus, creating issues.

Most methods which are required by implementing
[FileSystemInterface](../../src/Common/FileSystemInterface.php) have one-on-one
alternatives internally in PHP. The only thing the file system should do with
these is prevent these operations from being executed outside of the bounds.

A new class needs to be created which implements the
[FileSystemInterface](../../src/Common/FileSystemInterface.php). This will look
like the following example:
```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Common\FileSystemInterface;

class LocalFileSystem implements FileSystemInterface
```

The constructor method should expect parameters to be supplied, with which it
can determine the bounds of the file system. For a local file system this would
be the location.

```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Common\FileSystemInterface;

class LocalFileSystem implements FileSystemInterface
{
    /** @var string */
    private $root;

    /**
     * Constructor
     *
     * @param string $path The path to the directory.
     */
    public function __construct(string $root)
    {
        $this->root = realpath($root);
    }
```

For the local file system, two methods have been implemented to easily determine
the bounds of a given path. One is an internal alternative to the `realpath`
method, because it is by default not possible to use this method on non-existing
files. The second file determines whether or not a file is within the set
bounds. This method will also throw one of the common exceptions supplied by
this package. The reason this package throws exceptions instead of uses "vague"
return types, is because it gives a more streamlined response and it can also
give more insight/context into why something went wrong.

By adding these two private methods, the class will look like:
```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Exception\InaccessibleFileException;

class LocalFileSystem implements FileSystemInterface
{
    /** @var string */
    private $root;

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
```

The class can now utilise these methods to implement the rest of the class with
the least amount of code. A few example look like the following:

```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Vfs\Exception\InaccessibleFileException;

class LocalFileSystem implements FileSystemInterface
{
    /** @var string */
    private $root;

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
```

To see the rest of the class, please see
[LocalFileSystem](../../src/Component/FileSystem/LocalFileSystem.php).

## Further reading

[Back to development index](index.md)

[How it works](how-it-works.md)

[Create a driver](create-a-driver.md)

[Create a normalizer](create-a-normalizer.md)