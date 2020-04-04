# GrizzIT Virtual File System - Create a normalizer

Normalizers are the components responsible for encoding and decoding the
contents of a file, so the communication between the application and the files
can be established.

To create a normalizer, a new class needs to be created which implements the
[FileSystemNormalizerInterface](../../src/Common/FileSystemNormalizerInterface.php).

```php
<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\FileSystem;

use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;

class FileSystemNormalizer implements FileSystemNormalizerInterface
```

The interface for the normalizer suggests that the file should be able to be
decoded and/or encoded based on the file name and file system alone. An example
of the implementation would be to use the `grizz-it/translator` and
`grizz-it/codec`. In this example the extension of the file name will be
extracted and then translated to a key on which the codec is registered in its'
registry.

There will also be a common method to translate the extension to a codec, so
this logic only needs to be implemented once. This reduces the code duplication
within the class.

When all of this is tied together, the class will look like this:
```php
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
    private $codecRegistry;

    /**
     * Contains the translator for translating an extension to a MIME type.
     *
     * @var TranslatorInterface
     */
    private $extensionToMime;

    /**
     * Contains the translator for translating a MIME type to a codec.
     *
     * @var TranslatorInterface
     */
    private $mimeToCodec;

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
    ) {
        try {
            if ($fileSystem->isFile($filename)
            && !$fileSystem->isDirectory($filename)) {
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
        $value
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
```

The class uses exception provided by the package to set the correct expectations
for the implementor and increase the context of what went wrong, if anything
did.

## Further reading

[Back to development index](index.md)

[How it works](how-it-works.md)

[Create a driver](create-a-driver.md)

[Create a file system](create-a-file-system.md)