<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Exception;

use Exception;

/**
 * The base exception for files.
 */
class FileException extends Exception
{
    /**
     * Constructor.
     *
     * @param string $filename
     * @param string $message
     */
    public function __construct(string $filename, string $message)
    {
        parent::__construct(
            sprintf(
                'File exception: %s thrown for file: "%s"',
                $filename,
                $message
            )
        );
    }
}
