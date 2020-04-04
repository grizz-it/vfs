# GrizzIT Virtual File System - Support decoding

Decoding files can be essential in a lot of use cases. For example when reading
configuration from files, the configuration in string format is hardly useable.
To support decoding, the [FileSystemNormalizerInterface](../../src/Common/FileSystemNormalizerInterface.php) has been added.

When no normalizer is returned, the [VoidFileSystemNormalizer](../../src/Component/FileSystem/VoidFileSystemNormalizer.php) will be returned. This
implementation will only throw errors. However an implementation of a useable
variant is also added. This is the [FileSystemNormalizer](../../src/Component/FileSystem/FileSystemNormalizer.php).

The `FileSystemNormalizer` utilizes the `grizz-it/codec` and
`grizz-it/translator` packages to accomplish an easy useable solution.
However this requires some configuration.
It expects a `CodecRegistry` as its' first parameter. This registry should
contain a set of registered encoders and decoders. The second parameter is a
`Translator` which should be able to translate file extensions on the left side
to mime types on the right side, e.g.: `json => application/json` and vice
versa. The third parameter expects another translator to translate a mime type
on the left to the registered codec on the right side. These translators will be
used to automatically resolve the correct codec and encode/decode the file.

An example of all of this together will look like the following:
```php
<?php

use GrizzIt\Codec\Component\JsonCodec;
use GrizzIt\Codec\Component\YamlCodec;
use GrizzIt\Codec\Component\Registry\CodecRegistry;
use GrizzIt\Translator\Component\ArrayTranslator;
use GrizzIt\Vfs\Component\FileSystem\FileSystemNormalizer;

$registry = new CodecRegistry();
$jsonCodec = new JsonCodec();
$yamlCodec = new YamlCodec();

$registry->registerEncoder('json', $jsonCodec);
$registry->registerDecoder('json', $jsonCodec);

$registry->registerEncoder('yaml', $yamlCodec);
$registry->registerDecoder('yaml', $yamlCodec);

$extensionToMime = new ArrayTranslator();
$extensionToMime->register(['json'], ['application/json']);
$extensionToMime->register(['yml', 'yaml'], [
    'text\/vnd.yaml',
    'text\/yaml',
    'text\/x-yaml',
    'application\/x-yaml'
]);


$mimeToCodec = new ArrayTranslator();
$mimeToCodec->register(['application/json'], ['json']);
$mimeToCodec->register([
    'text\/vnd.yaml',
    'text\/yaml',
    'text\/x-yaml',
    'application\/x-yaml'
], ['yaml']);

$normalizer = new FileSystemNormalizer(
    $registry,
    $extensionToMime,
    $mimeToCodec
);

echo $normalizer->normalizeFromFile($fileSystem, 'foo.json');
```

By passing this normalizer to the driver, it will become accessible to all
classes which use that driver. The reason it is not bound to a single file
system is because the normalizer can be used for multiple drivers.

## Further reading

[Back to usage index](index.md)

[Connecting a local file system](connecting-local-file-system.md)