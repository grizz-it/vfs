<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Component\File;

use GrizzIt\Vfs\Common\FileIterableInterface;
use GrizzIt\Vfs\Exception\LockedOutException;
use InvalidArgumentException;

class FileIterable implements FileIterableInterface
{
    /**
     * The file resource.
     *
     * @var resource
     */
    private $file;

    /**
     * The read and write mode of the current file.
     *
     * @var string
     */
    private $mode;

    /**
     * The increment of the iterator.
     *
     * @var int
     */
    private $increment = 0;

    /**
     * The size in bytes of the readable chunks.
     *
     * @var int
     */
    private $chunkSize;

    /**
     * Contains the map for lines and their pointer position.
     *
     * @var array
     */
    private $lineMap;

    /**
     * Constructor
     *
     * @param resource $file The file which needs to be iterated.
     * @param string   $mode The read and write mode which should be used for the file.
     * @param int      $chunkSize The size of the chunk to be read in byes. Mode:
     *                 MODE_CHUNK = read size of the chunk.
     *                 MODE_LINE = maximum line length.
     */
    public function __construct(
        $file,
        string $mode = FileIterableInterface::MODE_CHUNK,
        int $chunkSize = 4096
    ) {
        $this->file       = $file;
        $this->mode       = $mode;
        $this->chunkSize  = $chunkSize;
        $this->lineMap[0] = 0;
    }

    /**
     * Destructor
     *
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes the file.
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * feof() does not correctly test for the end of the file stream.
     * It requires an additional read operation to determine this.
     *
     * @return bool
     */
    private function feof(): bool
    {
        $currentPosition = ftell($this->file);
        fread($this->file, 1);
        $end = feof($this->file);
        fseek($this->file, $currentPosition);
        return $end;
    }

    /**
     * Checks if the offset can be read.
     *
     * @param  int  $offset
     *
     * @return bool
     *
     * @throws InvalidArgumentException When the offset is not an integer value.
     */
    public function offsetExists($offset): bool
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException(
                "Tried to access file chunk with a non-integer key."
            );
        }

        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            fseek($this->file, $this->chunkSize * $offset);
            return !$this->feof();
        }

        $this->mapToLine($offset);
        return isset($this->lineMap[$offset]);
    }

    /**
     * Maps all pointer positions to line numbers.
     *
     * @param int $offset
     */
    private function mapToLine(int $offset): void
    {
        if (isset($this->lineMap[$offset])) {
            return;
        }

        end($this->lineMap);
        $increment = key($this->lineMap);
        fseek($this->file, $this->lineMap[$increment]);
        $offset = $offset - $increment;
        while ($offset > 0) {
            fgets($this->file, $this->chunkSize);
            $this->lineMap[] = ftell($this->file);
            if ($this->feof()) {
                break;
            }

            $offset--;
        }
    }

    /**
     * Retrieves the value at the offset.
     *
     * @param int $offset
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When the offset is not an integer value.
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException(
                "Tried to access file chunk with a non-integer key."
            );
        }

        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            fseek($this->file, $this->chunkSize * $offset);
            return $this->feof() ? null : fread($this->file, $this->chunkSize);
        }

        $this->mapToLine($offset);
        fseek($this->file, $this->lineMap[$offset]);
        return trim(fgets($this->file, $this->chunkSize), PHP_EOL);
    }

    /**
     * Handle writing a chunk to the file.
     *
     * @param int    $offset
     * @param string $value
     *
     * @return void
     *
     * @throws LockedOutException When the file is already locked.
     */
    private function handleChunkWrite(int $offset, string $value): void
    {
        if (flock($this->file, LOCK_EX)) {
            fseek($this->file, $this->chunkSize * ($offset + 1));
            $lastChunk = $this->feof();
            fseek($this->file, $this->chunkSize * $offset);
            if ($this->feof()) {
                fseek($this->file, 0, SEEK_END);
                fwrite($this->file, $value);
                flock($this->file, LOCK_UN);

                return;
            }

            if (strlen($value) == $this->chunkSize) {
                fwrite($this->file, $value);
                flock($this->file, LOCK_UN);

                return;
            }

            if ($lastChunk) {
                fwrite($this->file, $value);
                ftruncate($this->file, ftell($this->file));
                flock($this->file, LOCK_UN);

                return;
            }

            $temporaryFile = tmpfile();
            rewind($this->file);
            if ($offset !== 0) {
                fwrite(
                    $temporaryFile,
                    fread($this->file, $this->chunkSize * $offset)
                );
            }

            fwrite($temporaryFile, $value);
            fseek($this->file, $this->chunkSize * ($offset + 1));
            while (!$this->feof()) {
                fwrite(
                    $temporaryFile,
                    fread($this->file, $this->chunkSize)
                );
            }

            $size = ftell($temporaryFile);
            rewind($this->file);
            rewind($temporaryFile);
            fwrite($this->file, fread($temporaryFile, $size));
            ftruncate($this->file, $size);
            flock($this->file, LOCK_UN);
            fclose($temporaryFile);

            return;
        }

        throw new LockedOutException(
            stream_get_meta_data($this->file)['uri']
        );
    }

    /**
     * Handle writing to a line to the file.
     *
     * @param int         $offset
     * @param null|string $value
     *
     * @return void
     *
     * @throws LockedOutException When the file is already locked.
     */
    private function handleLineWrite(int $offset, ?string $value): void
    {
        if (flock($this->file, LOCK_EX)) {
            $this->mapToLine($offset);
            if (isset($this->lineMap[$offset])) {
                fseek($this->file, $this->lineMap[$offset]);
                $lineValue = fgets($this->file, $this->chunkSize);
                $lastLine = $this->feof();
                fseek($this->file, $this->lineMap[$offset]);

                if ($lastLine) {
                    fwrite($this->file, $value . PHP_EOL);
                    ftruncate($this->file, ftell($this->file));
                    flock($this->file, LOCK_UN);

                    return;
                }

                if (strlen($value) == strlen($lineValue) - 1) {
                    fwrite($this->file, $value . PHP_EOL);
                    flock($this->file, LOCK_UN);

                    return;
                }

                $temporaryFile = tmpfile();
                rewind($this->file);

                if ($offset !== 0) {
                    fwrite(
                        $temporaryFile,
                        fread($this->file, $this->lineMap[$offset])
                    );
                }

                $eolChar = $value === null ?: PHP_EOL;
                fwrite($temporaryFile, (string) $value . $eolChar);
                fgets($this->file, $this->chunkSize);

                while (!$this->feof()) {
                    fwrite(
                        $temporaryFile,
                        fgets($this->file, $this->chunkSize)
                    );
                }

                $size = ftell($temporaryFile);
                rewind($this->file);
                rewind($temporaryFile);
                fwrite($this->file, fread($temporaryFile, $size));
                ftruncate($this->file, $size);
                $this->lineMap = [0];
                flock($this->file, LOCK_UN);
                fclose($temporaryFile);

                return;
            }

            end($this->lineMap);
            $lastKey = key($this->lineMap);
            $padding = ($offset - $lastKey);
            fseek($this->file, $this->lineMap[$lastKey]);
            fgets($this->file, $this->chunkSize);

            while ($padding > 0) {
                fwrite($this->file, PHP_EOL);
                $padding--;
            }

            fwrite($this->file, $value);
            flock($this->file, LOCK_UN);

            return;
        }

        throw new LockedOutException(
            stream_get_meta_data($this->file)['uri']
        );
    }

    /**
     * Function to handle appending a string to the end of the file.
     *
     * @param string $value The string that should be appended.
     *
     * @return void
     */
    private function handleAppend(string $value): void
    {
        fseek($this->file, 0, SEEK_END);

        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            fwrite($this->file, $value);
            return;
        }

        $this->lineMap = [0];
        fwrite($this->file, trim($value, PHP_EOL) . PHP_EOL);
    }

    /**
     * Replaces a chunk.
     *
     * @param int         $offset
     * @param string|null $value
     *
     * @return void
     *
     * @throws InvalidArgumentException When either the offset is not an integer value.
     *                                  Or when the value is not of type string or null.
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_int($offset) && !is_null($offset)) {
            throw new InvalidArgumentException(
                "Tried to access file chunk with a non-integer key."
            );
        }

        if (!is_null($value) && !is_string($value)) {
            throw new InvalidArgumentException(
                "Value for file chunks can only be of type null and string."
            );
        }

        if (is_null($offset)) {
            $this->handleAppend($value);

            return;
        }

        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            $this->handleChunkWrite($offset, (string) $value);

            return;
        }

        $this->handleLineWrite($offset, $value);
    }

    /**
     * Removes a chunk.
     *
     * @param int $offset
     *
     * @return void
     *
     * @throws InvalidArgumentException When the offset is not an integer value.
     */
    public function offsetUnset($offset): void
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException(
                "Tried to access file chunk with a non-integer key."
            );
        }

        $this->offsetSet($offset, null);
    }

    /**
     * Ensure the correct position of the file pointer for the iterator.
     *
     * @return void
     */
    private function ensureIteratorPosition(): void
    {
        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            $expected = $this->chunkSize * $this->increment;
            if (ftell($this->file) !== $expected) {
                fseek($this->file, $expected);
            }

            return;
        }

        if (ftell($this->file) !== $this->lineMap[$this->increment]) {
            fseek($this->file, $this->lineMap[$this->increment]);
        }
    }

    /**
     * Returns the current file chunk.
     *
     * @return mixed
     */
    public function current()
    {
        $this->ensureIteratorPosition();

        if ($this->mode === FileIterableInterface::MODE_CHUNK) {
            return fread($this->file, $this->chunkSize);
        }

        $line = trim(fgets($this->file, $this->chunkSize), PHP_EOL);
        $this->lineMap[$this->increment + 1] = ftell($this->file);

        return $line;
    }

    /**
     * Returns the current increment of the file.
     *
     * @return int
     */
    public function key(): int
    {
        return $this->increment;
    }

    /**
     * Sets the file pointer to the next chunk.
     *
     * @return void
     */
    public function next(): void
    {
        $this->increment++;
    }

    /**
     * Sets the file pointer to the start of the file.
     *
     * @return void
     */
    public function rewind(): void
    {
        rewind($this->file);
        $this->increment = 0;
    }

    /**
     * Check if the current iteration can be read.
     *
     * @return bool
     */
    public function valid(): bool
    {
        $this->ensureIteratorPosition();

        return !$this->feof($this->file);
    }
}
