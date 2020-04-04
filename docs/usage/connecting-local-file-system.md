# GrizzIT Virtual File System - Connecting a local file system

## Definitions

### Drivers

Within this package, drivers are used to create a common abstraction layer to
connect instantiate a file system. For example, when connecting with an FTP
server, the driver can establish the connection with the server. When the
connection is established a file system can be created from a path.

### File Systems

The file system term in this package is used to describe a class which binds to
a directory and restricts the user to that directory. It ensures files can not
be manipulated outside of these bounds.

## Connecting with a local file system

The package provides an implementation for connecting with local file systems
through the provided driver.

To connect with a directory, create a new instance of the driver. Then provide
the directory path to the `connect` method. This method will return a file
system.

```php
<?php

use GrizzIt\Vfs\Driver\LocalFileSystemDriver;

$driver = new LocalFileSystemDriver();

$fileSystem = $driver->connect(__DIR__ . '/tests/test-filesystem');
```

The file system has a wide set of methods to work with a file system. These can
be inspected in the [FileSystemInterface](../../src/Common/FileSystemInterface.php).

### Using the file iterator

When requesting a file from the file system it is possible to retrieve an object
with iterator and `ArrayAccess` functionalities. This can be done by calling the
`getFileIterable` method on the file system.

The file can be opened in two different modes.

**MODE_CHUNK**

This mode iterates over the file in predetermined sized chunks.
The write operations completely override a chunk (even if the size is different).
The chunk size in this mode determines the amount of bytes to read.

**MODE_LINE**

This mode iterates over the file per line (using `PHP_EOL`).
The write operations completely override the line at the defined position.
The chunk size in this mode determines the maximum amount of bytes a line can be.

The `File` class can be created from a `LocalFileSystem` by calling `getFileIterable`.
To create one, open a file in `r+` mode and pass the resource together with the desired mode and a chunk size to the constructor:

```php
<?php
use GrizzIt\Vfs\Common\FileIterableInterface;
use GrizzIt\Vfs\Driver\LocalFileSystemDriver;

$driver = new LocalFileSystemDriver();

$fileSystem = $driver->connect(__DIR__ . '/tests/test-filesystem');

$fileIterable = $fileSystem->getFileIterable('foo.txt', FileIterableInterface::MODE_LINE);
```

The iterable can then be used, just like an array:

```php
// Write foo to the second line.
$fileIterable[1] = 'foo';

// Outputs foo
echo $fileIterable[1];

// Removes foo from the file.
unset($fileIterable[1]);

// Appends foo as a line to the end of the file.
$fileIterable[] = 'foo';

// Will output the line and line number for every line in the file.
foreach ($fileIterable as $key => $line) {
    echo sprintf('Line %d says: %s', $key + 1, $line);
}
```

## Further reading

[Back to usage index](index.md)

[Support decoding](support-decoding.md)