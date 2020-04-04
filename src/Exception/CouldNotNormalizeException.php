<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Exception;

use Throwable;

class CouldNotNormalizeException extends FileException
{
    /**
     * Constructor.
     *
     * @param string $filename
     * @param Throwable $previous
     */
    public function __construct(string $filename, Throwable $previous)
    {
        parent::__construct(
            $filename,
            sprintf(
                'Could not normalize file: %s',
                $previous->getMessage()
            )
        );
    }
}
