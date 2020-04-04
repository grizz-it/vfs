# GrizzIT Virtual File System - How it works

## Drivers

Drivers are the components responsible connecting with the file system, this
could range from anywhere between a local file system to an FTP server. The
only responsibility of these drivers is the following: establish a connection
with the file system, instantiate a file system instance and register the
normalizer.

## File systems

The file systems are the components responsible for maintaining the integrity of
the bounds within a directory and simple file manipulation operations.

## Normalizers

The normalizers are the components responsible for encoding and decoding files.
They have the ability to read a file from a file system en decode it. It is
also possible to write any input directly to a file by encoding it.

## Further reading

[Back to development index](index.md)

[Create a driver](create-a-driver.md)

[Create a file system](create-a-file-system.md)

[Create a normalizer](create-a-normalizer.md)