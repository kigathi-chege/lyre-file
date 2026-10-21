<?php

namespace Lyre\File\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Lyre\File\Models\File;

/**
 * A file's bytes were sent to a viewer, inline (stream) or as an attachment (download).
 */
class FileDelivered
{
    public function __construct(
        public readonly File $file,
        public readonly ?Authenticatable $viewer,
        public readonly string $disposition,
    ) {}
}
