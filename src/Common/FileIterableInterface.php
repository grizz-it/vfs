<?php
/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace GrizzIt\Vfs\Common;

use Iterator;
use ArrayAccess;

interface FileIterableInterface extends Iterator, ArrayAccess
{
    /**
     * Reads the file in chunks of set bytes.
     */
    const MODE_CHUNK = 'chunk';

    /**
     * Reads the file per line.
     */
    const MODE_LINE  = 'line';

    /**
     * Closes the file.
     *
     * @return void
     */
    public function close(): void;
}
