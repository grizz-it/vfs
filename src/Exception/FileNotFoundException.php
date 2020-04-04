<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Exception;

/**
 * An exception that is thrown when a file is not found
 */
class FileNotFoundException extends FileException
{
    public function __construct(string $filename)
    {
        parent::__construct('File not found', $filename);
    }
}
