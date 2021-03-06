<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Exception;

/**
 * An exception thrown when a file is inaccessible.
 */
class InaccessibleFileException extends FileException
{
    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct('File is not accessible', $filename);
    }
}
