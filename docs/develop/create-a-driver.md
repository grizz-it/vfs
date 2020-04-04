# GrizzIT Virtual File System - Create a driver

To create a driver, a new class needs to be created, which implements the
[FileSystemDriverInterface](../../src/Common/FileSystemDriverInterface.php).

```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\Driver;

use GrizzIt\Vfs\Common\FileSystemDriverInterface;

class LocalFileSystemDriver implements FileSystemDriverInterface
```

The constructor of the class should contain all information to connect with a
file system and have the possibility to configure a normalizer. For a local file
system this would only require a normalizer to be defined, because the path is
already passed in the `connect` method. However the option should also be there
to not provide any, because the implementation might not support this. So the
start of the implementation will look like the following example:

```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\Driver;

use GrizzIt\Vfs\Common\FileSystemDriverInterface;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
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
```

After this has been set up, the connect and disconnect methods can be created.
Within these methods the connection needs to be managed. For a local file system
a connection will be established, in the `connect` method by checking the path
which is passed to the  method and then a new instance of a FileSystemInterface
is returned. Some file systems might require a custom implementation of the
[FileSystemInterface](../../src/Common/FileSystemInterface.php), because they
do not operate with the same methods as a local file system. For the local file
system implementation, the
[LocalFileSystem](../../src/Component/FileSystem/LocalFileSystem.php) will
suffice. For the `disconnect` method on a local file system, an explicit
disconnect is not required. But it should still be implemented, so applications
can adhere to the standard and set the correct expectations.

By implementing these two methods the class will finally look like the
following:

```php
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
```

## Further reading

[Back to development index](index.md)

[How it works](how-it-works.md)

[Create a file system](create-a-file-system.md)

[Create a normalizer](create-a-normalizer.md)